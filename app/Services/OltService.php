<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Olt;
use App\Models\Ont;
use Illuminate\Support\Facades\Log;

/**
 * Multi-Vendor OLT Service — Telnet-based
 *
 * Supported brands (auto-detected from $olt->brand):
 *  - Tenda  (TES7001)
 *  - C-Data (FD1602S-B1, FD1604E-C1-DAP, FD1608S-B1, FD1616S-B2)
 *  - HSGQ   (G02ID)
 *
 * Supports:
 *  - SYNC  (pull ONT data → database)
 *  - PUSH  (register, reboot, unregister, WAN config, DBA profile)
 *
 * ──────────────────── WAN Config CLI Reference ────────────────────
 * Tenda TES7001:
 *   ont# service-port ont-index {slot}/{pon}/{id} gemport {N} vlan {V} mode pppoe|transparent
 *   ont# ont profile bind slotno {slot} port {pon} ontid {id} profile {name}
 *
 * C-Data FD series (interface gpon-onu_{slot}/{pon}:{id}):
 *   tcont {N} name tcont{N} [profile-id {dba}]
 *   gemport {N} name gem{N} tcont {tcont_N}
 *   service-port {N} vport {N} user-vlan {vid} vlan {vid}
 *   write
 *
 * HSGQ G02ID (interface gpon-onu_0/{pon}:{id}):
 *   wan add {idx} pppoe|bridge service-type internet
 *   wan {idx} vlan {vid} 0
 *   write
 * ──────────────────────────────────────────────────────────────────
 */
class OltService
{
    private Olt $olt;

    public function __construct(Olt $olt)
    {
        $this->olt = $olt;
    }

    public static function for(Olt $olt): self
    {
        return new self($olt);
    }

    // ─── Brand Detection ─────────────────────────────────────────────────────

    private function brand(): string
    {
        return strtolower(trim($this->olt->brand ?? ''));
    }

    private function isTenda(): bool
    {
        return str_contains($this->brand(), 'tenda');
    }

    private function isCData(): bool
    {
        return str_contains($this->brand(), 'cdata') || str_contains($this->brand(), 'c-data') || str_contains($this->brand(), 'c_data');
    }

    private function isHsgq(): bool
    {
        return str_contains($this->brand(), 'hsgq');
    }

    // ─── SYNC: Pull ONT data from OLT → Database ─────────────────────────────

