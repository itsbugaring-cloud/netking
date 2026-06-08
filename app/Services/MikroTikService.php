<?php

namespace App\Services;

use App\Models\Area;
use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * MikroTik RouterOS API Service — Per-Area, Lazy Connection
 * 
 * Supports multi-area ISP: each area has its own router credentials.
 * Connection is established lazily (on first API call), not in constructor.
 * 
 * Usage:
 *   // Per-area (recommended for multi-area ISP)
 *   $mikrotik = MikroTikService::forArea($customer->area);
 *   $mikrotik->toggleSecret($username, false);
 * 
 *   // Fallback: global config from .env (single-router mode)
 *   $mikrotik = app(MikroTikService::class);
 *   $mikrotik->testConnection();
 */
class MikroTikService
{
    protected ?Client $client = null;
    protected bool $connected = false;
    protected string $host;
    protected string $user;
    protected string $pass;
    protected int $port;

    public function __construct(?string $host = null, ?string $user = null, ?string $pass = null, ?int $port = null)
    {
        // Store credentials but do NOT connect yet (lazy)
        $this->host = $host
            ?? config('services.mikrotik.host', '')
            ?? config('mikrotik.host', '')
            ?? env('MIKROTIK_HOST', '');

        $this->user = $user
            ?? config('services.mikrotik.username', '')
            ?? config('mikrotik.user', '')
            ?? env('MIKROTIK_USERNAME', env('MIKROTIK_USER', ''));

        $this->pass = $pass
            ?? config('services.mikrotik.password', '')
            ?? config('mikrotik.pass', '')
            ?? env('MIKROTIK_PASSWORD', env('MIKROTIK_PASS', ''));

        $this->port = $port
            ?? (int) config('services.mikrotik.port', 0)
            ?: (int) config('mikrotik.port', 0)
            ?: (int) env('MIKROTIK_PORT', 8728);
    }

    /**
     * Create a MikroTikService instance for a specific area's router.
     * This is the preferred way to use MikroTikService in a multi-area ISP.
     */
    public static function forArea(Area $area): self
    {
        return new self(
            $area->router_ip,
            $area->router_user,
            $area->router_pass,
            8728
        );
    }

    /**
     * Lazily establish connection to the router.
     * Called automatically before any API operation.
     * Retries once on failure (handles stale WireGuard connections).
     */
    protected function connect(): bool
    {
        if ($this->connected) {
            return true;
        }

        if (empty($this->host) || empty($this->user)) {
            Log::warning('MikroTik: No host/credentials configured');
            return false;
        }

        $attempts = 2; // try twice: first attempt + 1 retry
        $lastError = '';

        for ($i = 1; $i <= $attempts; $i++) {
            try {
                $config = new Config([
                    'host'           => $this->host,
                    'user'           => $this->user,
                    'pass'           => $this->pass,
                    'port'           => $this->port,
                    'timeout'        => 5,   // connection timeout (TCP handshake)
                    'socket_timeout' => 15,  // read/write timeout (waiting for response)
                ]);

                $this->client = new Client($config);
                $this->connected = true;

                if ($i > 1) {
                    Log::info('MikroTik connection established on retry', ['host' => $this->host, 'attempt' => $i]);
                }
                return true;
            } catch (Exception $e) {
                $lastError = $e->getMessage();
                $this->connected = false;
                $this->client = null;

                if ($i < $attempts) {
                    usleep(500000); // 500ms before retry
                }
            }
        }

        Log::error('MikroTik connection failed after retries', [
            'error' => $lastError,
            'host' => $this->host,
        ]);
        return false;
    }

    /**
     * Check if connected (or can connect) to MikroTik
     */
    public function isConnected(): bool
    {
        return $this->connected || $this->connect();
    }

