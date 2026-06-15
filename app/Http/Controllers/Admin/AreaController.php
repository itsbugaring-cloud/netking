<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\AreaIpPool;
use App\Models\Customer;
use App\Models\Package;
use App\Models\Ipam\IpamRouter;
use App\Services\Ipam\IpamAuditService;
use App\Services\MikroTikService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RouterOS\Query;

class AreaController extends Controller
{
    public function index()
    {
        $areas = Area::withCount('customers')->get();
        return view('admin.areas.index', compact('areas'));
    }

    public function create()
    {
        return view('admin.areas.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'                   => 'required|string|max:255',
            'router_ip'              => 'required|string|max:255',
            'router_user'            => 'required|string|max:255',
            'router_pass'            => 'required|string|max:255',
            'pools'                  => 'nullable|array',
            'pools.*.ip_pool_start'  => 'nullable|ip',
            'pools.*.ip_pool_end'    => 'nullable|ip',
            'pools.*.pool_name'      => 'nullable|string|max:100',
        ]);

        // Parse host:port for router connection
        $routerHost = $request->router_ip;
        $routerPort = 8728;
        if (str_contains($routerHost, ':') && !filter_var($routerHost, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $parts = explode(':', $routerHost, 2);
            $routerHost = $parts[0];
            $routerPort = (int) $parts[1];
        }

        // If no pools provided, try to fetch from router
        $pools = $request->pools;
        if (empty($pools) || !isset($pools[0]['ip_pool_start']) || !$pools[0]['ip_pool_start']) {
            $mikrotik = new MikroTikService($routerHost, $request->router_user, $request->router_pass, $routerPort);
            $test = $mikrotik->testConnection();
            if ($test['success'] ?? false) {
                try {
                    $routerPools = $mikrotik->getIpPools();
                    foreach ($routerPools as $rp) {
                        if (!empty($rp['ranges']) && str_contains($rp['ranges'], '-')) {
                            [$start, $end] = explode('-', $rp['ranges'], 2);
                            $pools[] = [
                                'pool_name' => $rp['name'] ?? null,
                                'ip_pool_start' => trim($start),
                                'ip_pool_end' => trim($end),
                            ];
                        }
                    }
                } catch (\Exception $e) {
                    // ignore
                }
            }
        }

        if (empty($pools)) {
            return back()->withInput()->with('error', 'Tidak bisa mengambil IP Pool dari router. Isi manual atau cek koneksi router.');
        }

        $firstPool = $pools[0];

        $area = Area::create([
            'name'          => $request->name,
            'router_ip'     => $request->router_ip,
            'router_user'   => $request->router_user,
            'router_pass'   => $request->router_pass,
            'ip_pool_start' => $firstPool['ip_pool_start'],
            'ip_pool_end'   => $firstPool['ip_pool_end'],
        ]);

        foreach ($pools as $i => $pool) {
            AreaIpPool::create([
                'area_id'       => $area->id,
                'pool_name'     => $pool['pool_name'] ?? null,
                'ip_pool_start' => $pool['ip_pool_start'],
                'ip_pool_end'   => $pool['ip_pool_end'],
                'sort_order'    => $i,
            ]);
        }

        $routerSyncSummary = $this->syncAreaRouterMetadata($area);

        $syncMsg = $this->autoSyncPppoe($area);

        // Auto-sync MikroTik to IPAM Routers
        $this->syncToIpamRouter($area);

        return redirect()->route('admin.areas.index')
            ->with('success', "Area created. {$syncMsg} {$routerSyncSummary}");
    }

    public function show(Area $area)
    {
        $area->load(['customers' => function ($query) {
            $query->latest()->limit(15);
        }]);

        return view('admin.areas.show', compact('area'));
    }

    public function edit(Area $area)
    {
        return view('admin.areas.edit', compact('area'));
    }