    public function syncAll(): array
    {
        set_time_limit(300); // Allow up to 5 minutes for large OLTs
        try {
            $onts = match ($this->olt->preferred_protocol) {
                'snmp'  => $this->fetchViaSNMP(),
                'ssh'   => $this->fetchViaSSH(),
                'rest'  => $this->fetchViaREST(),
                default => $this->fetchViaTelnet(),
            };
        } catch (\Throwable $e) {
            Log::error("OLT sync failed [{$this->olt->name}]: " . $e->getMessage());
            return ['created' => 0, 'updated' => 0, 'total' => 0, 'error' => $e->getMessage()];
        }

        $created = 0;
        $updated = 0;

        foreach ($onts as $ontData) {
            $sn = $ontData['serial_number'] ?? null;
            if (!$sn) continue;

            $existing = Ont::where('olt_id', $this->olt->id)->where('serial_number', $sn)->first();
            $record = [
                'olt_id'           => $this->olt->id,
                'area_id'          => $this->olt->area_id,
                'serial_number'    => $sn,
                'pon_port'         => $ontData['pon_port']         ?? null,
                'olt_port_index'   => $ontData['olt_port_index']   ?? null,
                'description'      => $ontData['description']      ?? null,
                'status'           => $ontData['status']           ?? 'unknown',
                'rx_power'         => $ontData['rx_power']         ?? null,
                'tx_power'         => $ontData['tx_power']         ?? null,
                'distance'         => $ontData['distance']         ?? null,
                'firmware_version' => $ontData['firmware_version'] ?? null,
                'equipment_id'     => $ontData['equipment_id']     ?? null,
                'last_synced_at'   => now(),
            ];

            if ($existing) {
                $existing->update($record);
                $updated++;
            } else {
                $existing = Ont::create($record);
                $created++;
            }

            // ── Auto-link ONT ↔ Customer (area-scoped, safe exact match) ──────
            // Only attempt if this ONT has no customer yet.
            // Match ONT description → customer.name OR customer.pppoe_user
            // Scoped strictly to the OLT's area to prevent cross-area mistakes.
            // If multiple customers match the same description, skip (ambiguous).
            if (!($existing->customer_id) && !empty($record['description'])) {
                $desc = trim($record['description']);
                $areaId = $this->olt->area_id;

                // Match by name (exact, case-insensitive)
                $byName = Customer::where('area_id', $areaId)
                    ->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($desc)])
                    ->get();

                // Match by pppoe_user (exact, case-insensitive)
                $byPppoe = Customer::where('area_id', $areaId)
                    ->whereRaw('LOWER(TRIM(pppoe_user)) = ?', [mb_strtolower($desc)])
                    ->get();

                $matched = $byName->isNotEmpty() ? $byName : $byPppoe;

                if ($matched->count() === 1) {
                    $existing->update(['customer_id' => $matched->first()->id]);
                }
                // count > 1 → ambiguous, skip — admin links manually
            }
        }

        // ── Clean up stale ONTs not returned by this sync
        // Prevents orphaned records from wrong/old syncs mixing into inventory.
        if (!empty($onts)) {
            $syncedSNs = array_column($onts, 'serial_number');
            $stale = Ont::where('olt_id', $this->olt->id)
                ->whereNotIn('serial_number', $syncedSNs);
            // Unlink customers first, then delete (avoids any FK issues)
            (clone $stale)->update(['customer_id' => null]);
            $stale->delete();
        }

        // ── Activity Log + Notification ──
        $total = count($onts);
        $msg = "Synced {$total} ONTs — {$created} new, {$updated} updated.";
        try {
            \App\Models\ActivityLog::log('synced', "OLT [{$this->olt->name}]: {$msg}", $this->olt);
            \App\Models\AdminNotification::notify('sync', "OLT Sync: {$this->olt->name}", $msg, 'bx-refresh', 'green');
        } catch (\Throwable $e) {
            // Don't fail sync if logging fails (table may not exist yet)
        }

        return ['created' => $created, 'updated' => $updated, 'total' => $total];
    }

    // ─── PUSH: Reboot ONT ────────────────────────────────────────────────────

    public function rebootOnt(string $ponPort, int $ontId): array
    {
        try {
            [$slot, $pon] = explode('/', $ponPort);
            $sock = $this->telnetConnect();
            $this->telnetLogin($sock);

            if ($this->isTenda()) {
                $this->telnetSend($sock, 'ont');
                $this->telnetReadUntil($sock, ['ont#'], 3);
                $this->telnetSend($sock, "ont reboot slotno {$slot} port {$pon} ontid {$ontId}");
                $resp = $this->telnetReadMore($sock, 5);
            } elseif ($this->isCData()) {
                $this->telnetSend($sock, 'configure terminal');
                $this->telnetReadMore($sock, 2);
                $this->telnetSend($sock, "reboot onu gpon-olt_{$slot}/{$pon} {$ontId}");
                $resp = $this->telnetReadMore($sock, 5);
            } elseif ($this->isHsgq()) {
                $this->telnetSend($sock, 'configure terminal');
                $this->telnetReadMore($sock, 2);
                $this->telnetSend($sock, "interface gpon 0/{$pon}");
                $this->telnetReadMore($sock, 2);
                $this->telnetSend($sock, "onu-reboot {$ontId}");
                $resp = $this->telnetReadMore($sock, 5);
            } else {
                $resp = 'Brand not supported.';
            }

            $this->telnetLogout($sock);
            Log::info("OLT reboot ONT {$ponPort}/{$ontId}", ['olt' => $this->olt->name]);
            return ['success' => true, 'response' => $resp ?? ''];
        } catch (\Throwable $e) {
            Log::error('OLT rebootOnt failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ─── PUSH: Register ONT ──────────────────────────────────────────────────

    public function registerOnt(string $ponPort, string $serialNumber, string $description = '', string $profile = ''): array
    {
        try {
            [$slot, $pon] = explode('/', $ponPort);
            $sock = $this->telnetConnect();
            $this->telnetLogin($sock);

            if ($this->isTenda()) {
                $this->telnetSend($sock, 'ont');
                $this->telnetReadUntil($sock, ['ont#'], 3);
                $this->telnetSend($sock, "ont add slotno {$slot} port {$pon} sn {$serialNumber} des \"{$description}\"");
                $resp = $this->telnetReadMore($sock, 8);
            } elseif ($this->isCData()) {
                $this->telnetSend($sock, 'configure terminal');
                $this->telnetReadMore($sock, 2);
                $this->telnetSend($sock, "interface gpon-olt_{$slot}/{$pon}");
                $this->telnetReadMore($sock, 2);
                $this->telnetSend($sock, "onu auto-find onuid next sn {$serialNumber} name \"{$description}\"");
                $resp = $this->telnetReadMore($sock, 8);
                $this->telnetSend($sock, 'exit');
                $this->telnetReadMore($sock, 2);
                $this->telnetSend($sock, 'write');
                $resp .= $this->telnetReadMore($sock, 5);
            } elseif ($this->isHsgq()) {
                $this->telnetSend($sock, 'configure terminal');
                $this->telnetReadMore($sock, 2);
                $this->telnetSend($sock, "interface gpon 0/{$pon}");
                $this->telnetReadMore($sock, 2);
                $this->telnetSend($sock, "onu {$serialNumber} sn-auth {$serialNumber} omci ont-lineprofile-id 1 ont-srvprofile-id 1 desc \"{$description}\"");
                $resp = $this->telnetReadMore($sock, 8);
                $this->telnetSend($sock, 'exit');
                $this->telnetReadMore($sock, 2);
                $this->telnetSend($sock, 'write');
                $resp .= $this->telnetReadMore($sock, 5);
            } else {
                $resp = 'Brand not supported.';
            }

            $this->telnetLogout($sock);
            Log::info("OLT register ONT {$serialNumber} on {$ponPort}", ['olt' => $this->olt->name]);
            return ['success' => true, 'response' => $resp ?? ''];
        } catch (\Throwable $e) {
            Log::error('OLT registerOnt failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ─── PUSH: Unregister ONT ────────────────────────────────────────────────

    public function unregisterOnt(string $ponPort, int $ontId): array
    {
        try {
            [$slot, $pon] = explode('/', $ponPort);
            $sock = $this->telnetConnect();
            $this->telnetLogin($sock);

            if ($this->isTenda()) {
                $this->telnetSend($sock, 'ont');
                $this->telnetReadUntil($sock, ['ont#'], 3);
                $this->telnetSend($sock, "ont delete slotno {$slot} port {$pon} ontid {$ontId}");
                $resp = $this->telnetReadMore($sock, 5);
            } elseif ($this->isCData()) {
                $this->telnetSend($sock, 'configure terminal');
                $this->telnetReadMore($sock, 2);
                $this->telnetSend($sock, "interface gpon-olt_{$slot}/{$pon}");
                $this->telnetReadMore($sock, 2);
                $this->telnetSend($sock, "no onu {$ontId}");
                $resp = $this->telnetReadMore($sock, 5);
                $this->telnetSend($sock, 'exit');
                $this->telnetReadMore($sock, 2);
                $this->telnetSend($sock, 'write');
                $resp .= $this->telnetReadMore($sock, 5);
            } elseif ($this->isHsgq()) {
                $this->telnetSend($sock, 'configure terminal');
                $this->telnetReadMore($sock, 2);
                $this->telnetSend($sock, "interface gpon 0/{$pon}");
                $this->telnetReadMore($sock, 2);
                $this->telnetSend($sock, "no onu {$ontId}");
                $resp = $this->telnetReadMore($sock, 5);
                $this->telnetSend($sock, 'exit');
                $this->telnetReadMore($sock, 2);
                $this->telnetSend($sock, 'write');
                $resp .= $this->telnetReadMore($sock, 5);
            } else {
                $resp = 'Brand not supported.';
            }

            $this->telnetLogout($sock);
            return ['success' => true, 'response' => $resp ?? ''];
        } catch (\Throwable $e) {
            Log::error('OLT unregisterOnt failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ─── PUSH: WAN Configuration ─────────────────────────────────────────────

    /**
     * Push full WAN config (VLAN + mode + DBA profile) to a specific ONT via OLT CLI.
     *
     * Tenda TES7001:   ont# service-port ont-index + profile bind
     * C-Data FD series: tcont → gemport → service-port → write
     * HSGQ G02ID:       wan add pppoe|bridge + wan vlan → write
     *
     * @param string $ponPort    e.g. "1/1" (Tenda) or "0/1" (C-Data/HSGQ)
     * @param int    $ontId      ONT index on PON port
     * @param int|null $vlanId    Customer VLAN ID, or null/0 for untagged
     * @param string $mode       'pppoe' | 'bridge' (IPoE/DHCP)
     * @param int    $tcontSlot  T-CONT slot (C-Data only, default 1)
     * @param int    $gemPort    GEM port index (Tenda & C-Data, default 1)
     * @param string $profile    DBA profile name/ID (optional, C-Data/Tenda)
     * @param int    $wanIndex   WAN connection index (HSGQ only, 1-6, default 1)
     */
    public function setOntWanConfig(
        string $ponPort,
        int    $ontId,
        ?int   $vlanId,
        string $mode        = 'pppoe',
        int    $tcontSlot   = 1,
        int    $gemPort     = 1,
        string $profile     = '',
        int    $wanIndex    = 0,
        int    $priority    = 0,
        string $username    = '',
        string $password    = '',
        int    $mtu         = 0,
        int    $servicePort = 0
    ): array {
        try {
            [$slot, $pon] = explode('/', $ponPort);
            $sock = $this->telnetConnect();
            $this->telnetLogin($sock);
            $resp = '';

            // ── Tenda TES7001 ──────────────────────────────────────────────
            if ($this->isTenda()) {
                $this->telnetSend($sock, 'ont');
                $this->telnetReadUntil($sock, ['ont#'], 3);

                // Bind bandwidth profile first (if provided)
                if ($profile !== '') {
                    $this->telnetSend($sock, "ont profile bind slotno {$slot} port {$pon} ontid {$ontId} profile {$profile}");
                    $resp .= $this->telnetReadMore($sock, 5);
                }

                // service-port: GEM port → VLAN with PPPoE or transparent mode + priority
                $modeStr = match($mode) { 'bridge' => 'transparent', 'static' => 'static', default => 'pppoe' };
                if ($vlanId) {
                    $this->telnetSend($sock, "service-port ont-index {$slot}/{$pon}/{$ontId} gemport {$gemPort} vlan {$vlanId} priority {$priority} mode {$modeStr}");
                } else {
                    $this->telnetSend($sock, "service-port ont-index {$slot}/{$pon}/{$ontId} gemport {$gemPort} untagged mode {$modeStr}");
                }
                sleep(1);
                $resp .= $this->telnetReadMore($sock, 5);

            // ── C-Data FD1602S / FD1604E / FD1608S / FD1616S ─────────────
            } elseif ($this->isCData()) {
                $this->telnetSend($sock, 'configure terminal');
                $this->telnetReadMore($sock, 2);

                // Enter per-ONT config context
                $this->telnetSend($sock, "interface gpon-onu_{$slot}/{$pon}:{$ontId}");
                $this->telnetReadMore($sock, 2);

                // T-CONT: upstream bandwidth container (bind DBA profile if given)
                if ($profile !== '') {
                    $this->telnetSend($sock, "tcont {$tcontSlot} name tcont{$tcontSlot} profile-id {$profile}");
                } else {
                    $this->telnetSend($sock, "tcont {$tcontSlot} name tcont{$tcontSlot}");
                }
                $resp .= $this->telnetReadMore($sock, 3);

                // GEM port: downstream GPON channel, ties to T-CONT for upstream
                $this->telnetSend($sock, "gemport {$gemPort} name gem{$gemPort} tcont {$tcontSlot}");
                $resp .= $this->telnetReadMore($sock, 3);

                // Service-port: maps customer VLAN from UNI to upstream VLAN with priority
                $spId = $servicePort > 0 ? $servicePort : $gemPort;
                if ($vlanId) {
                    $this->telnetSend($sock, "service-port {$spId} vport {$gemPort} user-vlan {$vlanId} vlan {$vlanId} cos {$priority}");
                } else {
                    $this->telnetSend($sock, "service-port {$spId} vport {$gemPort} user-vlan untagged vlan untagged cos {$priority}");
                }
                sleep(1);
                $resp .= $this->telnetReadMore($sock, 5);

                $this->telnetSend($sock, 'exit');
                $this->telnetReadMore($sock, 2);
                $this->telnetSend($sock, 'write');
                $resp .= $this->telnetReadMore($sock, 5);

            // ── HSGQ G02ID ─────────────────────────────────────────────────
            } elseif ($this->isHsgq()) {
                $this->telnetSend($sock, 'configure terminal');
                $this->telnetReadMore($sock, 2);

                // Enter per-ONT config context
                $this->telnetSend($sock, "interface gpon-onu_0/{$pon}:{$ontId}");
                $this->telnetReadMore($sock, 2);

                // Add WAN connection: pppoe, bridge, or static
                $wanMode = match($mode) { 'bridge' => 'bridge', 'static' => 'static', default => 'pppoe' };
                $this->telnetSend($sock, "wan add {$wanIndex} {$wanMode} service-type internet");
                sleep(1);
                $resp .= $this->telnetReadMore($sock, 5);

                // Assign VLAN ID with 802.1p priority (skip if untagged)
                if ($vlanId) {
                    $this->telnetSend($sock, "wan {$wanIndex} vlan {$vlanId} {$priority}");
                    $resp .= $this->telnetReadMore($sock, 3);
                }

                // Set MTU if specified
                if ($mtu > 0) {
                    $this->telnetSend($sock, "wan {$wanIndex} mtu {$mtu}");
                    $resp .= $this->telnetReadMore($sock, 3);
                }

                // Set PPPoE credentials if provided (only applies to pppoe mode)
                if ($mode === 'pppoe' && $username !== '') {
                    $this->telnetSend($sock, "wan {$wanIndex} pppoe username {$username} password {$password}");
                    $resp .= $this->telnetReadMore($sock, 3);
                }

                $this->telnetSend($sock, 'exit');
                $this->telnetReadMore($sock, 2);
                $this->telnetSend($sock, 'write');
                $resp .= $this->telnetReadMore($sock, 5);

            } else {
                $resp = 'Brand not supported for WAN config push.';
            }

            $this->telnetLogout($sock);

            Log::info("OLT WAN config pushed: {$ponPort}/{$ontId} VLAN=" . ($vlanId ?: 'untagged') . " Priority={$priority} Mode={$mode} MTU={$mtu} User=" . ($username ?: '-'), [
                'olt'   => $this->olt->name,
                'brand' => $this->brand(),
                'resp'  => substr($resp, 0, 300),
            ]);

            return ['success' => true, 'response' => $resp];
        } catch (\Throwable $e) {
            Log::error('OLT setOntWanConfig failed', ['error' => $e->getMessage(), 'olt' => $this->olt->name]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ─── PUSH: Change DBA / Bandwidth Profile ────────────────────────────────

    /**
     * Rebind a specific ONT's T-CONT (change upstream bandwidth).
     *
     * DBA = Dynamic Bandwidth Allocation.
     * Think of it as the "speed plan" profile for the customer.
     * Use this when you want to UPGRADE or DOWNGRADE a customer's speed
     * without re-doing the full WAN config.
     *
     * Requirements:
     *  - The profile must already exist on the OLT (created via OLT web/CLI).
     *  - ONT must already be provisioned (registered + WAN configured).
     *
     * @param string $ponPort      e.g. "1/1" or "0/1"
     * @param int    $ontId        ONT index on this PON port
     * @param string $profileName  Profile name/ID defined on the OLT (e.g. "10M", "best-effort")
     * @param int    $tcontSlot    T-CONT slot index to rebind (default 1)
     */
    public function setOntServiceProfile(string $ponPort, int $ontId, string $profileName, int $tcontSlot = 1): array
    {
        try {
            [$slot, $pon] = explode('/', $ponPort);
            $sock = $this->telnetConnect();
            $this->telnetLogin($sock);
            $resp = '';

            if ($this->isTenda()) {
                $this->telnetSend($sock, 'ont');
                $this->telnetReadUntil($sock, ['ont#'], 3);
                $this->telnetSend($sock, "ont profile bind slotno {$slot} port {$pon} ontid {$ontId} profile {$profileName}");
                $resp = $this->telnetReadMore($sock, 5);

            } elseif ($this->isCData()) {
                $this->telnetSend($sock, 'configure terminal');
                $this->telnetReadMore($sock, 2);
                $this->telnetSend($sock, "interface gpon-onu_{$slot}/{$pon}:{$ontId}");
                $this->telnetReadMore($sock, 2);
                // Rebind T-CONT to a different DBA profile
                $this->telnetSend($sock, "tcont {$tcontSlot} profile-id {$profileName}");
                $resp = $this->telnetReadMore($sock, 5);
                $this->telnetSend($sock, 'exit');
                $this->telnetReadMore($sock, 2);
                $this->telnetSend($sock, 'write');
                $resp .= $this->telnetReadMore($sock, 5);

            } elseif ($this->isHsgq()) {
                $this->telnetSend($sock, 'configure terminal');
                $this->telnetReadMore($sock, 2);
                $this->telnetSend($sock, "interface gpon 0/{$pon}");
                $this->telnetReadMore($sock, 2);
                // Rebind T-CONT DBA profile on the OLT side
                $this->telnetSend($sock, "onu {$ontId} tcont {$tcontSlot} dba-profile {$profileName}");
                $resp = $this->telnetReadMore($sock, 5);
                $this->telnetSend($sock, 'end');
                $this->telnetReadMore($sock, 2);
                $this->telnetSend($sock, 'write');
                $resp .= $this->telnetReadMore($sock, 5);

            } else {
                $resp = 'Brand not supported.';
            }

            $this->telnetLogout($sock);
            Log::info("OLT profile rebind: {$ponPort}/{$ontId} → {$profileName}", ['olt' => $this->olt->name]);
            return ['success' => true, 'response' => $resp];
        } catch (\Throwable $e) {
            Log::error('OLT setOntServiceProfile failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ─── Telnet: Fetch ONT data (brand-aware) ────────────────────────────────

    private function fetchViaTelnet(): array
    {
        Log::info("OLT Telnet sync: {$this->olt->name} [{$this->brand()}]");

        if ($this->isCData()) {
            return $this->fetchCData();
        } elseif ($this->isHsgq()) {
            return $this->fetchHsgq();
        } else {
            return $this->fetchTenda(); // default
        }
    }

    // ─── Tenda TES7001 Telnet Fetch ──────────────────────────────────────────
    // Commands discovered from `[Admin]ont# list`:
    // - display ont-auth-info slotno <slot> [<pon>]  → serial number, model
    // - display ont-status <slot> <pon>              → online/offline status
    // - display ont-name <slot> <pon> <ont>          → ONT name (PPPoE username)
    // - display ont-optical-info <slot> <pon> <ont>  → RX/TX power

    private function fetchTenda(): array
    {
        $sock = $this->telnetConnect();
        $this->telnetLogin($sock);

        $this->telnetSend($sock, 'ont');
        $this->telnetReadUntil($sock, ['ont#'], 3);

        // Step 1: Get all ONTs via auth-info (gives serial/model/pon)
        $authOutput = '';
        foreach ([1, 2, 3, 4] as $slot) {
            $this->telnetSend($sock, "display ont-auth-info slotno {$slot}");
            $resp = $this->telnetReadMore($sock, 8);
            if (str_contains($resp, 'invalid') || str_contains($resp, 'error')) continue;
            $authOutput .= $resp . "\n";
        }

        // Step 2: Get status (online/offline) per slot/pon
        $statusOutput = '';
        foreach ([1, 2, 3, 4] as $slot) {
            foreach ([1, 2, 3, 4, 5, 6, 7, 8] as $pon) {
                $this->telnetSend($sock, "display ont-status {$slot} {$pon}");
                $resp = $this->telnetReadMore($sock, 5);
                if (str_contains($resp, 'invalid') || str_contains($resp, 'error')) continue;
                $statusOutput .= $resp . "\n";
            }
        }

        // Step 3: Get basic-info (gives distance)
        $basicOutput = '';
        foreach ([1, 2, 3, 4] as $slot) {
            $this->telnetSend($sock, "display ont-basic-info {$slot}");
            $resp = $this->telnetReadMore($sock, 10);
            if (str_contains($resp, 'invalid') || str_contains($resp, 'error')) continue;
            $basicOutput .= $resp . "\n";
        }

        // Parse auth+status+basic into the base ontMap
        $ontMap = $this->parseTendaAuth($authOutput, $statusOutput, $basicOutput);

        // For each parsed ONT, fetch its name and optical power
        foreach ($ontMap as $idx => &$record) {
            // Remove the 'skip offline' check because the OLT still stores their names
            // and last known info, which we want to sync.
            [$slot, $pon, $ontId] = explode('/', $idx);

            // Get ONT name
            $this->telnetSend($sock, "display ont-name {$slot} {$pon} {$ontId}");
            $nameResp = $this->telnetReadMore($sock, 3);
            if (preg_match('/(?:ont|onu)[\s_-]*name[\s]*:\s*(.+)/i', $nameResp, $nm)) {
                $record['description'] = trim($nm[1]);
            }

            // Get optical power only if online (offline will naturally fail or return 0/-40)
            if ($record['status'] === 'online') {
                $this->telnetSend($sock, "display ont-optical-info {$slot} {$pon} {$ontId}");
                $optResp = $this->telnetReadMore($sock, 5);
                if (preg_match('/receivedOpticalPower:\s*([-\d.]+)\(Dbm\)/i', $optResp, $rx)) {
                    $record['rx_power'] = (float) $rx[1];
                }
                if (preg_match('/transmittedOpticalPower:\s*([-\d.]+)\(Dbm\)/i', $optResp, $tx)) {
                    $record['tx_power'] = (float) $tx[1];
                }
            }
        }
        unset($record);

        $this->telnetLogout($sock);
        return array_values($ontMap);
    }

    /**
     * Parse auth-info, status, and basic-info output into the base ontMap.
     */
    private function parseTendaAuth(string $authRaw, string $statusRaw, string $basicRaw = ''): array
    {
        $clean  = fn(string $s) => $this->cleanTelnet($s);
        $ontMap = [];

        foreach (explode("\n", $clean($authRaw)) as $line) {
            $line = trim($line);
            if (preg_match('/(\d+)\/(\d+)\/(\d+)\s+\S+\s+\S*\s+([A-Za-z0-9]{8,16})\s/i', $line, $m)) {
                $slot  = $m[1];
                $pon   = $m[2];
                $ontId = $m[3];
                $sn    = strtoupper($m[4]);
                $idx   = "{$slot}/{$pon}/{$ontId}";

                $isOffline = str_contains($line, 'Not online');
                $ontMap[$idx] = [
                    'serial_number'    => $sn,
                    'equipment_id'     => null,
                    'pon_port'         => "{$slot}/{$pon}",
                    'olt_port_index'   => (int) $ontId,
                    'status'           => $isOffline ? 'offline' : 'online',
                    'rx_power'         => null,
                    'tx_power'         => null,
                    'distance'         => null,
                    'description'      => null,
                    'firmware_version' => null,
                ];
            }
        }

        // Merge status
        foreach (explode("\n", $clean($statusRaw)) as $line) {
            $line = trim($line);
            if (preg_match('/\d+\s+(\d+)\/(\d+)\/(\d+)\s+\S+\s+(up|down)/i', $line, $m)) {
                $idx = "{$m[1]}/{$m[2]}/{$m[3]}";
                if (isset($ontMap[$idx])) {
                    $ontMap[$idx]['status'] = strtolower($m[4]) === 'up' ? 'online' : 'offline';
                }
            }
        }

        // Merge distance and firmware from basic-info
        // Format:  NUM  onu-idx  venderID  modeID  img0Ver  img1Ver  hwVer  Distance
        // Example: 1    1/1/1             HG3     V1.7.1(*)  V1.7.1  v1.0   171
        if ($basicRaw) {
            foreach (explode("\n", $clean($basicRaw)) as $line) {
                $line = trim($line);
                if (!preg_match('/(\d+\/\d+\/\d+)/', $line, $idxMatch)) continue;
                $idx = $idxMatch[1];
                if (!isset($ontMap[$idx])) continue;

                // Split by whitespace to get columns reliably
                $cols = preg_split('/\s+/', $line);
                // Last column = Distance (always the last number)
                $lastCol = end($cols);
                if (is_numeric($lastCol) && (int) $lastCol > 0) {
                    $ontMap[$idx]['distance'] = (int) $lastCol;
                }
                // Find firmware version: look for V<digits> pattern (img1Ver)
                // img0Ver has (*) suffix, img1Ver does not
                foreach ($cols as $col) {
                    if (preg_match('/^V[\d.]+$/i', $col)) {
                        $ontMap[$idx]['firmware_version'] = $col;
                        // Don't break — last plain V<digits> match wins (img1Ver comes after img0Ver)
                    }
                }
            }
        }

        return $ontMap;
    }

    // Keep old parseTendaOutput as alias for backward compatibility
    private function parseTendaOutput(string $authRaw, string $statusRaw, string $opticalRaw = '', string $desRaw = ''): array
    {
        return $this->parseTendaAuth($authRaw, $statusRaw);
    }


    // ─── C-Data FD Series Telnet Fetch ───────────────────────────────────────

    /**
     * Fetch all ONTs from C-Data FD series OLT.
     *
     * Strategy:
     *   1. Iterate each PON port (0..7) with per-port commands:
     *      - "show ont info 0/0 {port} all"        → registered ONTs (online+offline)
     *      - "show ont optical-info 0/0 {port} all" → RX/TX power
     *      - "show ont version 0/0 {port} all"      → firmware
     *   2. If per-port iteration yields ZERO ONTs, fall back to global:
     *      - "show ont info all"  / "show ont run-info all" / "show ont version all"
     *   3. All raw output is logged for debugging.
     */
    private function fetchCData(): array
    {
        $sock = $this->telnetConnect();
        $this->telnetLogin($sock);

        // Number of PON ports to iterate. C-Data FD1608S has 8, FD1616S has 16.
        // We try 0..15 and stop after repeated invalid-port responses.
        $maxPorts = 16;

        $infoRaw    = '';
        $opticalRaw = '';
        $versionRaw = '';
        $detailRaw  = '';

        $supportsOptical = null;
        $opticalProbePort = null;
        $opticalResponses = [];
        $portsWithOnts = [];
        $ontIdsByPort = [];
        $distanceByKey = [];
        $diagnosticNotes = [];

        $containsAny = static function (string $haystack, array $needles): bool {
            foreach ($needles as $needle) {
                if ($needle !== '' && str_contains($haystack, $needle)) {
                    return true;
                }
            }

            return false;
        };

        $isUnsupportedResponse = static function (string $resp) use ($containsAny): bool {
            return $containsAny($resp, [
                '% Unknown',
                '% Invalid',
                'Unknown command',
                'unknown command',
                'Invalid command',
                'invalid command',
                'There is no matched command',
            ]);
        };

        $isInvalidPortResponse = static function (string $resp) use ($containsAny): bool {
            return $containsAny($resp, [
                '% Unknown',
                '% Invalid',
                'Unknown command',
                'unknown command',
                'Invalid command',
                'invalid command',
                'Incorrect F/S parameters',
                'Incorrect F/S parameter',
                'Failure: input parameter',
            ]);
        };

        $extractOntIds = function (string $resp): array {
            $ids = [];
            foreach (explode("\n", $this->cleanTelnet($resp)) as $line) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }

                if (preg_match('/^\d+\/\d+\s+\d+\s+(\d+)\s+[A-Za-z0-9]{8,20}\b/i', $line, $m)) {
                    $ids[] = (int) $m[1];
                    continue;
                }

                if (preg_match('/^(\d+)\s+[A-Za-z0-9]{8,20}\b/i', $line, $m)) {
                    $ids[] = (int) $m[1];
                }
            }

            return array_values(array_unique($ids));
        };

        $hasOpticalPayload = function (string $resp) use ($containsAny, $isInvalidPortResponse, $isUnsupportedResponse): bool {
            if ($isUnsupportedResponse($resp) || $isInvalidPortResponse($resp)) {
                return false;
            }

            $clean = trim($this->cleanTelnet($resp));
            if ($clean === '') {
                return false;
            }

            if ($containsAny($clean, [
                'There is no ONT available',
                'No related information to show',
                'No data',
            ])) {
                return false;
            }

            return
                (bool) preg_match('/Rx\s+power|Tx\s+power|Rx\s+Optical\s+Power|Tx\s+Optical\s+Power/i', $clean) ||
                (bool) preg_match('/^\d+\/\d+\s+\d+\s+\d+\b.*-?\d+\.\d+/m', $clean) ||
                (bool) preg_match('/^\d+\b.*-?\d+\.\d+/m', $clean);
        };

        // Scan all ports first so probing uses a port that actually contains ONTs.
        $consecutiveInvalid = 0;
        for ($port = 0; $port < $maxPorts; $port++) {
            $this->telnetSend($sock, "show ont info 0/0 {$port} all");
            $resp = $this->telnetReadMore($sock, 8);

            if ($isInvalidPortResponse($resp)) {
                $diagnosticNotes[] = "info 0/0/{$port}: invalid-port";
                $consecutiveInvalid++;
                if ($consecutiveInvalid >= 3) {
                    break;
                }
                continue;
            }

            $consecutiveInvalid = 0;
            $infoRaw .= "### PON 0/0/{$port} ###\n" . $resp . "\n";

            $ontIds = $extractOntIds($resp);
            if (!empty($ontIds)) {
                $portsWithOnts[] = $port;
                $ontIdsByPort[$port] = $ontIds;
                $diagnosticNotes[] = sprintf(
                    'info 0/0/%d: ont-data ids=%s',
                    $port,
                    implode(',', $ontIds)
                );
                if ($opticalProbePort === null) {
                    $opticalProbePort = $port;
                }
            } else {
                $diagnosticNotes[] = "info 0/0/{$port}: no-ont-rows";
            }
        }

        $usedPerPort = !empty(trim($infoRaw));

        if (!empty($portsWithOnts)) {
            $portsWithOnts = array_values(array_unique($portsWithOnts));

            // Pull per-ONT details from exec mode so we can extract Distance(m).
            foreach ($portsWithOnts as $port) {
                foreach (($ontIdsByPort[$port] ?? []) as $ontId) {
                    $this->telnetSend($sock, "show ont info 0/0 {$port} {$ontId}");
                    $detailResp = $this->telnetReadMore($sock, 6);
                    $detailRaw .= "### ONT 0/0/{$port}/{$ontId} ###\n" . $detailResp . "\n";

                    if (preg_match('/Distance\(m\)\s*:\s*(\d+)/i', $this->cleanTelnet($detailResp), $m)) {
                        $distanceByKey["0/0/{$port}/{$ontId}"] = (int) $m[1];
                    }
                }
            }

            // C-Data optical-info only works inside GPON interface configuration mode.
            $this->telnetSend($sock, 'config');
            $configResp = $this->telnetReadMore($sock, 3);
            $this->telnetSend($sock, 'interface gpon 0/0');
            $gponResp = $this->telnetReadMore($sock, 3);
            $enteredGponMode = !$isUnsupportedResponse($gponResp)
                && !str_contains($gponResp, 'There is no matched command')
                && !str_contains($gponResp, 'Unknown command');
            $diagnosticNotes[] = 'enter config: ' . substr(preg_replace('/\s+/', ' ', trim($this->cleanTelnet($configResp))), 0, 120);
            $diagnosticNotes[] = 'enter interface gpon 0/0: ' . substr(preg_replace('/\s+/', ' ', trim($this->cleanTelnet($gponResp))), 0, 120);

            if ($enteredGponMode) {
                $this->telnetSend($sock, "show ont optical-info {$opticalProbePort} all");
                $probeResp = $this->telnetReadMore($sock, 8);
                $supportsOptical = $hasOpticalPayload($probeResp);
                $diagnosticNotes[] = sprintf(
                    'probe optical-info %d/all: %s | %s',
                    $opticalProbePort,
                    $supportsOptical ? 'usable' : 'unsupported',
                    substr(preg_replace('/\s+/', ' ', trim($this->cleanTelnet($probeResp))), 0, 180)
                );
                Log::info('[CData] optical-info probe', [
                    'olt'      => $this->olt->name,
                    'port'     => $opticalProbePort,
                    'supports' => $supportsOptical,
                    'probe'    => substr(trim($probeResp), 0, 120),
                ]);

                if ($supportsOptical) {
                    $opticalResponses[$opticalProbePort] = $probeResp;
                }

                foreach ($portsWithOnts as $port) {
                    if (!$supportsOptical) {
                        break;
                    }

                    if (isset($opticalResponses[$port])) {
                        $optResp = $opticalResponses[$port];
                    } else {
                        $this->telnetSend($sock, "show ont optical-info {$port} all");
                        $optResp = $this->telnetReadMore($sock, 8);
                    }

                    if ($hasOpticalPayload($optResp)) {
                        $opticalRaw .= "### PON 0/0/{$port} ###\n" . $optResp . "\n";
                        $diagnosticNotes[] = "optical-info {$port}/all: captured";
                    } else {
                        $diagnosticNotes[] = sprintf(
                            'optical-info %d/all: empty-or-unsupported | %s',
                            $port,
                            substr(preg_replace('/\s+/', ' ', trim($this->cleanTelnet($optResp))), 0, 180)
                        );
                    }
                }

                $this->telnetSend($sock, 'exit');
                $this->telnetReadMore($sock, 2);
                $this->telnetSend($sock, 'exit');
                $this->telnetReadMore($sock, 2);
            } else {
                $supportsOptical = false;
                $diagnosticNotes[] = 'optical-info skipped: unable to enter config-gpon-0/0';
            }

            // Keep a final fallback for firmware variants that expose optical values in global run-info.
            if (trim($opticalRaw) === '') {
                $this->telnetSend($sock, 'show ont run-info all');
                $runInfoFallback = $this->telnetReadMore($sock, 10);
                if ($hasOpticalPayload($runInfoFallback)) {
                    $opticalRaw = $runInfoFallback;
                    $diagnosticNotes[] = 'global run-info all: captured';
                } else {
                    $diagnosticNotes[] = sprintf(
                        'global run-info all: empty-or-unsupported | %s',
                        substr(preg_replace('/\s+/', ' ', trim($this->cleanTelnet($runInfoFallback))), 0, 180)
                    );
                }
            }

            foreach ($portsWithOnts as $port) {
                $this->telnetSend($sock, "show ont version 0/0 {$port} all");
                $verResp = $this->telnetReadMore($sock, 5);
                if (!$isUnsupportedResponse($verResp) && !$isInvalidPortResponse($verResp)) {
                    $versionRaw .= "### PON 0/0/{$port} ###\n" . $verResp . "\n";
                }
            }
        }

        // Global fallback if the device does not support the per-port path.
        // This handles C-Data models where board number is NOT 0/0 (e.g. FD1604E-C1 uses 0/1).
        if (!$usedPerPort) {
            $this->telnetSend($sock, 'show ont info all');
            $infoRaw = $this->telnetReadMore($sock, 10);

            // Auto-detect board (F/S) and ports from global output.
            // Global format: "  0/1 1  1  SERIALNUMBER  Active  Online  ..."
            //                     ^^^  ^ = F/S=0/1, Port=1, ONT-ID=1
            $detectedFs           = null;
            $fallbackPortsWithOnts = [];
            $fallbackOntIdsByPort  = [];
            foreach (explode("\n", $this->cleanTelnet($infoRaw)) as $line) {
                if (preg_match('/^\s*(\d+\/\d+)\s+(\d+)\s+(\d+)\s+[A-Za-z0-9]{8,}/i', $line, $m)) {
                    $detectedFs = $m[1];
                    $port       = (int) $m[2];
                    $ontId      = (int) $m[3];
                    if (!in_array($port, $fallbackPortsWithOnts, true)) {
                        $fallbackPortsWithOnts[] = $port;
                    }
                    $fallbackOntIdsByPort[$port][] = $ontId;
                }
            }
            $diagnosticNotes[] = sprintf(
                'fallback: detected fs=%s ports=%s',
                $detectedFs ?? '-',
                implode(',', $fallbackPortsWithOnts)
            );

            // Get distance per ONT using detected F/S + port
            if ($detectedFs !== null && !empty($fallbackPortsWithOnts)) {
                foreach ($fallbackPortsWithOnts as $port) {
                    foreach (($fallbackOntIdsByPort[$port] ?? []) as $ontId) {
                        $this->telnetSend($sock, "show ont info {$detectedFs} {$port} {$ontId}");
                        $detailResp  = $this->telnetReadMore($sock, 6);
                        $detailRaw  .= "### ONT {$detectedFs}/{$port}/{$ontId} ###\n" . $detailResp . "\n";
                        if (preg_match('/Distance\(m\)\s*:\s*(\d+)/i', $this->cleanTelnet($detailResp), $dm)) {
                            $distanceByKey["{$detectedFs}/{$port}/{$ontId}"] = (int) $dm[1];
                        }
                    }
                }

                // Enter config → gpon mode using detected F/S (NOT hardcoded 0/0)
                $this->telnetSend($sock, 'config');
                $this->telnetReadMore($sock, 3);
                $this->telnetSend($sock, "interface gpon {$detectedFs}");
                $gponFallbackResp = $this->telnetReadMore($sock, 3);
                $diagnosticNotes[] = 'fallback: enter interface gpon ' . $detectedFs . ': '
                    . substr(preg_replace('/\s+/', ' ', trim($this->cleanTelnet($gponFallbackResp))), 0, 80);

                $enteredGponFallback = !$isUnsupportedResponse($gponFallbackResp)
                    && !str_contains($gponFallbackResp, 'There is no matched command')
                    && !str_contains($gponFallbackResp, 'Unknown command');

                if ($enteredGponFallback) {
                    foreach ($fallbackPortsWithOnts as $port) {
                        $this->telnetSend($sock, "show ont optical-info {$port} all");
                        $optResp = $this->telnetReadMore($sock, 8);
                        if ($hasOpticalPayload($optResp)) {
                            // Use ### PON {fs}/{port} ### header so parseCDataOutput can resolve correctly
                            $opticalRaw    .= "### PON {$detectedFs}/{$port} ###\n" . $optResp . "\n";
                            $diagnosticNotes[] = "fallback optical-info {$detectedFs}/{$port}/all: captured";
                        } else {
                            $diagnosticNotes[] = sprintf(
                                'fallback optical-info %s/%d/all: empty | %s',
                                $detectedFs,
                                $port,
                                substr(preg_replace('/\s+/', ' ', trim($this->cleanTelnet($optResp))), 0, 120)
                            );
                        }
                    }
                    $this->telnetSend($sock, 'exit');
                    $this->telnetReadMore($sock, 2);
                } else {
                    $diagnosticNotes[] = "fallback: unable to enter interface gpon {$detectedFs} — optical skipped";
                }
            }

            // Last resort: try global run-info if optical is still empty
            if (trim($opticalRaw) === '') {
                $this->telnetSend($sock, 'show ont run-info all');
                $runInfoFallback = $this->telnetReadMore($sock, 10);
                if ($hasOpticalPayload($runInfoFallback)) {
                    $opticalRaw = $runInfoFallback;
                    $diagnosticNotes[] = 'global run-info all: captured';
                } else {
                    $diagnosticNotes[] = sprintf(
                        'global run-info all: empty-or-unsupported | %s',
                        substr(preg_replace('/\s+/', ' ', trim($this->cleanTelnet($runInfoFallback))), 0, 180)
                    );
                }
            }

            $this->telnetSend($sock, 'show ont version all');
            $versionRaw = $this->telnetReadMore($sock, 10);
        }

        $this->telnetLogout($sock);

        @file_put_contents(
            storage_path('logs/cdata_optical_raw.txt'),
            "=== " . date('Y-m-d H:i:s')
            . " | stage=fetch | usedPerPort=" . ($usedPerPort ? 'true' : 'false')
            . " | probePort=" . ($opticalProbePort === null ? '-' : $opticalProbePort)
            . " | supportsOptical=" . ($supportsOptical === null ? 'unknown' : ($supportsOptical ? 'true' : 'false'))
            . " | portsWithOnts=" . implode(',', $portsWithOnts) . " ===\n"
            . "--- NOTES ---\n" . implode("\n", $diagnosticNotes) . "\n"
            . "--- INFO RAW (cleaned) ---\n" . $this->cleanTelnet($infoRaw) . "\n"
            . "--- DETAIL RAW (cleaned) ---\n" . $this->cleanTelnet($detailRaw) . "\n"
            . "--- OPTICAL RAW (cleaned) ---\n" . $this->cleanTelnet($opticalRaw) . "\n"
            . "--- VERSION RAW (cleaned) ---\n" . $this->cleanTelnet($versionRaw) . "\n\n",
            FILE_APPEND
        );

        // Keep the structured logs too for environments where info/debug is enabled.
        Log::debug('[CData RAW] info raw', [
            'olt'              => $this->olt->name,
            'per_port'         => $usedPerPort,
            'supports_optical' => $supportsOptical,
            'info_raw'         => substr($infoRaw, 0, 3000),
        ]);
        Log::debug('[CData RAW] optical raw', [
            'optical_raw' => substr($opticalRaw, 0, 2000),
        ]);
        Log::debug('[CData RAW] version raw', [
            'version_raw' => substr($versionRaw, 0, 1000),
        ]);

        $records = $this->parseCDataOutput($infoRaw, $opticalRaw, $versionRaw, $usedPerPort);
        foreach ($records as &$record) {
            $key = ($record['pon_port'] ?? '') . '/' . ($record['olt_port_index'] ?? '');
            if (isset($distanceByKey[$key])) {
                $record['distance'] = $distanceByKey[$key];
            }
        }
        unset($record);

        return $records;
    }

    /**
     * Parse C-Data OLT output into an array of ONT records.
     *
     * @param  string  $infoRaw     Output of "show ont info" (may be per-port or global)
     * @param  string  $opticalRaw  Output of "show ont optical-info" (per-port) OR "show ont run-info all" (global fallback)
     * @param  string  $versionRaw  Output of "show ont version"
     * @param  bool    $perPort     True = per-port format with "### PON 0/0/N ###" headers
     */
    private function parseCDataOutput(
        string $infoRaw,
        string $opticalRaw = '',
        string $versionRaw = '',
        bool   $perPort = false  // true = infoRaw contains ### PON ### headers
    ): array {
        $ontMap     = [];
        // currentFs tracks the frame/slot (e.g. "0/0") from section headers or full-format lines
        // currentPon tracks the PON port number (e.g. "3") from section headers or full-format lines
        $currentFs      = '0';
        $currentPonPort = '0';

        foreach (explode("\n", $this->cleanTelnet($infoRaw)) as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Per-port section header: ### PON 0/0/3 ###  or  ### PON 0/0 3 ###
            if ($perPort && preg_match('/###\s*PON\s+(\d+)\/(\d+)\/(\d+)\s*###/i', $line, $ph)) {
                // ph[1]/ph[2] = frame/slot (e.g. 0/0), ph[3] = pon port
                $currentFs      = "{$ph[1]}/{$ph[2]}";
                $currentPonPort = $ph[3];
                continue;
            }

            // Skip separator/header lines
            if (str_starts_with($line, '-') || preg_match('/^\s*F[\/\s]S/i', $line)) continue;

            // ── Format A (global & per-port, FD1608S/FD1616S) ─────────────────
            // Full format:  F/S   PON  ONT  SN  ...
            // Example:      0/0    1    1   TDTC35B1F930   Active  Online  success match dying-gasp  N-006
            //
            // Short per-port format ("show ont info 0/0 3 all"):
            // Columns:      ONT  SN  ...
            // Example:      1    TDTC35B1F930    Active    Online    success ...

            $fs = null; $pon = null; $ontId = null; $sn = null; $rest = '';

            if (preg_match(
                '/^\s*(\d+\/\d+)\s+(\d+)\s+(\d+)\s+([A-Za-z0-9]{8,20})\s+(.*)/i',
                $line, $base
            )) {
                // Full format: F/S PON ONT SN ...
                [$fs, $pon, $ontId, $sn, $rest] = [$base[1], $base[2], $base[3], strtoupper($base[4]), $base[5]];
                // Update current tracking for subsequent short-format lines
                $currentFs      = $fs;
                $currentPonPort = $pon;

            } elseif ($perPort && preg_match(
                '/^\s*(\d+)\s+([A-Za-z0-9]{8,20})\s+(.*)/i',
                $line, $base2
            )) {
                // Short per-port format: ONT SN ...
                // Use F/S and PON derived from the most recent section header
                $ontId = $base2[1];
                $sn    = strtoupper($base2[2]);
                $rest  = $base2[3];
                $fs    = $currentFs;
                $pon   = $currentPonPort;

            } else {
                continue;
            }

            // Determine status from rest of line
            if (!preg_match('/\b(Online|Offline)\b/i', $rest, $statusMatch)) continue;
            $status = strtolower($statusMatch[1]) === 'online' ? 'online' : 'offline';

            // Extract description: text after status + skip 0–4 middleware tokens
            $afterStatus = preg_replace('/^.*?\b(?:Online|Offline)\b\s*/i', '', $rest);
            $desc        = preg_replace('/^(?:\S+\s+){0,4}/', '', $afterStatus);
            $desc        = trim($desc);
            if ($desc === '' || $desc === '--' || $desc === '-') {
                $desc = null;
            }

            $key = "{$fs}/{$pon}/{$ontId}";
            $ontMap[$key] = [
                'serial_number'    => strtoupper($sn),
                'equipment_id'     => null,
                'pon_port'         => "{$fs}/{$pon}",
                'olt_port_index'   => (int) $ontId,
                'status'           => $status,
                'rx_power'         => null,
                'tx_power'         => null,
                'distance'         => null,
                'description'      => $desc,
                'firmware_version' => null,
            ];
        }

        // ── DIAGNOSTIC: log all keys built from info ──────────────────────────
        Log::debug('[CData PARSE] ontMap keys after info parse', [
            'perPort'     => $perPort,
            'keys'        => array_keys($ontMap),
            'count'       => count($ontMap),
            'opticalLen'  => strlen($opticalRaw),
        ]);

        // Dump raw optical to a dedicated debug file so we can diagnose format issues
        $debugFile = storage_path('logs/cdata_optical_raw.txt');
        @file_put_contents($debugFile,
            "=== " . date('Y-m-d H:i:s') . " | perPort=" . ($perPort?'true':'false')
            . " | ontMap=" . count($ontMap) . " ===\n"
            . "--- INFO RAW (cleaned) ---\n" . $this->cleanTelnet($infoRaw) . "\n"
            . "--- OPTICAL RAW (cleaned) ---\n" . $this->cleanTelnet($opticalRaw) . "\n\n",
            FILE_APPEND
        );

        // Build a SN→key lookup for fallback matching
        $snIndex = [];  // uppercase SN => key in $ontMap
        foreach ($ontMap as $k => $o) {
            $snIndex[strtoupper($o['serial_number'])] = $k;
        }

        // ── Parse optical power ───────────────────────────────────────────────
        // Handles multiple formats:
        //   A) Per-port optical-info inline:  "1  -20.50  3.50"
        //   B) run-info global inline:        "0/0  1  1  SN  Online  -20.50  3.50  123m"
        //   C) Per-port run-info inline:      "1  SN  Online  -20.50  3.50  123m"
        //   D) Global optical-info inline:    "0/0  1  1  -20.50  3.50"
        //   E) Multi-line key-value (FD1616S-B2):
        //        ONT: 0/0/1
        //          Rx Optical Power(dBm)  : -20.50
        //          Tx Optical Power(dBm)  :   3.50
        //          Distance(m)            :  171
        if ($opticalRaw) {
            $curOptFs  = '0/0';
            $curOptPon = '0';

            // ── Detect Format E: multi-line key-value (FD1616S-B2) ──────────────
            // Heuristic: if output contains "Rx Optical Power" or "Tx Optical Power" labels
            // the entire block uses key-value formatting, NOT inline columns.
            $isMultiLineOptical = (bool) preg_match(
                '/Rx\s+Optical\s+Power|Rx\s+Power\s*\(dBm\)|Tx\s+Optical\s+Power|Tx\s+Power\s*\(dBm\)/i',
                $opticalRaw
            );

            if ($isMultiLineOptical) {
                // ── Format E: multi-line key-value blocks ─────────────────────────
                // ONT block header variants:
                //   "ONT: 0/0/1"   "ONT 0/0/1"   "ONT-Index: 0/0/1"
                //   "ONU: 0/0/1"   "ONUID: 0/0/1"
                // Followed by indented key-value lines:
                //   "  Rx Optical Power(dBm) : -20.50"
                //   "  Tx Optical Power(dBm) :   3.50"
                //   "  Distance(m)           :  171"
                $curKey = null;
                foreach (explode("\n", $this->cleanTelnet($opticalRaw)) as $line) {
                    $line = trim($line);
                    if (empty($line)) continue;

                    // Section header injected by fetchCData: ### PON 0/0/3 ###
                    if (preg_match('/###\s*PON\s+(\d+)\/(\d+)\/(\d+)\s*###/i', $line, $ph)) {
                        $curOptFs  = "{$ph[1]}/{$ph[2]}";
                        $curOptPon = $ph[3];
                        $curKey    = null;
                        continue;
                    }

                    // ONT block header with full F/S/PON/ONT index:
                    //   "ONT: 0/0/1"  "ONU 0/0/1"  "ONUID: 0/0/1"
                    if (preg_match(
                        '/^(?:ONT|ONU|ONUID|ONT[-\s]?(?:Index|ID))\s*[:\s]+(?:ONT[-\s]?)?\s*(\d+)\/(\d+)\/(\d+)/i',
                        $line, $blk
                    )) {
                        $curKey    = "{$blk[1]}/{$blk[2]}/{$blk[3]}";
                        $curOptFs  = "{$blk[1]}/{$blk[2]}";
                        $curOptPon = $blk[3];
                        // Validate key exists; try SN fallback if not
                        if (!isset($ontMap[$curKey])) {
                            if (preg_match('/\b([A-Za-z0-9]{8,16})\b/', $line, $snTok)) {
                                $snUp = strtoupper($snTok[1]);
                                if (isset($snIndex[$snUp])) $curKey = $snIndex[$snUp];
                            }
                        }
                        continue;
                    }
                    // Per-port short block header: "ONT-Index: 1"  or  "Index: 1"
                    if (preg_match(
                        '/^(?:ONT[-\s]?(?:Index|ID)|ONUID|Index)\s*:\s*(\d+)\s*$/i',
                        $line, $blk2
                    )) {
                        $ontId  = $blk2[1];
                        $curKey = "{$curOptFs}/{$curOptPon}/{$ontId}";
                        continue;
                    }

                    if (!$curKey || !isset($ontMap[$curKey])) continue;

                    // "Rx Optical Power(dBm)  : -20.50"  or  "Rx Power(dBm) : -20.50"
                    // NOTE: skip lines containing "OLT" ("OLT Rx Power" is the OLT-side measurement, not ONT)
                    if (!preg_match('/\bOLT\b/i', $line) && preg_match(
                        '/Rx\s+(?:Optical\s+)?Power\s*(?:\(dBm\))?\s*:\s*(-?\d+\.?\d*)/i',
                        $line, $rxm
                    )) {
                        $ontMap[$curKey]['rx_power'] = (float) $rxm[1];
                    }
                    // "Tx Optical Power(dBm)  :   3.50"  or  "Tx Power(dBm) : 3.50"
                    if (preg_match(
                        '/Tx\s+(?:Optical\s+)?Power\s*(?:\(dBm\))?\s*:\s*(-?\d+\.?\d*)/i',
                        $line, $txm
                    )) {
                        $ontMap[$curKey]['tx_power'] = (float) $txm[1];
                    }
                    // "Distance(m)  : 171"  or  "Distance : 171 m"  or  "Distance : 171"
                    if (preg_match('/Distance\s*(?:\(m\))?\s*:\s*(\d+)/i', $line, $distm)) {
                        $ontMap[$curKey]['distance'] = (int) $distm[1];
                    }
                }

            } else {
                // ── Formats A/B/C/D: inline float scanning per line ───────────────
                foreach (explode("\n", $this->cleanTelnet($opticalRaw)) as $line) {
                $line = trim($line);
                if (empty($line)) continue;

                // Section header: ### PON 0/0/3 ###
                if (preg_match('/###\s*PON\s+(\d+)\/(\d+)\/(\d+)\s*###/i', $line, $ph)) {
                    $curOptFs  = "{$ph[1]}/{$ph[2]}";
                    $curOptPon = $ph[3];
                    continue;
                }

                // Skip header/separator lines
                if (str_starts_with($line, '-') ||
                    preg_match('/^\s*(ONT|No\.|Index)\b/i', $line) ||
                    preg_match('/^\s*F[\/\s]/i', $line)) continue;

                $key = null;

                // Format B/D: F/S PON ONT [SN] [...]
                if (preg_match('/^\s*(\d+\/\d+)\s+(\d+)\s+(\d+)\b/', $line, $hdr)) {
                    $key = "{$hdr[1]}/{$hdr[2]}/{$hdr[3]}";
                    $curOptFs  = $hdr[1];
                    $curOptPon = $hdr[2];

                // Format A/C: ONT [SN] [...] — per-port (short)
                } elseif (preg_match('/^\s*(\d+)\b/', $line, $hdr2)) {
                    $key = "{$curOptFs}/{$curOptPon}/{$hdr2[1]}";
                }

                // SN-based fallback: scan line for any known SN
                if ($key && !isset($ontMap[$key])) {
                    // Extract candidate tokens that look like serial numbers
                    if (preg_match('/\b([A-Za-z0-9]{8,16})\b/', $line, $snTok)) {
                        $snUp = strtoupper($snTok[1]);
                        if (isset($snIndex[$snUp])) {
                            $key = $snIndex[$snUp];
                        }
                    }
                }

                Log::debug('[CData PARSE] optical line', [
                    'line'     => $line,
                    'key'      => $key,
                    'keyFound' => $key ? isset($ontMap[$key]) : false,
                ]);

                if (!$key || !isset($ontMap[$key])) continue;

                // Scan floats in dBm range (-45..10 dBm)
                preg_match_all('/(-?\d+\.\d+)/', $line, $floats);
                $powers = array_values(
                    array_filter($floats[1] ?? [], function ($v) {
                        $f = (float) $v;
                        return $f >= -45.0 && $f <= 10.0;
                    })
                );
                if (count($powers) >= 1) $ontMap[$key]['rx_power'] = (float) $powers[0];
                if (count($powers) >= 2) $ontMap[$key]['tx_power'] = (float) $powers[1];

                // Scan distance
                if (preg_match('/(\d+)\s*m\b/i', $line, $distM)) {
                    $ontMap[$key]['distance'] = (int) $distM[1];
                } elseif (preg_match_all('/\b(\d+)\b/', $line, $allInts)) {
                    $candidates = array_filter($allInts[1], fn($v) => (int)$v > 10 && (int)$v < 100000);
                    if (!empty($candidates)) {
                        $ontMap[$key]['distance'] = (int) end($candidates);
                    }
                }
                }
            }
        }

        // ── Parse firmware version ────────────────────────────────────────────
        // Per-port (show ont version 0/0 N all): "1  V3.8.1-210805  2021-08-05"
        // Global   (show ont version all):        "0/0  1  1  V3.8.1  2021-08-05"
        if ($versionRaw) {
            $curVerFs  = '0/0';
            $curVerPon = '0';
            foreach (explode("\n", $this->cleanTelnet($versionRaw)) as $line) {
                $line = trim($line);
                if (empty($line)) continue;

                // Section header: ### PON 0/0/3 ###
                if ($perPort && preg_match('/###\s*PON\s+(\d+)\/(\d+)\/(\d+)\s*###/i', $line, $ph)) {
                    $curVerFs  = "{$ph[1]}/{$ph[2]}";
                    $curVerPon = $ph[3];
                    continue;
                }

                if (str_starts_with($line, '-') || preg_match('/^\s*ONT/i', $line)) continue;

                $key = null;
                $fw  = null;

                if (preg_match('/^\s*(\d+\/\d+)\s+(\d+)\s+(\d+)\s+(\S+)/i', $line, $vm)) {
                    $key = "{$vm[1]}/{$vm[2]}/{$vm[3]}";
                    $fw  = trim($vm[4]);
                    $curVerFs  = $vm[1];
                    $curVerPon = $vm[2];
                } elseif ($perPort && preg_match('/^\s*(\d+)\s+(\S+)/i', $line, $vm2)) {
                    $key = "{$curVerFs}/{$curVerPon}/{$vm2[1]}";
                    $fw  = trim($vm2[2]);
                }

                if ($key && $fw && isset($ontMap[$key])) {
                    $ontMap[$key]['firmware_version'] = $fw;
                }
            }
        }

        return array_values($ontMap);
    }

    // ─── HSGQ G02ID Telnet Fetch ─────────────────────────────────────────────

    private function fetchHsgq(): array
    {
        $maxAttempts = 3;
        $lastException = null;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $sock = null;
            try {
                $sock = $this->telnetConnect();
                $this->telnetLogin($sock);

                $this->telnetSend($sock, 'enable');
                $this->telnetReadUntil($sock, ['#'], 5);

                // Enter configure mode first (HSGQ G02ID requires this for show ont-* commands)
                $this->telnetSend($sock, 'configure');
                $this->telnetReadUntil($sock, ['(config)#', '(config)>', '#'], 5);

                $this->telnetSend($sock, 'show ont-info all');
                $infoRaw = $this->telnetReadMore($sock, 12);

                $this->telnetSend($sock, 'show ont-optical all');
                $opticalRaw = $this->telnetReadMore($sock, 12);

                $this->telnetLogout($sock);
                return $this->parseHsgqOutput($infoRaw, $opticalRaw);

            } catch (\RuntimeException $e) {
                if ($sock) { @fclose($sock); }
                $lastException = $e;
                Log::warning("[HSGQ] Attempt {$attempt}/{$maxAttempts} failed: " . $e->getMessage() . " — " . ($attempt < $maxAttempts ? "retrying in 5s..." : "giving up."));
                if ($attempt < $maxAttempts) {
                    sleep(5);
                }
            }
        }

        throw $lastException;
    }

    private function parseHsgqOutput(string $infoRaw, string $opticalRaw): array
    {
        // Parse: show ont-info all
        // Format: PON/ONU  ONU-Type  Serial  State  RunState  ConfigState  MatchState  LastLinkDown  ONT-Name
        // Example: 1/0  GPON  XPON535c2384  Active  Online  normal  Initial  -  NTSM-014
        $ontMap = [];

        foreach (explode("\n", $this->cleanTelnet($infoRaw)) as $line) {
            $line = trim($line);
            // Match lines like: 1/0  GPON  XPON535c2384  Active  Online  ...
            if (!preg_match('/^(\d+)\/(\d+)\s+GPON\s+([A-Za-z0-9]{8,20})\s+(\w+)\s+(\w+)/i', $line, $m)) {
                continue;
            }
            $pon     = (int) $m[1];
            $onuId   = (int) $m[2];
            $serial  = strtoupper($m[3]);
            $state   = strtolower($m[4]); // Active / Inactive
            $runState = strtolower($m[5]); // Online / Initial / etc

            // Determine description (ONT Name) — last word(s) on the line
            $description = null;
            if (preg_match('/\s{2,}(\S+)\s*$/', $line, $dm)) {
                $description = trim($dm[1]);
            }

            $key = "{$pon}/{$onuId}";
            $ontMap[$key] = [
                'serial_number'    => $serial,
                'equipment_id'     => null,
                'pon_port'         => "{$pon}/0",
                'olt_port_index'   => $onuId,
                'status'           => ($state === 'active' && $runState === 'online') ? 'online' : 'offline',
                'rx_power'         => null,
                'tx_power'         => null,
                'distance'         => null,
                'description'      => $description !== '-' ? $description : null,
                'firmware_version' => null,
            ];
        }

        // Parse: show ont-optical all
        // Format: PON/ONU  ONT-SN  Temp  Voltage  Bias  Tx power  Rx power  ONT-Name
        // Example:     1/0 XPON535c2384 33 C 3.40 V  13.30 mA 2.7060 dBm  -19.5080 dBm NTSM-014
        foreach (explode("\n", $this->cleanTelnet($opticalRaw)) as $line) {
            $line = trim($line);
            // Match: 1/0 XPONXXXXXXXX ... TX_dBm ... RX_dBm
            if (!preg_match('/^(\d+)\/(\d+)\s+([A-Za-z0-9]{8,20})\s+.*?([-\d.]+)\s+dBm\s+([-\d.]+)\s+dBm/i', $line, $m)) {
                continue;
            }
            $key = $m[1] . '/' . $m[2];
            if (isset($ontMap[$key])) {
                $ontMap[$key]['tx_power'] = (float) $m[4];
                $ontMap[$key]['rx_power'] = (float) $m[5];
            }
        }

        return array_values($ontMap);
    }


    /**
     * Brand-aware SNMP OID definitions.
     * Returns [ serialOid, statusOid, rxPowerOid, descOid, ponPortPrefix ]
     */
    private function snmpOids(): array
    {
        if ($this->isHsgq()) {
            // HSGQ G02ID enterprise OIDs (1.3.6.1.4.1.50224)
            // Instance ID is a flat 32-bit int: slot<<24 | 0<<16 | pon<<8 | onu
            return [
                'serial'        => '1.3.6.1.4.1.50224.3.12.2.1.15', // XPON serial string
                'status'        => '1.3.6.1.4.1.50224.3.12.2.1.4',  // INTEGER 1=online 0=offline
                'rx_power'      => null,                              // optical table has its own OID
                'rx_oid'        => '1.3.6.1.4.1.50224.3.12.3.1.4',  // Rx Power dBm*100, suffix .inst.0.0
                'tx_oid'        => '1.3.6.1.4.1.50224.3.12.3.1.4',  // same table, suffix .inst.65535.65535
                'desc'          => '1.3.6.1.4.1.50224.3.12.2.1.13', // ONT internal name
                'distance'      => '1.3.6.1.4.1.50224.3.12.2.1.19', // meters INTEGER
                'pon_fmt'       => '1/',
                'instance_flat' => true,  // flat int rather than .pon.onu suffix
            ];
        }

        if ($this->isCData()) {
            // C-Data FD series enterprise OIDs (1.3.6.1.4.1.34592)
            return [
                'serial'   => '1.3.6.1.4.1.34592.1.4.4.3.1.2',
                'status'   => '1.3.6.1.4.1.34592.1.4.4.3.1.1',
                'rx_power' => '1.3.6.1.4.1.34592.1.4.4.3.1.9',
                'desc'     => '1.3.6.1.4.1.34592.1.4.4.3.1.7',
                'distance' => null,
                'pon_fmt'  => '1/',
                'instance_flat' => false,
            ];
        }

        // Default: Tenda TES7001 enterprise OIDs (1.3.6.1.4.1.3902)
        return [
            'serial'        => '1.3.6.1.4.1.3902.1012.3.28.1.1.2',
            'status'        => '1.3.6.1.4.1.3902.1012.3.28.1.1.1',
            'rx_power'      => '1.3.6.1.4.1.3902.1012.3.28.2.1.3',
            'desc'          => '1.3.6.1.4.1.3902.1012.3.28.1.1.11',
            'distance'      => null,
            'pon_fmt'       => '1/',
            'instance_flat' => false,
        ];
    }

    private function fetchViaSNMP(): array
    {
        $ip        = $this->olt->ip_address;
        $community = $this->olt->snmp_community ?: 'public';
        $version   = $this->olt->snmp_version   ?: '2c';

        if (!function_exists('snmp2_walk') && !function_exists('snmpwalk')) {
            throw new \RuntimeException('PHP SNMP extension is not installed. Run: sudo apt install php-snmp');
        }

        $oids = $this->snmpOids();

        // CRITICAL: snmp2_walk returns indexed array (OIDs lost).
        //           snmp2_real_walk returns OID => value associative array — required for suffix extraction.
        $walkFn  = ($version === '1') ? 'snmprealwalk' : 'snmp2_real_walk';
        if (!function_exists($walkFn)) {
            $walkFn = 'snmprealwalk';  // final fallback
        }
        $serials = @$walkFn($ip, $community, $oids['serial'], 3000000, 1);

        if ($serials === false || empty($serials)) {
            throw new \RuntimeException(
                "SNMP walk failed for {$ip} (brand: {$this->olt->brand}). " .
                "Check community='{$community}' and SNMP enabled on OLT."
            );
        }

        $statuses  = @$walkFn($ip, $community, $oids['status'], 3000000, 1) ?: [];
        $rxValues  = ($oids['rx_power'] ?? null) ? (@$walkFn($ip, $community, $oids['rx_power'], 3000000, 1) ?: []) : [];
        $distVals  = ($oids['distance'] ?? null) ? (@$walkFn($ip, $community, $oids['distance'], 3000000, 1) ?: []) : [];
        $descs     = @$walkFn($ip, $community, $oids['desc'], 3000000, 1) ?: [];
        $onts      = [];
        $flatInst  = !empty($oids['instance_flat']);

        // OID-format-agnostic: build lookup maps indexed by the TRAILING portion
        // of each OID key. Works regardless of prefix (iso., enterprises., .1.3.6…).
        // HSGQ uses 1 trailing number (flat instance), Tenda/CData use 2 (.pon.onu).
        $trailParts = $flatInst ? 1 : 2;
        $trailPat   = '/(\.\d+){' . $trailParts . '}$/';

        $buildMap = static function (array $walk) use ($trailPat): array {
            $map = [];
            foreach ($walk as $k => $v) {
                if (preg_match($trailPat, (string) $k, $m)) {
                    $map[$m[0]] = $v;
                }
            }
            return $map;
        };

        $statusMap = $buildMap($statuses);
        $rxMap     = $buildMap($rxValues);
        $distMap   = $buildMap($distVals);
        $descMap   = $buildMap($descs);

        // HSGQ: optical power lives in a separate table (tbl3 col4).
        // Suffix is .instance.0.0 for Rx and .instance.65535.65535 for Tx.
        $hsgqRxMap = [];
        $hsgqTxMap = [];
        if ($flatInst && !empty($oids['rx_oid'])) {
            $opticalRaw = @$walkFn($ip, $community, $oids['rx_oid'], 5000000, 1) ?: [];
            foreach ($opticalRaw as $oKey => $oVal) {
                // Match .instanceId.0.0 (Rx) or .instanceId.65535.65535 (Tx)
                if (preg_match('/\.(\d+)\.0\.0$/', (string) $oKey)) {
                    // Rx power — extract instance ID
                    if (preg_match('/\.(\d+)\.0\.0$/', (string) $oKey, $oM)) {
                        $iid = '.' . $oM[1];
                        $hsgqRxMap[$iid] = ((float) preg_replace('/[^\-0-9]/', '', $oVal)) / 100;
                    }
                } elseif (preg_match('/\.(\d+)\.65535\.65535$/', (string) $oKey, $oM)) {
                    $iid = '.' . $oM[1];
                    $hsgqTxMap[$iid] = ((float) preg_replace('/[^\-0-9]/', '', $oVal)) / 100;
                }
            }
        }

        foreach ($serials as $oid => $value) {
            // Extract trailing suffix — e.g. '.16777482' (HSGQ) or '.1.2' (Tenda)
            if (!preg_match($trailPat, (string) $oid, $m)) continue;
            $suffix = $m[0];

            if ($flatInst) {
                // HSGQ: flat 32-bit instance, encoded as 0x01_00_PP_OO
                $instanceId = (int) ltrim($suffix, '.');
                $ponIdx     = ($instanceId >> 8) & 0xFF;     // bits15-8 = PON port
                $onuIdx     = ($instanceId & 0xFF) + 1;      // bits7-0 + 1 = ONU (web UI is 1-based)
            } else {
                preg_match('/\.(\d+)\.(\d+)$/', $suffix, $m2);
                $ponIdx = (int) ($m2[1] ?? 0);
                $onuIdx = (int) ($m2[2] ?? 0);
            }

            // Parse SN — may be plain STRING or Hex-STRING
            $sn = trim($value);
            $sn = preg_replace('/^(?:STRING|OCTET STRING):\s*/i', '', $sn);
            $sn = trim($sn, '"');
            if (preg_match('/^Hex-STRING:\s*([a-fA-F0-9\s]+)$/i', $value, $hexM)) {
                $hex   = preg_replace('/\s+/', '', $hexM[1]);
                $ascii = '';
                for ($i = 0; $i < strlen($hex); $i += 2) {
                    $ascii .= chr(hexdec(substr($hex, $i, 2)));
                }
                $sn = trim($ascii);
            }
            if (!$sn) continue;

            // Status: INTEGER 1 = online
            $statusRaw = $statusMap[$suffix] ?? '';
            $statusInt = (int) preg_replace('/[^0-9]/', '', $statusRaw);
            $status    = $statusInt === 1 ? 'online' : 'offline';

            // Rx power
            $rxPower = null;
            if (!empty($oids['rx_power']) && isset($rxMap[$suffix])) {
                $rxPower = ((float) preg_replace('/[^\-0-9]/', '', $rxMap[$suffix])) / 100;
            } elseif (isset($hsgqRxMap[$suffix])) {
                $rxPower = $hsgqRxMap[$suffix];
            }

            // Tx power (HSGQ only)
            $txPower = isset($hsgqTxMap[$suffix]) ? $hsgqTxMap[$suffix] : null;

            // Distance (meters)
            $distance = null;
            if (!empty($oids['distance']) && isset($distMap[$suffix])) {
                $distance = (int) preg_replace('/[^0-9]/', '', $distMap[$suffix]) ?: null;
            }

            // Description
            $desc = isset($descMap[$suffix])
                ? trim(preg_replace('/^STRING:\s*/i', '', $descMap[$suffix]), '"')
                : null;
            if ($desc === '' || $desc === '-') $desc = null;

            $onts[] = [
                'serial_number'    => strtoupper($sn),
                'pon_port'         => $oids['pon_fmt'] . $ponIdx,
                'olt_port_index'   => $onuIdx,
                'status'           => $status,
                'rx_power'         => $rxPower,
                'tx_power'         => $txPower,
                'distance'         => $distance,
                'description'      => $desc,
                'firmware_version' => null,
                'equipment_id'     => null,
            ];
        }

        return $onts;
    }

    // ─── SSH Adapter ─────────────────────────────────────────────────────────

    private function fetchViaSSH(): array
    {
        $ip   = $this->olt->ip_address;
        $user = $this->olt->ssh_user;
        $pass = $this->olt->ssh_pass;
        $port = $this->olt->ssh_port ?: 22;

        if (!$user || !$pass) {
            throw new \RuntimeException('SSH credentials not configured.');
        }

        $cmd = 'sshpass -p ' . escapeshellarg($pass) .
            " ssh -o StrictHostKeyChecking=no -o ConnectTimeout=5 -p {$port}" .
            ' ' . escapeshellarg($user) . "@{$ip}" .
            " 'show gpon onu state' 2>/dev/null";

        $output = [];
        exec($cmd, $output);

        return $this->parseGenericCLI(implode("\n", $output));
    }

    private function parseGenericCLI(string $raw): array
    {
        $onts = [];
        foreach (explode("\n", $raw) as $line) {
            $line = trim($line);
            if (empty($line) || str_starts_with($line, '-') || str_starts_with($line, '=')) continue;
            if (preg_match('/(?:gpon-onu_)?(\d+\/\d+):(\d+)\s+(\w{4,20})\s+(online|offline|active|inactive|deactive|working)/i', $line, $m)) {
                $onts[] = [
                    'serial_number'    => $m[3],
                    'pon_port'         => $m[1],
                    'olt_port_index'   => (int) $m[2],
                    'status'           => str_contains(strtolower($m[4]), 'on') ? 'online' : 'offline',
                    'rx_power'         => null,
                    'tx_power'         => null,
                    'distance'         => null,
                    'description'      => null,
                    'firmware_version' => null,
                    'equipment_id'     => null,
                ];
            }
        }
        return $onts;
    }

    // ─── REST API Adapter ────────────────────────────────────────────────────

    private function fetchViaREST(): array
    {
        $url   = rtrim($this->olt->api_url, '/');
        $token = $this->olt->api_token;

        if (!$url) {
            throw new \RuntimeException('REST API URL not configured.');
        }

        $headers  = $token ? ['Authorization' => "Bearer {$token}"] : [];
        $response = \Illuminate\Support\Facades\Http::timeout(10)
            ->withHeaders($headers)
            ->get("{$url}/api/onts");

        if (!$response->successful()) {
            throw new \RuntimeException('REST API HTTP ' . $response->status());
        }

        $data = $response->json('data', $response->json());
        $onts = [];

        foreach ($data as $item) {
            $onts[] = [
                'serial_number'    => $item['serial_number'] ?? $item['sn']       ?? '',
                'pon_port'         => $item['pon_port']       ?? $item['port']     ?? null,
                'olt_port_index'   => $item['index']          ?? $item['onu_id']   ?? null,
                'status'           => $item['status']                              ?? 'unknown',
                'rx_power'         => $item['rx_power']                            ?? null,
                'tx_power'         => $item['tx_power']                            ?? null,
                'distance'         => $item['distance']                            ?? null,
                'description'      => $item['description']                         ?? null,
                'firmware_version' => $item['firmware']                            ?? null,
                'equipment_id'     => $item['equipment_id']                        ?? null,
            ];
        }

        return $onts;
    }

    // ─── Telnet Helpers ──────────────────────────────────────────────────────

    private function telnetConnect()
    {
        $ip   = $this->olt->ip_address;
        $port = $this->olt->telnet_port ?: 23;

        $sock = @fsockopen($ip, $port, $errno, $errstr, 5);
        if (!$sock) {
            throw new \RuntimeException("Telnet connect failed: {$ip}:{$port} — {$errstr}");
        }
        stream_set_timeout($sock, 8);

        // Negotiate character mode: respond to IAC DO/WILL sequences
        // and request character-at-a-time mode (needed for HSGQ and some C-Data OLTs)
        // IAC WILL ECHO + IAC WILL SGA = tell server we handle echo & suppress-go-ahead
        @fwrite($sock, "\xff\xfb\x01\xff\xfb\x03");
        usleep(100000);
        // Drain any IAC negotiation bytes sent by the OLT
        @fread($sock, 512);

        return $sock;
    }

    private function telnetLogin($sock): void
    {
        $user = $this->olt->telnet_user ?: 'admin';
        $pass = $this->olt->telnet_pass ?: 'admin';

        $this->telnetReadUntil($sock, ['Username:', 'login:', 'name:', 'User:'], 5);
        $this->telnetSend($sock, $user);

        $this->telnetReadUntil($sock, ['Password:', 'assword:'], 5);
        $this->telnetSend($sock, $pass);

        $prompt = $this->telnetReadUntil($sock, ['>', '#', '$'], 5);

        if (str_ends_with(trim($prompt), '>')) {
            $this->telnetSend($sock, 'enable');
            $enableResp = $this->telnetReadUntil($sock, ['Password:', '#'], 5);
            if (str_contains($enableResp, 'assword')) {
                $this->telnetSend($sock, $pass);
                $this->telnetReadUntil($sock, ['#'], 5);
            }
        }
    }

    private function telnetLogout($sock): void
    {
        try {
            $this->telnetSend($sock, 'exit');
            usleep(200000);
            $this->telnetSend($sock, 'exit');
        } catch (\Throwable) {}
        @fclose($sock);
    }

    private function telnetSend($sock, string $command): void
    {
        $result = @fwrite($sock, $command . "\r\n");
        if ($result === false) {
            throw new \RuntimeException("Telnet send failed (broken pipe) — koneksi ke OLT terputus");
        }
    }

    private function telnetReadUntil($sock, array $prompts, int $timeoutSec): string
    {
        $buffer = '';
        $start  = time();

        while (time() - $start < $timeoutSec) {
            $chunk = @fread($sock, 4096);
            if ($chunk === false || $chunk === '') {
                usleep(200000);
                continue;
            }
            $buffer .= $chunk;
            foreach ($prompts as $prompt) {
                if (str_contains($buffer, $prompt)) {
                    return $buffer;
                }
            }
        }

        return $buffer;
    }

    private function telnetReadMore($sock, int $timeoutSec): string
    {
        $buffer = '';
        $start  = time();

        while (time() - $start < $timeoutSec) {
            $chunk = @fread($sock, 8192);
            if ($chunk === false || $chunk === '') {
                usleep(200000);
                continue;
            }
            $buffer .= $chunk;

            if (str_contains($buffer, '--More--')) {
                fwrite($sock, ' ');
                $buffer = str_replace('--More--', '', $buffer);
            }

            if (preg_match('/[\]>$#]\s*$/', $buffer)) {
                break;
            }
        }

        return $buffer;
    }

    private function cleanTelnet(string $s): string
    {
        $s = preg_replace('/\xff[\xfb-\xfe]./s', '', $s);
        $s = preg_replace('/[\x00-\x08\x0b\x0c\x0e-\x1f]/', '', $s);
        return $s;
    }
}


