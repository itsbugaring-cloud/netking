<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Customer;
use App\Models\User;
use App\Services\MikroTikService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('area')->orderBy('name')->paginate(20);
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $areas = Area::orderBy('name')->get();
        return view('admin.users.create', compact('areas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)],
            'role'     => 'required|in:admin,partner,finance',
            'area_id'  => 'required_if:role,partner|nullable|exists:areas,id',
            'telegram_username' => ['nullable', 'regex:/^@?[A-Za-z0-9_]{5,32}$/', 'unique:users,telegram_username'],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        // Only partners need area_id
        if ($validated['role'] !== 'partner') {
            $validated['area_id'] = null;
        }
        $validated['telegram_username'] = $this->normalizeTelegramUsername($validated['telegram_username'] ?? null);

        $user = User::create($validated);

        // Auto-sync: When a partner is created with an area, import all PPPoE
        // secrets from that area's MikroTik as customers automatically
        $synced = 0;
        if ($user->role === 'partner' && $user->area_id) {
            $synced = $this->syncMikrotikCustomers($user);
        }

        $message = 'User created successfully.';
        if ($synced > 0) {
            $message .= " {$synced} customers auto-imported from MikroTik.";
        }

        return redirect()->route('admin.users.index')->with('success', $message);
    }

    public function edit(User $user)
    {
        $areas = Area::orderBy('name')->get();
        return view('admin.users.edit', compact('user', 'areas'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,' . $user->id,
            'password' => ['nullable', 'confirmed', Password::min(8)],
            'role'     => 'required|in:admin,partner,finance',
            'area_id'  => 'required_if:role,partner|nullable|exists:areas,id',
            'telegram_username' => ['nullable', 'regex:/^@?[A-Za-z0-9_]{5,32}$/', 'unique:users,telegram_username,' . $user->id],
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        if ($validated['role'] !== 'partner') {
            $validated['area_id'] = null;
        }
        $validated['telegram_username'] = $this->normalizeTelegramUsername($validated['telegram_username'] ?? null);

        $oldAreaId = $user->area_id;
        $user->update($validated);

        // If area_id changed (or newly set), sync customers for the new area
        $synced = 0;
        if ($user->role === 'partner' && $user->area_id && $user->area_id != $oldAreaId) {
            $synced = $this->syncMikrotikCustomers($user);
        }

        $message = 'User updated successfully.';
        if ($synced > 0) {
            $message .= " {$synced} new customers auto-imported from MikroTik.";
        }

        return redirect()->route('admin.users.index')->with('success', $message);
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User deleted.');
    }

    /**
     * Reset partner password (admin only).
     * POST /admin/users/{user}/reset-password
     */
    public function resetPassword(Request $request, User $user)
    {
        // Only admin can reset passwords
        if (auth()->user()->role !== 'admin') {
            return back()->with('error', 'Hanya admin yang bisa reset password pengguna internal.');
        }

        // If confirmation is left blank in the UI, treat it as same value.
        // This prevents "nothing happens" UX when browser blocks/validation fails.
        if (!$request->filled('new_password_confirmation')) {
            $request->merge([
                'new_password_confirmation' => (string) $request->input('new_password', ''),
            ]);
        }

        $validated = $request->validate([
            'new_password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user->update(['password' => Hash::make($validated['new_password'])]);

        // Revoke all existing API tokens so partner must re-login
        $user->tokens()->delete();

        return back()->with('success', "Password {$user->name} berhasil direset. Semua sesi login lama sudah dicabut.");
    }

    /**
     * Auto-import PPPoE secrets from partner's area MikroTik as Customer records.
     * Returns number of newly created customers.
     */
    private function syncMikrotikCustomers(User $partner): int
    {
        try {
            $area = Area::find($partner->area_id);
            if (!$area || empty($area->router_ip)) {
                return 0;
            }

            $mikrotik = MikroTikService::forArea($area);
            if (!$mikrotik->testConnection()['success']) {
                Log::warning("Auto-sync skipped: cannot connect to MikroTik for area {$area->name}");
                return 0;
            }

            $secretsResult = $mikrotik->getAllSecrets();
            if (!$secretsResult['success']) {
                return 0;
            }

            $created = 0;
            foreach ($secretsResult['data'] as $secret) {
                $username = $secret['name'] ?? null;
                if (!$username) continue;

                // PPPoE usernames can repeat across areas, so only skip same-area matches.
                if (Customer::forAreaPppoe($area->id, $username)->exists()) {
                    continue;
                }

                Customer::create([
                    'name'            => $secret['comment'] ?: $username,
                    'pppoe_user'      => $username,
                    'pppoe_pass'      => $secret['password'] ?? Str::random(12),
                    'portal_password' => Hash::make(Str::random(10)),
                    'area_id'         => $area->id,
                    'partner_id'      => $partner->id,
                    'status'          => 'active',
                    'package_price'   => 0,
                ]);
                $created++;
            }

            Log::info("Auto-synced {$created} customers for partner {$partner->name} (area: {$area->name})");
            return $created;
        } catch (\Throwable $e) {
            Log::error("Auto-sync failed for partner {$partner->id}: " . $e->getMessage());
            return 0;
        }
    }

    private function normalizeTelegramUsername(?string $username): ?string
    {
        if ($username === null) {
            return null;
        }

        $clean = trim($username);
        if ($clean === '') {
            return null;
        }

        $clean = ltrim($clean, '@');
        return strtolower($clean);
    }
}
