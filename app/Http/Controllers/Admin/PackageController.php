<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Package;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    private function defaultPriceBySpeed(int $speedDown): float
    {
        $map = (array) config('billing.default_speed_prices', []);
        if (isset($map[$speedDown])) {
            return (float) $map[$speedDown];
        }

        return (float) config('billing.default_package_price', 100000);
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Package::with('area')->withCount('customers');

        if ($user && $user->role === 'partner') {
            $query->where('area_id', $user->area_id);
        } elseif ($request->filled('area_id')) {
            $query->where('area_id', $request->integer('area_id'));
        }

        $packages = $query->orderBy('name')->get();
        $areas = ($user && $user->role === 'admin')
            ? Area::orderBy('name')->get(['id', 'name'])
            : collect();

        return view('admin.packages.index', compact('packages', 'areas'));
    }

    public function create()
    {
        $user = auth()->user();
        $areas = ($user && $user->role === 'partner')
            ? Area::where('id', $user->area_id)->orderBy('name')->get()
            : Area::orderBy('name')->get();
        return view('admin.packages.create', compact('areas'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if ($user && $user->role === 'partner') {
            $request->merge(['area_id' => $user->area_id]);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:packages,code',
            'speed_down' => 'required|integer|min:1',
            'speed_up' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'type' => 'required|in:residential,business,corporate',
            'description' => 'nullable|string|max:500',
            'mikrotik_profile' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'area_id' => 'nullable|exists:areas,id',
        ]);

        $validated['is_active'] = $request->has('is_active');

        Package::create($validated);

        return redirect()->route('admin.packages.index')
            ->with('success', 'Package created successfully');
    }

    public function edit(Package $package)
    {
        $user = auth()->user();
        if ($user && $user->role === 'partner' && (int) $package->area_id !== (int) $user->area_id) {
            abort(403);
        }

        $areas = ($user && $user->role === 'partner')
            ? Area::where('id', $user->area_id)->orderBy('name')->get()
            : Area::orderBy('name')->get();
        return view('admin.packages.edit', compact('package', 'areas'));
    }

    public function update(Request $request, Package $package)
    {
        $user = auth()->user();
        if ($user && $user->role === 'partner') {
            if ((int) $package->area_id !== (int) $user->area_id) {
                abort(403);
            }
            $request->merge(['area_id' => $user->area_id]);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:packages,code,' . $package->id,
            'speed_down' => 'required|integer|min:1',
            'speed_up' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'type' => 'required|in:residential,business,corporate',
            'description' => 'nullable|string|max:500',
            'mikrotik_profile' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'area_id' => 'nullable|exists:areas,id',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $package->update($validated);

        return redirect()->route('admin.packages.index')
            ->with('success', 'Package updated successfully');
    }

    public function destroy(Package $package)
    {
        $user = auth()->user();
        if ($user && $user->role === 'partner' && (int) $package->area_id !== (int) $user->area_id) {
            abort(403);
        }

        if ($package->customers()->exists()) {
            return back()->with('error', 'Cannot delete package with existing customers. Deactivate it instead.');
        }

        $package->delete();

        return redirect()->route('admin.packages.index')
            ->with('success', 'Package deleted successfully');
    }

    /**
     * Sync PPPoE profiles from MikroTik (READ-ONLY from MikroTik)
     * Creates/updates Package records matching MikroTik profile names
     */
    public function syncFromMikrotik()
    {
        $areas = Area::whereNotNull('router_ip')->get();

        if ($areas->isEmpty()) {
            return back()->with('error', 'No areas with MikroTik configured.');
        }

        $created = 0;
        $updated = 0;
        $errors = [];

        foreach ($areas as $area) {
            try {
                $mikrotik = \App\Services\MikroTikService::forArea($area);
                if (!$mikrotik->isConnected()) {
                    $errors[] = "{$area->name}: koneksi MikroTik gagal";
                    continue;
                }

                $profiles = $mikrotik->getProfiles();
                if (!is_array($profiles)) {
                    $errors[] = "{$area->name}: data profile dari MikroTik tidak valid";
                    continue;
                }

                foreach ($profiles as $profile) {
                    if (!is_array($profile)) {
                        continue;
                    }

                    $name = $profile['name'] ?? null;
                    if (!$name || $name === 'default' || $name === 'default-encryption') continue;

                    // Parse rate-limit: "upload/download" format like "5M/10M"
                    $rateLimit = $profile['rate-limit'] ?? '';
                    $speedUp = 0;
                    $speedDown = 0;
                    if (preg_match('/(\d+)[MmKk]?\/(\d+)[MmKk]?/', $rateLimit, $m)) {
                        $speedUp = (int) $m[1];
                        $speedDown = (int) $m[2];
                    }

                    $existing = Package::where('mikrotik_profile', $name)
                        ->where('area_id', $area->id)
                        ->first();

                    if ($existing) {
                        // Only update speed if changed
                        $changes = [];
                        if ($speedDown > 0 && $existing->speed_down != $speedDown) $changes['speed_down'] = $speedDown;
                        if ($speedUp > 0 && $existing->speed_up != $speedUp) $changes['speed_up'] = $speedUp;
                        if ((float) $existing->price <= 0 && $speedDown > 0) {
                            $changes['price'] = $this->defaultPriceBySpeed($speedDown);
                        }
                        if (!empty($changes)) {
                            $existing->update($changes);
                            $updated++;
                        }
                    } else {
                        $resolvedDown = $speedDown ?: 10;
                        Package::create([
                            'name' => $name,
                            'code' => strtoupper(str_replace(' ', '-', $name)) . '-' . $area->id,
                            'speed_down' => $resolvedDown,
                            'speed_up' => $speedUp ?: 5,
                            'price' => $this->defaultPriceBySpeed($resolvedDown),
                            'type' => 'residential',
                            'mikrotik_profile' => $name,
                            'area_id' => $area->id,
                            'is_active' => true,
                        ]);
                        $created++;
                    }
                }
            } catch (\Throwable $e) {
                $errors[] = "{$area->name}: {$e->getMessage()}";
            }
        }

        $msg = "Sync complete — {$created} new, {$updated} updated.";
        if (!empty($errors)) {
            $msg .= ' Errors: ' . implode('; ', $errors);
        }

        return back()->with($errors && !$created && !$updated ? 'error' : 'success', $msg);
    }
}