    /**
     * Test MikroTik connection and get router identity
     */
    public function testConnection(): array
    {
        if (!$this->connect()) {
            return ['success' => false, 'error' => 'Not connected to MikroTik'];
        }

        try {
            $query = new Query('/system/identity/print');
            $response = $this->client->query($query)->read();

            return [
                'success' => true,
                'identity' => $response[0]['name'] ?? 'Unknown',
                'host' => $this->host,
            ];
        } catch (Exception $e) {
            Log::error('MikroTik test connection failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Create PPPoE Secret
     */
    public function createSecret(
        string $username,
        string $password,
        string $service = 'pppoe',
        string $profile = 'default',
        ?string $remoteAddress = null,
        ?string $localAddress = null,
        ?string $comment = null
    ): array {
        if (!$this->connect()) {
            return ['success' => false, 'error' => 'Not connected to MikroTik'];
        }

        try {
            $query = new Query('/ppp/secret/add');
            $query->equal('name', $username);
            $query->equal('password', $password);
            $query->equal('service', $service);
            $query->equal('profile', $profile);

            if ($remoteAddress) {
                $query->equal('remote-address', $remoteAddress);
            }

            if ($localAddress) {
                $query->equal('local-address', $localAddress);
            }

            if ($comment) {
                $query->equal('comment', $comment);
            }

            $response = $this->client->query($query)->read();

            Log::info('MikroTik PPPoE Secret Created', [
                'username' => $username,
                'service' => $service,
                'profile' => $profile,
                'remote_address' => $remoteAddress,
                'host' => $this->host,
            ]);

            return ['success' => true, 'data' => $response];
        } catch (Exception $e) {
            Log::error('MikroTik Create Secret Failed', [
                'username' => $username,
                'error' => $e->getMessage(),
                'host' => $this->host,
            ]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Delete PPPoE Secret
     */
    public function deleteSecret(string $username): array
    {
        if (!$this->connect()) {
            return ['success' => false, 'error' => 'Not connected to MikroTik'];
        }

        try {
            $query = new Query('/ppp/secret/print');
            $query->where('name', $username);
            $secrets = $this->client->query($query)->read();

            if (empty($secrets)) {
                return ['success' => false, 'error' => 'Secret not found'];
            }

            $secretId = $secrets[0]['.id'];

            $query = new Query('/ppp/secret/remove');
            $query->equal('.id', $secretId);
            $this->client->query($query)->read();

            Log::info('MikroTik PPPoE Secret Deleted', [
                'username' => $username,
                'host' => $this->host,
            ]);

            return ['success' => true, 'message' => 'Secret deleted successfully'];
        } catch (Exception $e) {
            Log::error('MikroTik Delete Secret Failed', [
                'username' => $username,
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Enable or Disable PPPoE Secret
     */
    public function toggleSecret(string $username, bool $enable = true): array
    {
        if (!$this->connect()) {
            return ['success' => false, 'error' => 'Not connected to MikroTik'];
        }

        try {
            $query = new Query('/ppp/secret/print');
            $query->where('name', $username);
            $secrets = $this->client->query($query)->read();

            if (empty($secrets)) {
                return ['success' => false, 'error' => 'Secret not found'];
            }

            $secretId = $secrets[0]['.id'];

            $action = $enable ? '/ppp/secret/enable' : '/ppp/secret/disable';
            $query = new Query($action);
            $query->equal('.id', $secretId);
            $this->client->query($query)->read();

            Log::info('MikroTik PPPoE Secret Toggled', [
                'username' => $username,
                'enabled' => $enable,
                'host' => $this->host,
            ]);

            return [
                'success' => true,
                'message' => $enable ? 'Secret enabled successfully' : 'Secret disabled successfully',
            ];
        } catch (Exception $e) {
            Log::error('MikroTik Toggle Secret Failed', [
                'username' => $username,
                'enable' => $enable,
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get Active PPPoE Sessions with full traffic stats
     */
    public function getActiveSessions(?string $username = null): array
    {
        if (!$this->connect()) {
            return ['success' => false, 'error' => 'Not connected to MikroTik'];
        }

        try {
            // Explicitly request byte/rate fields — /ppp/active/print does NOT
            // include byte counters by default through the API without .proplist
            $query = new Query('/ppp/active/print');
            $query->equal('.proplist', implode(',', [
                '.id', 'name', 'service', 'caller-id', 'address', 'uptime',
                'encoding', 'session-id',
                'bytes-in', 'bytes-out',   // RouterOS 6
                'rx-byte',  'tx-byte',      // RouterOS 7 alias
                'rate-in',  'rate-out',     // current rate (bps), if available
            ]));

            if ($username) {
                $query->where('name', $username);
            }

            $sessions = $this->client->query($query)->read();

            // Normalize bytes & rates — RouterOS returns different field names depending on version
            foreach ($sessions as &$s) {
                // Total bytes (cumulative for session)
                $s['bytes-in']  = (int) ($s['bytes-in']  ?? $s['bytes_in']  ?? $s['rx-byte'] ?? $s['rx_byte'] ?? 0);
                $s['bytes-out'] = (int) ($s['bytes-out'] ?? $s['bytes_out'] ?? $s['tx-byte'] ?? $s['tx_byte'] ?? 0);
                // Current rate in bps (available when PPPoE accounting/simple-queues active)
                $s['rate-in']   = (int) ($s['rate-in']  ?? $s['rate_in']  ?? 0);
                $s['rate-out']  = (int) ($s['rate-out'] ?? $s['rate_out'] ?? 0);
            }
            unset($s);

            return ['success' => true, 'data' => $sessions];
        } catch (Exception $e) {
            Log::error('MikroTik Get Active Sessions Failed', [
                'username' => $username,
                'error'    => $e->getMessage(),
            ]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get all PPPoE Secrets
     * Uses .proplist to limit fields — dramatically faster on slow routers.
     */
    public function getAllSecrets(): array
    {
        if (!$this->connect()) {
            return ['success' => false, 'error' => 'Not connected to MikroTik'];
        }

        try {
            $query = new Query('/ppp/secret/print');
            $query->equal('.proplist', '.id,name,password,service,profile,remote-address,local-address,disabled,comment');
            $secrets = $this->client->query($query)->read();

            return ['success' => true, 'data' => $secrets];
        } catch (Exception $e) {
            Log::error('MikroTik Get All Secrets Failed', ['error' => $e->getMessage(), 'host' => $this->host]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Push hotspot voucher user.
     */
    public function pushVoucher(string $code, string $profile): array
    {
        if (!$this->connect()) {
            return ['success' => false, 'error' => 'Not connected to MikroTik'];
        }

        try {
            $query = new Query('/ip/hotspot/user/add');
            $query->equal('name', $code);
            $query->equal('password', $code);
            $query->equal('profile', $profile);
            $query->equal('comment', 'netking-' . now()->format('Ymd'));
            $response = $this->client->query($query)->read();

            return ['success' => true, 'data' => $response];
        } catch (Exception $e) {
            Log::error('MikroTik Push Voucher Failed', [
                'code' => $code,
                'profile' => $profile,
                'error' => $e->getMessage(),
                'host' => $this->host,
            ]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Fast check whether PPPoE secret exists (without loading full secret list)
     */
    public function secretExists(string $username): array
    {
        if (!$this->connect()) {
            return ['success' => false, 'exists' => false, 'error' => 'Not connected to MikroTik'];
        }

        try {
            $query = new Query('/ppp/secret/print');
            $query->equal('.proplist', '.id,name');
            $query->where('name', $username);
            $rows = $this->client->query($query)->read();

            return [
                'success' => true,
                'exists' => !empty($rows),
            ];
        } catch (Exception $e) {
            Log::error('MikroTik secretExists failed', [
                'username' => $username,
                'error' => $e->getMessage(),
                'host' => $this->host,
            ]);

            return ['success' => false, 'exists' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get all PPPoE Profiles (for package sync)
     */
    public function getPppoeProfiles(): array
    {
        if (!$this->connect()) {
            return ['success' => false, 'error' => 'Not connected to MikroTik'];
        }

        try {
            $query = new Query('/ppp/profile/print');
            $profiles = $this->client->query($query)->read();

            return ['success' => true, 'data' => $profiles];
        } catch (Exception $e) {
            Log::error('MikroTik Get PPPoE Profiles Failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Disconnect active PPPoE session
     */
    public function disconnectSession(string $username): array
    {
        if (!$this->connect()) {
            return ['success' => false, 'error' => 'Not connected to MikroTik'];
        }

        try {
            $query = new Query('/ppp/active/print');
            $query->where('name', $username);
            $sessions = $this->client->query($query)->read();

            if (empty($sessions)) {
                return ['success' => false, 'error' => 'No active session found'];
            }

            $sessionId = $sessions[0]['.id'];

            $query = new Query('/ppp/active/remove');
            $query->equal('.id', $sessionId);
            $this->client->query($query)->read();

            Log::info('MikroTik PPPoE Session Disconnected', [
                'username' => $username,
                'host' => $this->host,
            ]);

            return ['success' => true, 'message' => 'Session disconnected successfully'];
        } catch (Exception $e) {
            Log::error('MikroTik Disconnect Session Failed', [
                'username' => $username,
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get all PPPoE profiles from MikroTik (READ-ONLY)
     * @return array List of profiles with name, rate-limit, etc.
     */
    public function getProfiles(): array
    {
        if (!$this->connect()) {
            throw new Exception('Not connected to MikroTik at ' . $this->host);
        }

        $query = new Query('/ppp/profile/print');
        $response = $this->client->query($query)->read();

        return $response;
    }

    /**
     * Parse RouterOS time format to milliseconds (integer).
     * RouterOS returns times like "20ms727us", "1s200ms", "500us", "0s" etc.
     * Returns total milliseconds rounded to nearest int.
     */
    private function parseRouterOsTime(?string $timeStr): int
    {
        if (empty($timeStr) || $timeStr === '' || $timeStr === '0s') return 0;
        $total = 0;
        if (preg_match('/(\d+)s/', $timeStr, $m))   $total += (int)$m[1] * 1000;
        if (preg_match('/(\d+)ms/', $timeStr, $m))  $total += (int)$m[1];
        if (preg_match('/(\d+)us/', $timeStr, $m))  $total += (int) round((int)$m[1] / 1000);
        return $total;
    }

    /**
     * Ping a host from MikroTik router
     * @param string $target IP or hostname to ping
     * @param int $count Number of ping packets
     * @return array Ping results
     */
    public function ping(string $target, int $count = 5): array
    {
        if (!$this->connect()) {
            throw new Exception('Not connected to MikroTik at ' . $this->host);
        }

        try {
            $query = (new Query('/ping'))
                ->add('=address=' . $target)
                ->add('=count=' . $count);

            $response = $this->client->query($query)->read();

            $results = [];
            $sent = 0; $received = 0; $totalTime = 0; $minTime = PHP_INT_MAX; $maxTime = 0;

            foreach ($response as $row) {
                // RouterOS returns status='' (empty string) for SUCCESS, 'timeout' for timeout.
                // Using ?? 'timeout' is wrong because the key IS present (just empty).
                $rawStatus = $row['status'] ?? '';
                $status = ($rawStatus === '' || $rawStatus === 'success') ? 'ok' : $rawStatus;

                // RouterOS time format: "20ms727us" — use shared parser
                $time = ($status === 'ok' && isset($row['time']))
                    ? $this->parseRouterOsTime($row['time'])
                    : 0;

                $results[] = [
                    'seq'    => $row['seq'] ?? $sent,
                    'status' => $status,
                    'time'   => $time,
                    'ttl'    => $row['ttl'] ?? null,
                    'size'   => $row['size'] ?? null,
                ];
                $sent++;
                if ($status === 'ok') {
                    $received++;
                    $totalTime += $time;
                    if ($time < $minTime) $minTime = $time;
                    if ($time > $maxTime) $maxTime = $time;
                }
            }

            $loss = $sent > 0 ? round((($sent - $received) / $sent) * 100) : 100;
            $avg  = $received > 0 ? round($totalTime / $received) : 0;

            return [
                'success' => true,
                'target'  => $target,
                'router'  => $this->host,
                'sent'    => $sent,
                'received'=> $received,
                'loss'    => $loss,
                'min'     => $received > 0 ? $minTime : 0,
                'avg'     => $avg,
                'max'     => $maxTime,
                'results' => $results,
            ];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage(), 'target' => $target];
        }
    }

    /**
     * Traceroute a host from MikroTik router
     * @param string $target IP or hostname
     * @param int $maxHops Maximum hops
     * @return array Traceroute results
     */
    public function traceroute(string $target, int $maxHops = 15): array
    {
        if (!$this->connect()) {
            throw new Exception('Not connected to MikroTik at ' . $this->host);
        }

        try {
            $query = (new Query('/tool/traceroute'))
                ->add('=address=' . $target)
                ->add('=count=1')
                ->add('=max-hops=' . $maxHops);

            $response = $this->client->query($query)->read();

            // RouterOS traceroute streams real-time updates per hop.
            // Each hop can appear multiple times as probes complete.
            // Fix: use hop number as key → last update per hop wins (most complete).
            $hopsMap = [];
            foreach ($response as $row) {
                $hopNum = (int)($row['n'] ?? 0);
                if ($hopNum < 1) continue;

                // Parse time fields — RouterOS format "10ms500us" or empty string
                $t1 = (isset($row['time1']) && $row['time1'] !== '') ? $this->parseRouterOsTime($row['time1']) : null;
                $t2 = (isset($row['time2']) && $row['time2'] !== '') ? $this->parseRouterOsTime($row['time2']) : null;
                $t3 = (isset($row['time3']) && $row['time3'] !== '') ? $this->parseRouterOsTime($row['time3']) : null;

                // Prefer previous entry's times if new row has no times yet (streaming partial update)
                $prev = $hopsMap[$hopNum] ?? null;
                $hopsMap[$hopNum] = [
                    'hop'     => $hopNum,
                    'address' => ($row['address'] ?? '') !== '' ? $row['address'] : ($prev['address'] ?? '*'),
                    'loss'    => $row['loss'] ?? ($prev['loss'] ?? '100%'),
                    'time1'   => $t1 ?? ($prev['time1'] ?? null),
                    'time2'   => $t2 ?? ($prev['time2'] ?? null),
                    'time3'   => $t3 ?? ($prev['time3'] ?? null),
                    'status'  => ($row['status'] ?? '') !== '' ? $row['status'] : ($prev['status'] ?? ''),
                ];
            }

            ksort($hopsMap); // ensure hop order
            $hops = array_values($hopsMap);

            return [
                'success' => true,
                'target'  => $target,
                'router'  => $this->host,
                'hops'    => $hops,
            ];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage(), 'target' => $target];
        }
    }

    /**
     * Get all interfaces with traffic stats from MikroTik
     * @return array Interface list with rx/tx bytes
     */
    public function getInterfaceStats(): array
    {
        if (!$this->connect()) {
            throw new Exception('Not connected to MikroTik at ' . $this->host);
        }

        $query = new Query('/interface/print');
        $query->add('=stats=');
        $response = $this->client->query($query)->read();

        return array_map(function ($iface) {
            return [
                'name'      => $iface['name'] ?? '',
                'type'      => $iface['type'] ?? '',
                'running'   => ($iface['running'] ?? 'false') === 'true',
                'disabled'  => ($iface['disabled'] ?? 'false') === 'true',
                'rx_bytes'  => (int)($iface['rx-byte'] ?? 0),
                'tx_bytes'  => (int)($iface['tx-byte'] ?? 0),
                'rx_packets'=> (int)($iface['rx-packet'] ?? 0),
                'tx_packets'=> (int)($iface['tx-packet'] ?? 0),
                'comment'   => $iface['comment'] ?? '',
            ];
        }, $response);
    }

    /**
     * Get LLDP/IP neighbors from MikroTik (for topology)
     * @return array Neighbor list
     */
    public function getNeighbors(): array
    {
        if (!$this->connect()) {
            throw new Exception('Not connected to MikroTik at ' . $this->host);
        }

        try {
            $query = new Query('/ip/neighbor/print');
            $response = $this->client->query($query)->read();

            return array_map(function ($n) {
                return [
                    'identity'   => $n['identity'] ?? 'Unknown',
                    'address'    => $n['address'] ?? '',
                    'interface'  => $n['interface'] ?? '',
                    'mac'        => $n['mac-address'] ?? '',
                    'platform'   => $n['platform'] ?? '',
                    'board'      => $n['board'] ?? '',
                    'version'    => $n['version'] ?? '',
                ];
            }, $response);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get real-time interface traffic speed (no delta needed).
     * Uses RouterOS /interface/monitor-traffic which returns instantaneous bps,
     * exactly like Winbox Traffic Monitor / The Dude.
     *
     * @param string $iface  Interface name (e.g. "ether1", "bridge-local")
     * @return array ['rx_mbps' => float, 'tx_mbps' => float, 'rx_bps' => int, 'tx_bps' => int]
     */
    public function monitorTraffic(string $iface): array
    {
        if (!$this->connect()) {
            throw new Exception('Not connected to MikroTik at ' . $this->host);
        }

        $query = (new Query('/interface/monitor-traffic'))
            ->add('=interface=' . $iface)
            ->add('=once=');

        $resp  = $this->client->query($query)->read();
        $rxBps = (int)($resp[0]['rx-bits-per-second'] ?? 0);
        $txBps = (int)($resp[0]['tx-bits-per-second'] ?? 0);

        return [
            'rx_bps'  => $rxBps,
            'tx_bps'  => $txBps,
            'rx_mbps' => round($rxBps / 1_000_000, 2),
            'tx_mbps' => round($txBps / 1_000_000, 2),
        ];
    }

    /**
     * Monitor ALL meaningful interfaces at once (single API call).
     * Filters to physical/logical interfaces only (no PPPoE virtuals).
     * Returns only interfaces that have traffic > 0.
     *
     * @return array [['name'=>'ether1','type'=>'ether','rx_mbps'=>38.0,'tx_mbps'=>1.5], ...]
     */
    public function monitorAllInterfaces(): array
    {
        if (!$this->connect()) {
            throw new Exception('Not connected to MikroTik at ' . $this->host);
        }

        // Get interface list — filter to meaningful types only (exclude pppoe virtuals)
        $meaningful = ['ether', 'bridge', 'bonding', 'vlan', 'sfp-sfpplus', 'sfp28', 'wlan', 'cap'];
        $ifaces     = $this->getInterfaceStats();
        $targets    = collect($ifaces)
            ->filter(fn($i) => !$i['disabled'] && $i['running'] && in_array($i['type'], $meaningful))
            ->values();

        if ($targets->isEmpty()) return [];

        $ifaceList = $targets->pluck('name')->implode(',');

        $query = (new Query('/interface/monitor-traffic'))
            ->add('=interface=' . $ifaceList)
            ->add('=once=');

        $resp   = $this->client->query($query)->read();
        $result = [];

        foreach ($resp as $row) {
            $rxBps = (int)($row['rx-bits-per-second'] ?? 0);
            $txBps = (int)($row['tx-bits-per-second'] ?? 0);
            $iface = $targets->firstWhere('name', $row['name'] ?? '');
            $result[] = [
                'name'    => $row['name'] ?? '',
                'type'    => $iface['type'] ?? '',
                'rx_mbps' => round($rxBps / 1_000_000, 2),
                'tx_mbps' => round($txBps / 1_000_000, 2),
            ];
        }

        // Sort by total traffic desc, exclude idle interfaces
        return collect($result)
            ->filter(fn($i) => ($i['rx_mbps'] + $i['tx_mbps']) > 0)
            ->sortByDesc(fn($i) => $i['rx_mbps'] + $i['tx_mbps'])
            ->values()
            ->toArray();
    }

    /**
     * Auto-detect the main WAN/uplink interface.
     * Priority: physical ether interfaces (not bridge/pppoe/vlan) that are running,
     * sorted by highest cumulative rx_bytes (WAN receives the most download traffic).
     *
     * @return string|null Interface name, or null if none found
     */
    public function detectWanInterface(): ?string
    {
        try {
            $ifaces = $this->getInterfaceStats();
            $wan = collect($ifaces)
                ->filter(fn($i) =>
                    !$i['disabled'] &&
                    $i['running'] &&
                    in_array($i['type'], ['ether', 'sfp-sfpplus', 'sfp28', '100base-tx'])
                )
                ->sortByDesc(fn($i) => $i['rx_bytes'] + $i['tx_bytes'])
                ->first();

            return $wan ? $wan['name'] : null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Get IP pools from MikroTik.
     * Returns pool name, ranges, and usage count (if available).
     *
     * @return array [['name'=>'pool-cicaheum','ranges'=>'192.168.1.2-192.168.1.254','total'=>253,'used'=>120], ...]
     */
    public function getIpPools(): array
    {
        if (!$this->connect()) {
            throw new Exception('Not connected to MikroTik at ' . $this->host);
        }

        try {
            // Fetch all pools
            $query  = new Query('/ip/pool/print');
            $pools  = $this->client->query($query)->read();

            // Fetch used addresses count per pool
            $usedQuery = new Query('/ip/pool/used/print');
            $usedRows  = $this->client->query($usedQuery)->read();

            // Count used per pool name
            $usedCounts = [];
            foreach ($usedRows as $row) {
                $pool = $row['pool'] ?? '';
                if ($pool) {
                    $usedCounts[$pool] = ($usedCounts[$pool] ?? 0) + 1;
                }
            }

            return array_map(function ($pool) use ($usedCounts) {
                $name   = $pool['name'] ?? '';
                $ranges = $pool['ranges'] ?? '';

                // Calculate total IPs from range string "x.x.x.x-x.x.x.y"
                $total = 0;
                if ($ranges && str_contains($ranges, '-')) {
                    [$start, $end] = explode('-', $ranges, 2);
                    $startLong = ip2long(trim($start));
                    $endLong   = ip2long(trim($end));
                    if ($startLong !== false && $endLong !== false && $endLong >= $startLong) {
                        $total = $endLong - $startLong + 1;
                    }
                }

                $used = $usedCounts[$name] ?? 0;

                return [
                    'name'    => $name,
                    'ranges'  => $ranges,
                    'total'   => $total,
                    'used'    => $used,
                    'free'    => max(0, $total - $used),
                    'comment' => $pool['comment'] ?? '',
                ];
            }, $pools);

        } catch (Exception $e) {
            Log::error('MikroTik getIpPools failed', ['error' => $e->getMessage(), 'host' => $this->host]);
            return [];
        }
    }

    /**
     * Get system resource data (CPU, RAM, disk, uptime, version, board)
     */
    public function getSystemResource(): array
    {
        if (!$this->connect()) {
            return ['success' => false, 'error' => 'Not connected'];
        }
        try {
            $query = new Query('/system/resource/print');
            $resp = $this->client->query($query)->read();
            return ['success' => true, 'data' => $resp[0] ?? []];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get system health (temperature, voltage) — not all routers support this
     */
    public function getSystemHealth(): array
    {
        if (!$this->connect()) {
            return ['success' => false, 'error' => 'Not connected'];
        }
        try {
            $query = new Query('/system/health/print');
            $resp = $this->client->query($query)->read();
            return ['success' => true, 'data' => $resp];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get system license info
     */
    public function getSystemLicense(): array
    {
        if (!$this->connect()) {
            return ['success' => false, 'error' => 'Not connected'];
        }
        try {
            $query = new Query('/system/license/print');
            $resp = $this->client->query($query)->read();
            return ['success' => true, 'data' => $resp[0] ?? []];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get all simple queues
     */
    public function getSimpleQueues(): array
    {
        if (!$this->connect()) return ['success' => false, 'error' => 'Not connected'];
        try {
            $query = new Query('/queue/simple/print');
            $resp = $this->client->query($query)->read();
            return ['success' => true, 'data' => $resp];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Create a simple queue
     */
    public function createSimpleQueue(string $name, string $target, string $maxLimit, ?string $burstLimit = null, ?string $burstThreshold = null, ?string $burstTime = null, ?string $comment = null): array
    {
        if (!$this->connect()) return ['success' => false, 'error' => 'Not connected'];
        try {
            $query = new Query('/queue/simple/add');
            $query->equal('name', $name);
            $query->equal('target', $target);
            $query->equal('max-limit', $maxLimit);
            if ($burstLimit) $query->equal('burst-limit', $burstLimit);
            if ($burstThreshold) $query->equal('burst-threshold', $burstThreshold);
            if ($burstTime) $query->equal('burst-time', $burstTime);
            if ($comment) $query->equal('comment', $comment);
            $resp = $this->client->query($query)->read();
            return ['success' => true, 'data' => $resp];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Update a simple queue by .id
     */
    public function updateSimpleQueue(string $id, array $params): array
    {
        if (!$this->connect()) return ['success' => false, 'error' => 'Not connected'];
        try {
            $query = new Query('/queue/simple/set');
            $query->equal('.id', $id);
            foreach ($params as $key => $value) {
                $query->equal($key, $value);
            }
            $resp = $this->client->query($query)->read();
            return ['success' => true, 'data' => $resp];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Delete a simple queue by .id
     */
    public function deleteSimpleQueue(string $id): array
    {
        if (!$this->connect()) return ['success' => false, 'error' => 'Not connected'];
        try {
            $query = new Query('/queue/simple/remove');
            $query->equal('.id', $id);
            $this->client->query($query)->read();
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ─── Address List (Isolir) Methods ──────────────────────────────────

    /**
     * Get all entries from a specific address list
     */
    public function getAddressList(string $listName = 'isolir'): array
    {
        if (!$this->connect()) return ['success' => false, 'error' => 'Not connected'];
        try {
            $query = new Query('/ip/firewall/address-list/print');
            $query->where('list', $listName);
            $resp = $this->client->query($query)->read();
            return ['success' => true, 'data' => $resp];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Add IP to address list
     */
    public function addToAddressList(string $address, string $listName = 'isolir', ?string $timeout = null, ?string $comment = null): array
    {
        if (!$this->connect()) return ['success' => false, 'error' => 'Not connected'];
        try {
            $query = new Query('/ip/firewall/address-list/add');
            $query->equal('address', $address);
            $query->equal('list', $listName);
            if ($timeout) $query->equal('timeout', $timeout);
            if ($comment) $query->equal('comment', $comment);
            $resp = $this->client->query($query)->read();
            return ['success' => true, 'data' => $resp];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Remove entry from address list by .id
     */
    public function removeFromAddressList(string $id): array
    {
        if (!$this->connect()) return ['success' => false, 'error' => 'Not connected'];
        try {
            $query = new Query('/ip/firewall/address-list/remove');
            $query->equal('.id', $id);
            $this->client->query($query)->read();
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Find address in a specific list
     */
    public function findInAddressList(string $address, string $listName = 'isolir'): array
    {
        if (!$this->connect()) return ['success' => false, 'error' => 'Not connected'];
        try {
            $query = new Query('/ip/firewall/address-list/print');
            $query->where('list', $listName);
            $query->where('address', $address);
            $resp = $this->client->query($query)->read();
            return ['success' => true, 'data' => $resp, 'found' => !empty($resp)];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get BGP peer status from MikroTik.
     * Tries RouterOS 7 path (/routing/bgp/peer) first, falls back to RouterOS 6 (/routing/bgp/peer).
     *
     * @return array [['name'=>'peer1','remote-address'=>'...','state'=>'established','uptime'=>'...'], ...]
     */
    public function getBgpPeers(): array
    {
        if (!$this->connect()) {
            throw new Exception('Not connected to MikroTik at ' . $this->host);
        }

        try {
            // RouterOS 7: /routing/bgp/connection print
            // RouterOS 6: /routing/bgp/peer print
            // Try ROS7 first
            try {
                $query = new Query('/routing/bgp/connection/print');
                $peers = $this->client->query($query)->read();
            } catch (Exception $e) {
                // Fallback to ROS6
                $query = new Query('/routing/bgp/peer/print');
                $peers = $this->client->query($query)->read();
            }

            return array_map(function ($peer) {
                return [
                    'name'           => $peer['name'] ?? $peer['peer-name'] ?? '—',
                    'remote_address' => $peer['remote.address'] ?? $peer['remote-address'] ?? '—',
                    'remote_as'      => $peer['remote.as'] ?? $peer['remote-as'] ?? '—',
                    'local_address'  => $peer['local.address'] ?? $peer['local-address'] ?? '—',
                    'state'          => $peer['established'] ?? $peer['state'] ?? 'unknown',
                    'uptime'         => $peer['uptime'] ?? '—',
                    'prefix_count'   => (int)($peer['prefix-count'] ?? $peer['used-update-in'] ?? 0),
                    'tx_bytes'       => (int)($peer['bytes-out'] ?? 0),
                    'rx_bytes'       => (int)($peer['bytes-in'] ?? 0),
                    'disabled'       => ($peer['disabled'] ?? 'false') === 'true',
                ];
            }, $peers);

        } catch (Exception $e) {
            Log::warning('MikroTik getBgpPeers failed (no BGP?)', ['error' => $e->getMessage(), 'host' => $this->host]);
            return [];
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    // PPPoE Profile CRUD
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * Create a new PPP profile on the router
     */
    public function createPppProfile(string $name, string $rateLimit, array $options = []): array
    {
        if (!$this->connect()) return ['success' => false, 'error' => 'Not connected'];
        try {
            $query = new Query('/ppp/profile/add');
            $query->equal('name', $name);
            $query->equal('rate-limit', $rateLimit);
            foreach ($options as $k => $v) {
                if ($v !== null && $v !== '') $query->equal($k, $v);
            }
            $resp = $this->client->query($query)->read();
            return ['success' => true, 'data' => $resp];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Update an existing PPP profile on the router
     */
    public function updatePppProfile(string $id, array $params): array
    {
        if (!$this->connect()) return ['success' => false, 'error' => 'Not connected'];
        try {
            $query = new Query('/ppp/profile/set');
            $query->equal('.id', $id);
            foreach ($params as $k => $v) {
                $query->equal($k, $v ?? '');
            }
            $resp = $this->client->query($query)->read();
            return ['success' => true, 'data' => $resp];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Delete a PPP profile from the router
     */
    public function deletePppProfile(string $id): array
    {
        if (!$this->connect()) return ['success' => false, 'error' => 'Not connected'];
        try {
            $query = new Query('/ppp/profile/remove');
            $query->equal('.id', $id);
            $this->client->query($query)->read();
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Count PPPoE secrets referencing a given profile name
     */
    public function countSecretsForProfile(string $profileName): int
    {
        if (!$this->connect()) return 0;
        try {
            $query = new Query('/ppp/secret/print');
            $query->where('profile', $profileName);
            $query->equal('.proplist', '.id');
            return count($this->client->query($query)->read());
        } catch (Exception $e) {
            return 0;
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    // Router Backup
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * Create a binary backup on the router
     */
    public function createBackup(string $name = ''): array
    {
        if (!$this->connect()) return ['success' => false, 'error' => 'Not connected'];
        try {
            $query = new Query('/system/backup/save');
            if ($name) $query->equal('name', $name);
            $this->client->query($query)->read();
            return ['success' => true, 'filename' => $name ? $name . '.backup' : 'backup.backup'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Create a text export of the router configuration
     */
    public function createExport(): array
    {
        if (!$this->connect()) return ['success' => false, 'error' => 'Not connected'];
        try {
            $query = new Query('/export');
            $resp = $this->client->query($query)->read();
            return ['success' => true, 'data' => $resp];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get file info from the router filesystem
     */
    public function getFileContents(string $filename): array
    {
        if (!$this->connect()) return ['success' => false, 'error' => 'Not connected'];
        try {
            $query = new Query('/file/print');
            $query->where('name', $filename);
            $files = $this->client->query($query)->read();
            if (empty($files)) return ['success' => false, 'error' => 'File not found'];
            return ['success' => true, 'data' => $files[0]];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Delete a file from the router filesystem
     */
    public function deleteFile(string $filename): array
    {
        if (!$this->connect()) return ['success' => false, 'error' => 'Not connected'];
        try {
            $query = new Query('/file/remove');
            $query->equal('.id', $filename);
            $this->client->query($query)->read();
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
