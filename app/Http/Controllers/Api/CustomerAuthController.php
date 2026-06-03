<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class CustomerAuthController extends Controller
{
    /**
     * Customer login for mobile app.
     *
     * Identifier: phone number only
     * Password  : portal_password (bcrypt)
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'nullable|string',
            'phone' => 'nullable|string',
            'password' => 'required|string',
        ]);

        $identity = trim((string) ($request->input('phone') ?: $request->input('username')));
        $password = $request->password;

        if ($identity === '') {
            throw ValidationException::withMessages([
                'phone' => ['Nomor HP wajib diisi.'],
            ]);
        }

        $customer = Customer::where('phone', $identity)->first();

        if (!$customer) {
            throw ValidationException::withMessages([
                'phone' => ['Nomor HP atau password akun salah.'],
            ]);
        }

        // Block accounts that aren't usable yet
        if (in_array($customer->status, ['failed', 'provisioning'])) {
            throw ValidationException::withMessages([
                'phone' => ['Akun Anda masih dalam proses. Silakan tunggu aktivasi akun.'],
            ]);
        }

        if (!$customer->portal_password) {
            throw ValidationException::withMessages([
                'phone' => ['Akun Anda belum diaktifkan. Silakan ajukan reset password akun.'],
            ]);
        }

        if (!Hash::check($password, $customer->portal_password)) {
            throw ValidationException::withMessages([
                'phone' => ['Nomor HP atau password akun salah.'],
            ]);
        }

        // Revoke old tokens and issue a fresh one
        $customer->tokens()->delete();
        $token = $customer->createToken('customer-mobile-app', ['role:customer'])->plainTextToken;

        // Update last login
        $customer->update(['last_login_at' => now()]);

        return response()->json([
            'success'  => true,
            'token'    => $token,
            'customer' => [
                'id'         => $customer->id,
                'name'       => $customer->name,
                'phone'      => $customer->phone,
                'status'     => $customer->status,
                'package_id' => $customer->package_id,
            ],
        ]);
    }
}
