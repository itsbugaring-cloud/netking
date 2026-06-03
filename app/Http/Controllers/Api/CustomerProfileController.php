<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Services\MikroTikService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;

class CustomerProfileController extends Controller
{
    public function index(Request $request)
    {
        $customer = $request->user()->load(['package', 'area', 'odp']);

        $this->resolvePackageFromRouterProfile($customer);

        return response()->json(['data' => $customer]);
    }

    private function resolvePackageFromRouterProfile($customer): void
    {
        $currentPackage = $customer->package;
        $hasValidPackage = $currentPackage && (float) ($currentPackage->price ?? 0) > 0;

        if ($hasValidPackage || !$customer->area || empty($customer->pppoe_user)) {
            return;
        }

        try {
            $mikrotik = MikroTikService::forArea($customer->area);
            $secrets = $mikrotik->getAllSecrets();

            if (($secrets['success'] ?? false) !== true || empty($secrets['data']) || !is_array($secrets['data'])) {
                return;
            }

            $secret = collect($secrets['data'])->first(function ($item) use ($customer) {
                $name = strtolower(trim((string) ($item['name'] ?? '')));
                return $name === strtolower(trim((string) $customer->pppoe_user));
            });

            if (!$secret) {
                return;
            }

            $profileName = trim((string) ($secret['profile'] ?? ''));
            if ($profileName === '') {
                return;
            }

            $package = Package::query()
                ->whereRaw('LOWER(TRIM(mikrotik_profile)) = ?', [mb_strtolower($profileName)])
                ->where(function ($query) use ($customer) {
                    $query->where('area_id', $customer->area_id)
                        ->orWhereNull('area_id');
                })
                ->orderByRaw('CASE WHEN area_id = ? THEN 0 ELSE 1 END', [$customer->area_id])
                ->first();

            if (!$package) {
                return;
            }

            $customer->setRelation('package', $package);

            if ((float) ($customer->package_price ?? 0) <= 0 && (float) ($package->price ?? 0) > 0) {
                $customer->package_price = $package->price;
            }
        } catch (\Throwable $e) {
            Log::warning('Customer profile package fallback failed', [
                'customer_id' => $customer->id,
                'pppoe_user' => $customer->pppoe_user,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $customer = $request->user();

        if (!Hash::check($request->current_password, $customer->portal_password)) {
            return response()->json([
                'errors' => ['current_password' => ['The provided password does not match your current password.']]
            ], 422);
        }

        $customer->update([
            'portal_password' => Hash::make($request->password)
        ]);

        return response()->json(['message' => 'Password updated successfully']);
    }

    public function updateContact(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
        ]);

        $customer = $request->user();

        // Phone might be unique, check if changed
        if ($customer->phone !== $request->phone) {
            $request->validate([
                'phone' => 'unique:customers,phone'
            ]);
        }

        $customer->update([
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        return response()->json([
            'message' => 'Profile updated successfully',
            'data' => $customer
        ]);
    }
}
