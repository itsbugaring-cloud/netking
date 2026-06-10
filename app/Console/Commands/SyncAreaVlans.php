<?php

namespace App\Console\Commands;

use App\Models\Area;
use App\Services\MikroTikService;
use Illuminate\Console\Command;
use RouterOS\Query;

class SyncAreaVlans extends Command
{
    protected $signature = 'areas:sync-vlans {--apply : Apply changes to DB}';
    protected $description = 'Auto-detect VLAN PPPoE + MGMT from MikroTik routers per area';

    public function handle(): int
    {
        $apply = $this->option('apply');
        $this->info($apply ? '⚠️  MODE: APPLY' : '👁️  MODE: DRY-RUN');
        $this->newLine();

        $areas = Area::orderBy('name')->get();
        $updated = 0;
        $failed = 0;

        foreach ($areas as $area) {
            $this->line("━━━ {$area->name} ({$area->router_ip}) ━━━");

            try {
                $mikrotik = MikroTikService::forArea($area);
                if (!$mikrotik->isConnected()) {
                    $this->error("  ✗ Cannot connect");
                    $failed++;
                    continue;
                }

                // Get PPPoE servers → find interface
                $pppoeServers = $this->queryRouter($mikrotik, '/interface/pppoe-server/server/print', 'interface');
                if (empty($pppoeServers)) {
                    $this->warn("  ⚠ No PPPoE server found");
                    $failed++;
                    continue;
                }

                $pppoeInterface = $pppoeServers[0]['interface'] ?? null;
                $this->line("  PPPoE Server interface: {$pppoeInterface}");

                // Check if that interface is a VLAN
                $vlanPppoe = $this->getVlanId($mikrotik, $pppoeInterface);

                // Try to find MGMT VLAN (look for interface with "mgmt" or "management" in name/comment)
                $vlanMgmt = $this->findMgmtVlan($mikrotik);

                $this->info("  VLAN PPPoE: " . ($vlanPppoe ?: 'not detected'));
                $this->info("  VLAN MGMT:  " . ($vlanMgmt ?: 'not detected'));

                if ($apply && ($vlanPppoe || $vlanMgmt)) {
                    $updateData = [];
                    if ($vlanPppoe) $updateData['vlan_pppoe'] = $vlanPppoe;
                    if ($vlanMgmt) $updateData['vlan_mgmt'] = $vlanMgmt;
                    $area->update($updateData);
                    $this->info("  ✓ Updated in DB");
                    $updated++;
                } elseif (!$vlanPppoe && !$vlanMgmt) {
                    $this->warn("  ⚠ No VLAN detected (PPPoE might be on physical interface, not VLAN)");
                }

            } catch (\Exception $e) {
                $this->error("  ✗ Error: " . $e->getMessage());
                $failed++;
            }

            $this->newLine();
        }

        $this->newLine();
        $this->info("═══ Summary ═══");
        $this->line("Total areas: {$areas->count()}");
        $this->line("Updated: {$updated}");
        $this->line("Failed/No VLAN: {$failed}");

        if (!$apply) {
            $this->newLine();
            $this->warn("DRY-RUN. Jalankan dengan --apply untuk write ke DB.");
        }

        return self::SUCCESS;
    }

    private function queryRouter(MikroTikService $mikrotik, string $path, string $proplist): array
    {
        try {
            $client = $this->getClient($mikrotik);
            $query = new Query($path);
            $query->equal('.proplist', $proplist);
            return $client->query($query)->read();
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getVlanId(MikroTikService $mikrotik, ?string $interfaceName): ?string
    {
        if (!$interfaceName) return null;

        try {
            $client = $this->getClient($mikrotik);

            // Check /interface/vlan for this interface name
            $query = new Query('/interface/vlan/print');
            $query->where('name', $interfaceName);
            $vlans = $client->query($query)->read();

            if (!empty($vlans) && isset($vlans[0]['vlan-id'])) {
                return (string) $vlans[0]['vlan-id'];
            }

            // Maybe PPPoE is on a bridge that has a VLAN — check bridge ports
            $query = new Query('/interface/bridge/port/print');
            $query->where('bridge', $interfaceName);
            $ports = $client->query($query)->read();

            foreach ($ports as $port) {
                $portInterface = $port['interface'] ?? '';
                $query2 = new Query('/interface/vlan/print');
                $query2->where('name', $portInterface);
                $vlanCheck = $client->query($query2)->read();
                if (!empty($vlanCheck) && isset($vlanCheck[0]['vlan-id'])) {
                    return (string) $vlanCheck[0]['vlan-id'];
                }
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function findMgmtVlan(MikroTikService $mikrotik): ?string
    {
        try {
            $client = $this->getClient($mikrotik);

            // Get all VLANs and look for one with "mgmt" or "management" in name/comment
            $query = new Query('/interface/vlan/print');
            $query->equal('.proplist', 'name,vlan-id,comment');
            $vlans = $client->query($query)->read();

            foreach ($vlans as $vlan) {
                $name = strtolower($vlan['name'] ?? '');
                $comment = strtolower($vlan['comment'] ?? '');
                if (str_contains($name, 'mgmt') || str_contains($name, 'management') ||
                    str_contains($comment, 'mgmt') || str_contains($comment, 'management')) {
                    return (string) ($vlan['vlan-id'] ?? '');
                }
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function getClient(MikroTikService $mikrotik)
    {
        // Access the protected client via reflection
        $ref = new \ReflectionClass($mikrotik);
        $prop = $ref->getProperty('client');
        $prop->setAccessible(true);
        return $prop->getValue($mikrotik);
    }
}
