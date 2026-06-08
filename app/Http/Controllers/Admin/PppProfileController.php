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

            // Read from local packages table (already synced from MikroTik)
            $packages = Package::where('area_id', $selectedArea->id)
                ->withCount('customers')
                ->orderBy('name')
                ->get();

            foreach ($packages as $pkg) {
                $profiles[] = [
                    'id' => (string) $pkg->id,
                    'name' => $pkg->mikrotik_profile ?: $pkg->name,
                    'rate-limit' => $pkg->speed_up . 'M/' . $pkg->speed_down . 'M',
                    'local-address' => '',
                    'remote-address' => '',
                    'dns-server' => '',
                    'change-tcp-mss' => '',
                    'only-one' => '',
                    'subscribers' => $pkg->customers_count,
                    'is_active' => $pkg->is_active,
                    'price' => $pkg->price,
                    'package_id' => $pkg->id,
                ];
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

        // Parse speed from rate_limit
        $speedUp = 0;
        $speedDown = 0;
        if (preg_match('/(\d+)[MmKk]?\/(\d+)[MmKk]?/', $request->rate_limit, $m)) {
            $speedUp = (int) $m[1];
            $speedDown = (int) $m[2];
        }

        // Try to create on router (non-blocking — if fails, still save locally)
        $routerSuccess = false;
        try {
            $mikrotik = MikroTikService::forArea($area);
            $options = array_filter([
                'local-address' => $request->local_address,
                'remote-address' => $request->remote_address,
                'dns-server' => $request->dns_server,
                'change-tcp-mss' => $request->change_tcp_mss,
                'only-one' => $request->only_one,
            ], fn($v) => $v !== null && $v !== '');

            $result = $mikrotik->createPppProfile($request->name, $request->rate_limit, $options);
            $routerSuccess = $result['success'] ?? false;
        } catch (\Throwable $e) {
            // Router unreachable — continue with local save
        }

        // Save to local Package
        $code = strtoupper(str_replace(' ', '-', $request->name)) . '-' . $area->id;
        Package::create([
            'name' => $request->name,
            'code' => $code,
            'mikrotik_profile' => $request->name,
            'speed_down' => $speedDown,
            'speed_up' => $speedUp,
            'price' => 0,
            'type' => 'pppoe',
            'is_active' => true,
            'area_id' => $area->id,
        ]);

        $msg = "Profile '{$request->name}' berhasil dibuat.";
        if (!$routerSuccess) {
            $msg .= ' (Belum tersimpan ke router — router tidak dapat dihubungi. Sync manual nanti.)';
        }

        return redirect()->route('admin.ppp-profiles.index', ['area_id' => $area->id])
            ->with('success', $msg);
    }

    public function update(Request $request)
    {
        $request->validate([
            'area_id' => 'required|exists:areas,id',
            'profile_id' => 'required',
            'profile_name' => 'required|string',
            'rate_limit' => 'required|string|regex:/^\d+[MmKk]?\/\d+[MmKk]?$/',
        ]);

        $area = Area::findOrFail($request->area_id);

        // Parse speed
        $speedUp = 0;
        $speedDown = 0;
        if (preg_match('/(\d+)[MmKk]?\/(\d+)[MmKk]?/', $request->rate_limit, $m)) {
            $speedUp = (int) $m[1];
            $speedDown = (int) $m[2];
        }

        // Update local Package
        $package = Package::find($request->profile_id);
        if ($package) {
            $package->update([
                'speed_down' => $speedDown,
                'speed_up' => $speedUp,
            ]);
        }

        // Try to update on router (best-effort)
        try {
            $mikrotik = MikroTikService::forArea($area);
            $mikrotik->updatePppProfile($request->profile_id, ['rate-limit' => $request->rate_limit]);
        } catch (\Throwable $e) {
            // Ignore router errors
        }

        return redirect()->route('admin.ppp-profiles.index', ['area_id' => $area->id])
            ->with('success', "Profile '{$request->profile_name}' berhasil diupdate.");
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'area_id' => 'required|exists:areas,id',
            'profile_id' => 'required',
            'profile_name' => 'required|string',
        ]);

        $area = Area::findOrFail($request->area_id);

        // Deactivate local Package
        $package = Package::find($request->profile_id);
        if ($package) {
            if ($package->customers()->count() > 0) {
                return back()->with('error', "Tidak bisa hapus profile '{$request->profile_name}' — masih digunakan oleh {$package->customers()->count()} pelanggan.");
            }
            $package->update(['is_active' => false]);
        }

        // Try to delete from router (best-effort)
        try {
            $mikrotik = MikroTikService::forArea($area);
            $mikrotik->deletePppProfile($request->profile_id);
        } catch (\Throwable $e) {
            // Ignore router errors
        }

        return redirect()->route('admin.ppp-profiles.index', ['area_id' => $area->id])
            ->with('success', "Profile '{$request->profile_name}' berhasil dihapus.");
    }
}