    public function update(Request $request, Area $area)
    {
        $request->validate([
            'name'                   => 'required|string|max:255',
            'router_ip'              => "required|string|max:255",
            'router_user'            => 'required|string|max:255',
            'router_pass'            => 'nullable|string|max:255',
            'pools'                  => 'required|array|min:1',
            'pools.*.ip_pool_start'  => 'required|ip',
            'pools.*.ip_pool_end'    => 'required|ip',
            'pools.*.pool_name'      => 'nullable|string|max:100',
        ]);

        $firstPool = $request->pools[0];

        $updateData = [
            'name'          => $request->name,
            'router_ip'     => $request->router_ip,
            'router_user'   => $request->router_user,
            'ip_pool_start' => $firstPool['ip_pool_start'],
            'ip_pool_end'   => $firstPool['ip_pool_end'],
        ];

        if ($request->filled('router_pass')) {
            $updateData['router_pass'] = $request->router_pass;
        }

        $area->update($updateData);

        // Replace all IP pools
        $area->ipPools()->delete();
        foreach ($request->pools as $i => $pool) {
            AreaIpPool::create([
                'area_id'       => $area->id,
                'pool_name'     => $pool['pool_name'] ?? null,
                'ip_pool_start' => $pool['ip_pool_start'],
                'ip_pool_end'   => $pool['ip_pool_end'],
                'sort_order'    => $i,
            ]);
        }

        $routerSyncSummary = $this->syncAreaRouterMetadata($area);

        $syncMsg = $this->autoSyncPppoe($area);

        // Auto-sync MikroTik to IPAM Routers
        $this->syncToIpamRouter($area);

        return redirect()->route('admin.areas.index')
            ->with('success', "Area updated. {$syncMsg} {$routerSyncSummary}");
    }

    public function destroy(Area $area)
    {
        if ($area->customers()->exists()) {
            return back()->with('error', 'Cannot delete area with existing customers');
        }

        $routerIp = $area->router_ip;
        $area->delete();

        // Remove matching IPAM Router (don't error if not found)
        if ($routerIp) {
            IpamRouter::where('wireguard_ip', $routerIp)->delete();
        }

        return redirect()->route('admin.areas.index')
            ->with('success', 'Area deleted successfully');
    }

    /**
     * Auto-sync Area MikroTik router to ipam_routers table.
     * Creates or updates the IpamRouter record based on router_ip.
     */
    private function syncToIpamRouter(Area $area): void
    {
        if (empty($area->router_ip)) {
            return;
        }

        try {
            $router = IpamRouter::firstOrNew(['wireguard_ip' => $area->router_ip]);
            $isNew  = !$router->exists;

            $router->device_name   = $area->router_identity ?: $area->name;
            $router->auth_username = $area->router_user;
            if (!empty($area->router_pass)) {
                $router->auth_password = $area->router_pass;
            }
            $router->save();

            $action = $isNew ? 'import' : 'update';
            IpamAuditService::log(
                $action, 'router', $router->id,
                ($isNew ? 'Auto-synced from Area: ' : 'Auto-updated from Area: ')
                . "{$router->device_name} ({$area->router_ip})"
            );
        } catch (\Throwable $e) {
            \Log::warning("IPAM router auto-sync failed for Area {$area->name}: " . $e->getMessage());
        }
    }

