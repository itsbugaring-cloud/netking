<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\AreaIpPool;
use App\Models\Customer;
use App\Models\Package;
use App\Services\MikroTikService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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
            'router_ip'              => 'required|ip|unique:areas,router_ip',
            'router_user'            => 'required|string|max:255',
            'router_pass'            => 'required|string|max:255',
            'pools'                  => 'required|array|min:1',
            'pools.*.ip_pool_start'  => 'required|ip',
            'pools.*.ip_pool_end'    => 'required|ip',
            'pools.*.pool_name'      => 'nullable|string|max:100',
        ]);

        $firstPool = $request->pools[0];

        $area = Area::create([
            'name'          => $request->name,
            'router_ip'     => $request->router_ip,
            'router_user'   => $request->router_user,
            'router_pass'   => $request->router_pass,
            'ip_pool_start' => $firstPool['ip_pool_start'],
            'ip_pool_end'   => $firstPool['ip_pool_end'],
        ]);

        foreach ($request->pools as $i => $pool) {
            AreaIpPool::create([
                'area_id'       => $area->id,
                'pool_name'     => $pool['pool_name'] ?? null,
                'ip_pool_start' => $pool['ip_pool_start'],
                'ip_pool_end'   => $pool['ip_pool_end'],
                'sort_order'    => $i,
            ]);
        }

        $syncMsg = $this->autoSyncPppoe($area);

        return redirect()->route('admin.areas.index')
            ->with('success', "Area created. {$syncMsg}");
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
            'router_ip'              => "required|ip|unique:areas,router_ip,{$area->id}",
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

        $syncMsg = $this->autoSyncPppoe($area);

        return redirect()->route('admin.areas.index')
            ->with('success', "Area updated. {$syncMsg}");
    }

    public function destroy(Area $area)
    {
        if ($area->customers()->exists()) {
            return back()->with('error', 'Cannot delete area with existing customers');
        }

        $area->delete();

        return redirect()->route('admin.areas.index')
            ->with('success', 'Area deleted successfully');
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
}

