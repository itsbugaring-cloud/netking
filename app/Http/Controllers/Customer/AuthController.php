<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Customer;

class AuthController extends Controller
{
    /**
     * Show customer login form
     */
    public function showLogin()
    {
        return view('customer.auth.login');
    }

    /**
     * Handle customer login
     * Customers login using their PPPoE username or phone number and password.
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $identity = trim((string) $request->username);
        $customer = Customer::where('phone', $identity)->first();

        if (!$customer) {
            $matches = Customer::whereRaw('LOWER(TRIM(pppoe_user)) = ?', [mb_strtolower($identity)])->get();

            if ($matches->count() === 1) {
                $customer = $matches->first();
            } elseif ($matches->count() > 1) {
                return back()->withErrors([
                    'username' => 'Username PPPoE ini dipakai di beberapa area. Gunakan nomor HP untuk login.',
                ])->withInput();
            }
        }

        // Verify password (hashed portal password)
        if ($customer && Hash::check($request->password, $customer->portal_password)) {
            // Block non-functional accounts (provisioning/failed) but allow suspended to login & pay
            if (in_array($customer->status, ['failed', 'provisioning'])) {
                return back()->withErrors([
                    'username' => 'Your account is currently ' . $customer->status . '. Please contact support.'
                ])->withInput();
            }

            // Update last login
            $customer->update(['last_login_at' => now()]);

            // Login customer using custom guard
            Auth::guard('customer')->login($customer, $request->filled('remember'));

            $request->session()->regenerate();

            return redirect()->intended(route('customer.dashboard'));
        }

        return back()->withErrors([
            'username' => 'Invalid username or password.',
        ])->withInput();
    }

    /**
     * Handle customer logout
     */
    public function logout(Request $request)
    {
        Auth::guard('customer')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('customer.login');
    }
}

