<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Customer;
use App\Models\User;
use App\Services\MikroTikService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PppoeController extends Controller
{
    private function accessibleAreaIds($user)
    {
        if ($user->role !== 'partner') {
            return Area::whereNotNull('router_ip')
                ->where('router_ip', '!=', '')
                ->pluck('id');
        }

        $customerAreaIds = Customer::where('partner_id', $user->id)
            ->whereNotNull('area_id')
            ->pluck('area_id');

        return $customerAreaIds
            ->push($user->area_id)
            ->filter()
            ->unique()
            ->values();
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        // Partners can only see routers for areas they actually own customers in.
        $accessibleAreaIds = $this->accessibleAreaIds($user);
        $areas = Area::whereNotNull('router_ip')->where('router_ip', '!=', '')->withCount('customers');
        if ($user->role === 'partner') {
            $areas->whereIn('id', $accessibleAreaIds);
        }
        $areas = $areas->get();

        $selectedArea = null;
        $secrets      = [];
        $sessions     = [];
        $routerInfo   = null;
        $error        = null;

        if ($request->filled('area_id')) {
            $selectedArea = Area::findOrFail($request->area_id);

            // Double-check partner cannot view other owned-outside areas.
            if ($user->role === 'partner' && !$accessibleAreaIds->contains($selectedArea->id)) {
                abort(403, 'Access denied.');
            }

            $mikrotik   = MikroTikService::forArea($selectedArea);
            $testResult = $mikrotik->testConnection();

            if (!$testResult['success']) {
                $error = 'Cannot connect to router: ' . ($testResult['error'] ?? 'Unknown error');
            } else {
                $routerInfo     = $testResult;
                $secretsResult  = $mikrotik->getAllSecrets();
                $sessionsResult = $mikrotik->getActiveSessions();

                $secrets  = $secretsResult['success']  ? $secretsResult['data']  : [];
                $sessions = $sessionsResult['success']  ? $sessionsResult['data'] : [];
            }
        }

        $activeSessions = collect($sessions)->keyBy('name');

        return view('admin.pppoe.index', compact(
            'areas',
            'selectedArea',
            'secrets',
            'activeSessions',
            'routerInfo',
            'error'
        ));
    }

    /**
     * Sync PPPoE secrets from MikroTik → Customers table
     * Creates new customers AND updates existing ones (name, package, password)
     */
    public function syncCustomers(Request $request)
    {
        $request->validate(['area_id' => 'required|exists:areas,id']);

        $user = auth()->user();
        $area = Area::findOrFail($request->area_id);
        $accessibleAreaIds = $this->accessibleAreaIds($user);

        if ($user->role === 'partner' && !$accessibleAreaIds->contains($area->id)) {
            abort(403, 'Access denied.');
        }

        $mikrotik   = MikroTikService::forArea($area);
        $testResult = $mikrotik->testConnection();

        if (!$testResult['success']) {
            return back()->with('error', 'Cannot connect to MikroTik: ' . $testResult['error']);
        }

        $secretsResult = $mikrotik->getAllSecrets();
        if (!$secretsResult['success']) {
            return back()->with('error', 'Failed to fetch secrets from MikroTik.');
        }

        // Pre-load packages indexed by mikrotik_profile
        $packages = \App\Models\Package::where('area_id', $area->id)
            ->whereNotNull('mikrotik_profile')
            ->where('mikrotik_profile', '!=', '')
            ->get()
            ->keyBy('mikrotik_profile');

        $areaPartners = User::where('role', 'partner')
            ->where('area_id', $area->id)
            ->orderBy('id')
            ->get(['id', 'name']);

        $created   = 0;
        $updated   = 0;
        $unchanged = 0;
        $assignedPartner = null;
        $partnerId = null;

        if ($user->role === 'partner') {
            $partnerId = $user->id;
            $assignedPartner = $user;
        } elseif ($areaPartners->count() === 1) {
            $assignedPartner = $areaPartners->first();
            $partnerId = $assignedPartner->id;
        }

        foreach ($secretsResult['data'] as $secret) {
            $username = $secret['name'] ?? null;
            if (!$username) continue;

            $comment = $secret['comment'] ?? '';
            $profile = $secret['profile'] ?? 'default';
            $package = $packages->get($profile);

            // PPPoE usernames can repeat across areas, so sync must stay area-scoped.
            $existing = Customer::forAreaPppoe($area->id, $username)->first();

            if ($existing) {
                // UPDATE existing customer: name from comment, package from profile
                $changes = [];
                if ($comment && $existing->name !== trim($comment)) {
                    $changes['name'] = trim($comment);
                }
                if ($package && $existing->package_id != $package->id) {
                    $changes['package_id'] = $package->id;
                    $changes['package_price'] = $package->price;
                }
                if ($existing->area_id != $area->id) {
                    $changes['area_id'] = $area->id;
                }
                if ($existing->partner_id === null && $partnerId) {
                    $changes['partner_id'] = $partnerId;
                }

                if (!empty($changes)) {
                    $existing->update($changes);
                    $updated++;
                } else {
                    $unchanged++;
                }
                continue;
            }

            // CREATE new customer
            Customer::create([
                'name'            => $comment ?: $username,
                'pppoe_user'      => $username,
                'pppoe_pass'      => $secret['password'] ?? Str::random(12),
                'portal_password' => Hash::make(Str::random(10)),
                'area_id'         => $area->id,
                'partner_id'      => $partnerId,
                'package_id'      => $package ? $package->id : null,
                'package_price'   => $package ? $package->price : 0,
                'status'          => 'active',
            ]);
            $created++;
        }

        $message = "Sync selesai: {$created} baru, {$updated} diupdate, {$unchanged} tidak berubah.";

        if ($assignedPartner) {
            $message .= " Partner auto-assigned: {$assignedPartner->name}.";
        } elseif ($user->role === 'admin') {
            if ($areaPartners->isEmpty()) {
                $message .= " Partner belum di-assign ke area ini, jadi customer hasil sync tetap tanpa partner.";
            } else {
                $message .= " Area ini punya lebih dari 1 partner, jadi partner tidak di-auto-assign.";
            }
        }

        return back()->with('success', $message);
    }

    public function disconnect(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'area_id'  => 'required|exists:areas,id',
        ]);

        $area     = Area::findOrFail($request->area_id);
        $mikrotik = MikroTikService::forArea($area);
        $result   = $mikrotik->disconnectSession($request->username);

        return $result['success']
            ? back()->with('success', "Session '{$request->username}' disconnected.")
            : back()->with('error', $result['error'] ?? 'Disconnect failed.');
    }

    public function toggle(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'area_id'  => 'required|exists:areas,id',
            'enable'   => 'required|boolean',
        ]);

        $area     = Area::findOrFail($request->area_id);
        $mikrotik = MikroTikService::forArea($area);
        $result   = $mikrotik->toggleSecret($request->username, (bool) $request->enable);

        if ($result['success']) {
            $action = $request->enable ? 'enabled' : 'disabled';
            return back()->with('success', "PPPoE secret '{$request->username}' {$action}.");
        }

        return back()->with('error', $result['error'] ?? 'Toggle failed.');
    }

    /**
     * Traffic monitor — show live interface bandwidth per router
     */
    public function traffic(Request $request)
    {
        $request->validate(['area_id' => 'required|exists:areas,id']);

        $area = Area::findOrFail($request->area_id);
        $mikrotik = MikroTikService::forArea($area);

        try {
            $traffic = $mikrotik->monitorAllInterfaces();
            return response()->json([
                'success' => true,
                'area' => $area->name,
                'data' => $traffic,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 503);
        }
    }

    /**
     * IP Pool usage per router
     */
    public function pools(Request $request)
    {
        $request->validate(['area_id' => 'required|exists:areas,id']);

        $area = Area::findOrFail($request->area_id);
        $mikrotik = MikroTikService::forArea($area);

        try {
            $pools = $mikrotik->getIpPools();
            return response()->json([
                'success' => true,
                'area' => $area->name,
                'data' => $pools,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 503);
        }
    }

    /**
     * Ping from router
     */
    public function ping(Request $request)
    {
        $request->validate([
            'area_id' => 'required|exists:areas,id',
            'target' => 'required|ip',
            'count' => 'nullable|integer|min:1|max:20',
        ]);

        $area = Area::findOrFail($request->area_id);
        $mikrotik = MikroTikService::forArea($area);

        try {
            $result = $mikrotik->ping($request->target, $request->input('count', 5));
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 503);
        }
    }
}