    /**
     * AJAX: Test router connection and fetch identity + IP pools
     */
    public function testRouter(Request $request)
    {
        $request->validate([
            'router_ip' => 'required|string',
            'router_user' => 'required|string',
            'router_pass' => 'required|string',
        ]);

        // Parse host:port if present
        $host = $request->router_ip;
        $port = 8728;
        if (str_contains($host, ':')) {
            [$host, $port] = explode(':', $host, 2);
            $port = (int) $port;
        }

        $mikrotik = new MikroTikService($host, $request->router_user, $request->router_pass, $port);

        $test = $mikrotik->testConnection();
        if (!$test['success']) {
            return response()->json([
                'success' => false,
                'error' => $test['error'] ?? 'Connection failed',
            ]);
        }

        // Fetch IP pools from router
        $pools = [];
        try {
            $poolsData = $mikrotik->getIpPools();
            foreach ($poolsData as $pool) {
                if (!empty($pool['ranges'])) {
                    $ranges = $pool['ranges'];
                    if (str_contains($ranges, '-')) {
                        [$start, $end] = explode('-', $ranges, 2);
                        $pools[] = [
                            'pool_name' => $pool['name'] ?? '',
                            'ip_pool_start' => trim($start),
                            'ip_pool_end' => trim($end),
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
            // Pools fetch failed — not critical
        }

        return response()->json([
            'success' => true,
            'identity' => $test['identity'] ?? 'Unknown',
            'pools' => $pools,
        ]);
    }

    /**
     * Auto-sync PPPoE secrets from MikroTik → Customers + Profiles → Packages
     */
    private function autoSyncPppoe(Area $area): string
    {
        if (!$area->router_ip) return '';

        try {
            $mikrotik = MikroTikService::forArea($area);
            $test = $mikrotik->testConnection();
            if (!$test['success']) return 'Router tidak bisa dihubungi.';

            // 1. Sync PPPoE profiles → Packages
            $profilesResult = $mikrotik->getPppoeProfiles();
            $profilesSynced = 0;
            if ($profilesResult['success'] ?? false) {
                foreach ($profilesResult['data'] as $profile) {
                    $profileName = $profile['name'] ?? null;
                    if (!$profileName || $profileName === 'default' || $profileName === 'default-encryption') continue;

                    if (Package::where('area_id', $area->id)->where('mikrotik_profile', $profileName)->exists()) continue;

                    // Parse rate-limit (e.g. "10M/5M")
                    $rateLimit = $profile['rate-limit'] ?? '';
                    $speedDown = 0;
                    $speedUp = 0;
                    if (preg_match('/(\d+)[Mm]\/(\d+)[Mm]/', $rateLimit, $m)) {
                        // MikroTik format: upload/download → e.g. "5M/10M"
                        $speedUp   = (int)$m[1]; // first = upload
                        $speedDown = (int)$m[2]; // second = download
                    }

                    Package::create([
                        'name' => $profileName,
                        'code' => Str::slug($profileName),
                        'area_id' => $area->id,
                        'mikrotik_profile' => $profileName,
                        'speed_down' => $speedDown,
                        'speed_up' => $speedUp,
                        'price' => 0,
                        'type' => 'residential',
                        'is_active' => true,
                    ]);
                    $profilesSynced++;
                }
            }

            // 2. Sync PPPoE secrets → Customers
            $secretsResult = $mikrotik->getAllSecrets();
            if (!$secretsResult['success']) return "Profiles: +{$profilesSynced}. Secrets gagal diambil.";

            $packages = Package::where('area_id', $area->id)
                ->whereNotNull('mikrotik_profile')
                ->where('mikrotik_profile', '!=', '')
                ->get()->keyBy('mikrotik_profile');

            $created = 0;
            $skipped = 0;

            foreach ($secretsResult['data'] as $secret) {
                $username = $secret['name'] ?? null;
                if (!$username) continue;

                if (Customer::forAreaPppoe($area->id, $username)->exists()) {
                    $skipped++;
                    continue;
                }

                $comment = $secret['comment'] ?? '';
                $profile = $secret['profile'] ?? 'default';
                $package = $packages->get($profile);

                Customer::create([
                    'name'            => $comment ?: $username,
                    'pppoe_user'      => $username,
                    'pppoe_pass'      => $secret['password'] ?? Str::random(12),
                    'portal_password' => Hash::make(Str::random(10)),
                    'area_id'         => $area->id,
                    'package_id'      => $package ? $package->id : null,
                    'package_price'   => $package ? $package->price : 0,
                    'status'          => 'active',
                ]);
                $created++;
            }

            return "PPPoE sync: +{$created} customer, {$skipped} exist, +{$profilesSynced} packages.";
        } catch (\Throwable $e) {
            return 'Sync error: ' . Str::limit($e->getMessage(), 80);
        }
    }

    private function syncAreaRouterMetadata(Area $area): string
    {
        try {
            $mikrotik = MikroTikService::forArea($area);
            $test = $mikrotik->testConnection();
            if (($test['success'] ?? false) !== true) {
                return 'Router metadata: koneksi gagal, identity/VLAN belum terdeteksi.';
            }

            $syncFields = ['router_identity' => $test['identity'] ?? null];
            $vlanData = $this->detectAreaVlans($mikrotik);
            if (!empty($vlanData['vlan_pppoe'])) {
                $syncFields['vlan_pppoe'] = $vlanData['vlan_pppoe'];
            }
            if (!empty($vlanData['vlan_mgmt'])) {
                $syncFields['vlan_mgmt'] = $vlanData['vlan_mgmt'];
            }
            $area->update($syncFields);

            $identity = trim((string) ($syncFields['router_identity'] ?? '')) ?: '-';
            $vlanPppoe = trim((string) ($syncFields['vlan_pppoe'] ?? '')) ?: 'belum kebaca';
            $vlanMgmt = trim((string) ($syncFields['vlan_mgmt'] ?? '')) ?: 'belum kebaca';

            return "Router metadata: identity {$identity}, VLAN PPPoE {$vlanPppoe}, VLAN MGMT {$vlanMgmt}.";
        } catch (\Throwable $e) {
            return 'Router metadata: sync gagal (' . Str::limit($e->getMessage(), 80) . ').';
        }
    }

    private function detectAreaVlans(MikroTikService $mikrotik): array
    {
        try {
            if (!$mikrotik->isConnected()) {
                return [];
            }

            $pppoeServers = $this->queryRouter($mikrotik, '/interface/pppoe-server/server/print', 'interface');
            $pppoeInterface = $this->pickPppoeInterface($pppoeServers);
            $pppoeVlan = $this->resolvePppoeVlan($mikrotik, $pppoeInterface);

            return [
                'vlan_pppoe' => $pppoeVlan['vlan_id'] ?? null,
                'vlan_mgmt' => $this->findMgmtVlan($mikrotik),
            ];
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function queryRouter(MikroTikService $mikrotik, string $path, string $proplist): array
    {
        try {
            $client = $this->getClient($mikrotik);
            $query = new Query($path);
            $query->equal('.proplist', $proplist);
            return $client->query($query)->read();
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function getClient(MikroTikService $mikrotik)
    {
        $ref = new \ReflectionClass($mikrotik);
        $prop = $ref->getProperty('client');
        $prop->setAccessible(true);
        return $prop->getValue($mikrotik);
    }

    private function pickPppoeInterface(array $pppoeServers): ?string
    {
        $best = null;
        $bestScore = PHP_INT_MIN;

        foreach ($pppoeServers as $server) {
            $name = trim((string) ($server['interface'] ?? ''));
            if ($name === '') {
                continue;
            }

            $score = 0;
            if ($this->looksLikePppoe($name)) {
                $score += 100;
            }
            if ($this->looksLikeMgmt($name)) {
                $score -= 1000;
            }
            if (preg_match('/\bvlan\b/i', $name)) {
                $score += 10;
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $name;
            }
        }

        return $best !== null && !$this->looksLikeMgmt($best) ? $best : null;
    }

    private function resolvePppoeVlan(MikroTikService $mikrotik, ?string $pppoeInterface): array
    {
        if (!$pppoeInterface || $this->looksLikeMgmt($pppoeInterface)) {
            return ['vlan_id' => null];
        }

        $details = $this->getVlanDetails($mikrotik, $pppoeInterface);
        if ($details === null) {
            $details = $this->findVlanByParentInterface($mikrotik, $pppoeInterface);
        }
        if ($details === null) {
            $details = $this->findVlanInsideBridge($mikrotik, $pppoeInterface);
        }
        if ($details === null) {
            return ['vlan_id' => null];
        }

        return ['vlan_id' => (string) ($details['vlan_id'] ?? '') ?: null];
    }

    private function getVlanDetails(MikroTikService $mikrotik, ?string $interfaceName): ?array
    {
        if (!$interfaceName || $this->looksLikeMgmt($interfaceName)) {
            return null;
        }

        try {
            $vlans = $this->getAllVlans($mikrotik);

            $target = strtolower(trim((string) $interfaceName));
            foreach ($vlans as $vlan) {
                $name = strtolower(trim((string) ($vlan['name'] ?? '')));
                if ($name === $target && isset($vlan['vlan-id'])) {
                    return [
                        'name' => (string) ($vlan['name'] ?? ''),
                        'vlan_id' => (string) $vlan['vlan-id'],
                        'comment' => (string) ($vlan['comment'] ?? ''),
                    ];
                }
            }
        } catch (\Throwable $e) {
        }

        return null;
    }

    private function getAllVlans(MikroTikService $mikrotik): array
    {
        try {
            $client = $this->getClient($mikrotik);
            $query = new Query('/interface/vlan/print');
            $query->equal('.proplist', 'name,vlan-id,interface,comment');
            $rows = $client->query($query)->read();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function findVlanByParentInterface(MikroTikService $mikrotik, ?string $parentInterface): ?array
    {
        $parent = strtolower(trim((string) $parentInterface));
        if ($parent === '' || $this->looksLikeMgmt($parent)) {
            return null;
        }

        $best = null;
        $bestScore = PHP_INT_MIN;
        foreach ($this->getAllVlans($mikrotik) as $vlan) {
            $linkedParent = strtolower(trim((string) ($vlan['interface'] ?? '')));
            if ($linkedParent !== $parent) {
                continue;
            }

            $score = $this->scoreVlanCandidate($vlan);
            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $vlan;
            }
        }

        return $best;
    }

    private function findVlanInsideBridge(MikroTikService $mikrotik, ?string $bridgeName): ?array
    {
        $bridge = strtolower(trim((string) $bridgeName));
        if ($bridge === '' || $this->looksLikeMgmt($bridge)) {
            return null;
        }

        try {
            $client = $this->getClient($mikrotik);
            $query = new Query('/interface/bridge/port/print');
            $query->equal('.proplist', 'interface,bridge');
            $ports = $client->query($query)->read();
        } catch (\Throwable $e) {
            return null;
        }

        $best = null;
        $bestScore = PHP_INT_MIN;
        foreach ((array) $ports as $port) {
            $portBridge = strtolower(trim((string) ($port['bridge'] ?? '')));
            if ($portBridge !== $bridge) {
                continue;
            }

            $member = trim((string) ($port['interface'] ?? ''));
            $candidate = $this->getVlanDetails($mikrotik, $member) ?? $this->findVlanByParentInterface($mikrotik, $member);
            if ($candidate === null) {
                continue;
            }

            $score = $this->scoreVlanCandidate($candidate);
            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $candidate;
            }
        }

        return $best;
    }

    private function scoreVlanCandidate(array $vlan): int
    {
        $name = strtolower(trim((string) ($vlan['name'] ?? '')));
        $comment = strtolower(trim((string) ($vlan['comment'] ?? '')));
        $score = 0;

        if ($this->looksLikePppoe($name) || $this->looksLikePppoe($comment)) {
            $score += 100;
        }
        if ($this->looksLikeMgmt($name) || $this->looksLikeMgmt($comment)) {
            $score -= 1000;
        }
        if (str_contains($name, 'vlan')) {
            $score += 10;
        }

        return $score;
    }

    private function findMgmtVlan(MikroTikService $mikrotik): ?string
    {
        try {
            $client = $this->getClient($mikrotik);
            $query = new Query('/interface/vlan/print');
            $query->equal('.proplist', 'name,vlan-id,comment');
            $vlans = $client->query($query)->read();

            foreach ($vlans as $vlan) {
                $name = strtolower((string) ($vlan['name'] ?? ''));
                $comment = strtolower((string) ($vlan['comment'] ?? ''));
                if (str_contains($name, 'mgmt') || str_contains($name, 'management') ||
                    str_contains($comment, 'mgmt') || str_contains($comment, 'management')) {
                    return (string) ($vlan['vlan-id'] ?? '');
                }
            }
        } catch (\Throwable $e) {
        }

        return null;
    }

    private function looksLikeMgmt(?string $name): bool
    {
        $value = strtolower(trim((string) $name));
        return $value !== '' && (str_contains($value, 'mgmt') || str_contains($value, 'management'));
    }

    private function looksLikePppoe(?string $name): bool
    {
        $value = strtolower(trim((string) $name));
        return $value !== '' && str_contains($value, 'pppoe');
    }

    public function installIsolirRule(Area $area)
    {
        try {
            $mikrotik = MikroTikService::forArea($area);
            $client = $this->getClient($mikrotik);

            // Fetch current filter rules to find the first rule's ID
            $rules = $client->query(new Query('/ip/firewall/filter/print'))->read();
            
            $params = [
                'action' => 'drop',
                'chain' => 'forward',
                'comment' => 'BLOCK TOTAL KONEKSI ISOLIR',
                'src-address-list' => 'isolir'
            ];

            // If there are existing rules, place this new rule before the very first rule
            if (!empty($rules) && isset($rules[0]['.id'])) {
                $params['place-before'] = $rules[0]['.id'];
            }

            $query = new Query('/ip/firewall/filter/add', $params);
            $client->query($query)->read();

            return back()->with('success', "Berhasil! Script 1 Baris (Block Total) sudah tertanam di urutan paling atas pada router {$area->name}.");
        } catch (\Throwable $e) {
            return back()->with('error', "Gagal menginstall rule ke {$area->name}: " . $e->getMessage());
        }
    }
}

