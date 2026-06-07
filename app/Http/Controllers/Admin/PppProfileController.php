<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Package;
use App\Services\MikroTikService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PppProfileController extends Controller
{
    public function index(Request $request)
    {
        $areas = Area::whereNotNull('router_ip')
            ->where('router_ip', '!=', '')
            ->get();

        $selectedArea = null;
        $profiles = [];
        $error = null;

        if ($request->filled('area_id')) {
            $selectedArea = Area::findOrFail($request->area_id);
            $mikrotik = MikroTikService::forArea($selectedArea);

            try {
                $result = $mikrotik->getPppoeProfiles();

                if (!$result['success']) {
                    $error = 'Gagal terhubung ke router: ' . ($result['error'] ?? 'Unknown');
                } else {
                    foreach ($result['data'] as $profile) {
                        $name = $profile['name'] ?? '';
                        if (!$name) continue;

                        $subscriberCount = 0;
                        try {
                            $subscriberCount = $mikrotik->countSecretsForProfile($name);
                        } catch (\Throwable $e) {
                            // Skip counting if connection lost
                        }

                        $profiles[] = [
                            'id' => $profile['.id'] ?? '',
                            'name' => $name,
                            'rate-limit' => $profile['rate-limit'] ?? '',
                            'local-address' => $profile['local-address'] ?? '',
                            'remote-address' => $profile['remote-address'] ?? '',
                            'dns-server' => $profile['dns-server'] ?? '',
                            'change-tcp-mss' => $profile['change-tcp-mss'] ?? '',
                            'only-one' => $profile['only-one'] ?? '',
                            'subscribers' => $subscriberCount,
                        ];
                    }
                }
            } catch (\Throwable $e) {
                $error = 'Gagal terhubung ke router: ' . $e->getMessage();
            }
        }

        return view('admin.ppp-profiles.index', compact('areas', 'selectedArea', 'profiles', 'error'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'area_id' => 'required|exists:areas,id',
            'name' => 'required|string|max:100|regex:/^[a-zA-Z0-9\-_]+$/',
            'rate_limit' => 'required|string|regex:/^\d+[MmKk]?\/\d+[MmKk]?$/',
            'local_address' => 'nullable|string',
            'remote_address' => 'nullable|string',
            'dns_server' => 'nullable|string',
            'change_tcp_mss' => 'nullable|in:yes,no',
            'only_one' => 'nullable|in:yes,no,default',
        ]);

        $area = Area::findOrFail($request->area_id);
        $mikrotik = MikroTikService::forArea($area);

        $options = array_filter([
            'local-address' => $request->local_address,
            'remote-address' => $request->remote_address,
            'dns-server' => $request->dns_server,
            'change-tcp-mss' => $request->change_tcp_mss,
            'only-one' => $request->only_one,
        ], fn($v) => $v !== null && $v !== '');

        $result = $mikrotik->createPppProfile($request->name, $request->rate_limit, $options);

        if (!$result['success']) {
            return back()->with('error', 'Gagal membuat profile: ' . ($result['error'] ?? 'Unknown'))->withInput();
        }

        // Sync to local Package record
        $this->syncProfileToPackage($request->name, $request->rate_limit, $area->id);

        Log::info('PPP Profile created', [
            'admin' => auth()->user()->name,
            'profile' => $request->name,
            'area' => $area->name,
        ]);

        return redirect()->route('admin.ppp-profiles.index', ['area_id' => $area->id])
            ->with('success', "Profile '{$request->name}' berhasil dibuat.");
    }

    public function update(Request $request)
    {
        $request->validate([
            'area_id' => 'required|exists:areas,id',
            'profile_id' => 'required|string',
            'profile_name' => 'required|string',
            'rate_limit' => 'required|string|regex:/^\d+[MmKk]?\/\d+[MmKk]?$/',
            'local_address' => 'nullable|string',
            'remote_address' => 'nullable|string',
            'dns_server' => 'nullable|string',
            'change_tcp_mss' => 'nullable|in:yes,no',
            'only_one' => 'nullable|in:yes,no,default',
        ]);

        $area = Area::findOrFail($request->area_id);
        $mikrotik = MikroTikService::forArea($area);

        $params = [
            'rate-limit' => $request->rate_limit,
        ];
        if ($request->filled('local_address')) $params['local-address'] = $request->local_address;
        if ($request->filled('remote_address')) $params['remote-address'] = $request->remote_address;
        if ($request->filled('dns_server')) $params['dns-server'] = $request->dns_server;
        if ($request->filled('change_tcp_mss')) $params['change-tcp-mss'] = $request->change_tcp_mss;
        if ($request->filled('only_one')) $params['only-one'] = $request->only_one;

        $result = $mikrotik->updatePppProfile($request->profile_id, $params);

        if (!$result['success']) {
            return back()->with('error', 'Gagal update profile: ' . ($result['error'] ?? 'Unknown'))->withInput();
        }

        // Sync to local Package record
        $this->syncProfileToPackage($request->profile_name, $request->rate_limit, $area->id);

        Log::info('PPP Profile updated', [
            'admin' => auth()->user()->name,
            'profile' => $request->profile_name,
            'area' => $area->name,
        ]);

        return redirect()->route('admin.ppp-profiles.index', ['area_id' => $area->id])
            ->with('success', "Profile '{$request->profile_name}' berhasil diupdate.");
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'area_id' => 'required|exists:areas,id',
            'profile_id' => 'required|string',
            'profile_name' => 'required|string',
        ]);

        $area = Area::findOrFail($request->area_id);
        $mikrotik = MikroTikService::forArea($area);

        // Check if profile has subscribers
        $count = $mikrotik->countSecretsForProfile($request->profile_name);
        if ($count > 0) {
            return back()->with('error', "Tidak bisa hapus profile '{$request->profile_name}' — masih digunakan oleh {$count} subscriber.");
        }

        $result = $mikrotik->deletePppProfile($request->profile_id);

        if (!$result['success']) {
            return back()->with('error', 'Gagal hapus profile: ' . ($result['error'] ?? 'Unknown'));
        }

        // Deactivate local Package
        Package::where('mikrotik_profile', $request->profile_name)
            ->where(function ($q) use ($area) {
                $q->where('area_id', $area->id)->orWhereNull('area_id');
            })
            ->update(['is_active' => false]);

        Log::info('PPP Profile deleted', [
            'admin' => auth()->user()->name,
            'profile' => $request->profile_name,
            'area' => $area->name,
        ]);

        return redirect()->route('admin.ppp-profiles.index', ['area_id' => $area->id])
            ->with('success', "Profile '{$request->profile_name}' berhasil dihapus.");
    }

    /**
     * Sync a MikroTik profile to local Package record
     */
    private function syncProfileToPackage(string $profileName, string $rateLimit, int $areaId): void
    {
        // Parse rate-limit (format: "10M/5M" or "10000000/5000000")
        $speedDown = 0;
        $speedUp = 0;
        if (preg_match('/(\d+)[MmKk]?\/(\d+)[MmKk]?/', $rateLimit, $m)) {
            $speedUp = (int)$m[1];
            $speedDown = (int)$m[2];
        }

        $package = Package::where('mikrotik_profile', $profileName)
            ->where(function ($q) use ($areaId) {
                $q->where('area_id', $areaId)->orWhereNull('area_id');
            })
            ->first();

        if ($package) {
            $package->update([
                'speed_down' => $speedDown,
                'speed_up' => $speedUp,
                'is_active' => true,
            ]);
        } else {
            $code = strtoupper(str_replace(' ', '-', $profileName)) . '-' . $areaId;
            Package::create([
                'name' => $profileName,
                'code' => $code,
                'mikrotik_profile' => $profileName,
                'speed_down' => $speedDown,
                'speed_up' => $speedUp,
                'price' => config('billing.default_speed_prices.' . $speedDown, 0),
                'type' => 'pppoe',
                'is_active' => true,
                'area_id' => $areaId,
            ]);
        }
    }
}
