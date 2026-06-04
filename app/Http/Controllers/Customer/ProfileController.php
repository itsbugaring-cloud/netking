<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /**
     * Show customer profile
     */
    public function index()
    {
        $customer = auth('customer')->user();

        return view('customer.profile.index', compact('customer'));
    }

    /**
     * Update customer password
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        $customer = auth('customer')->user();

        // Verify current portal password
        if (!Hash::check($request->current_password, $customer->portal_password)) {
            return back()->withErrors([
                'current_password' => 'Current password is incorrect'
            ]);
        }

        // Update portal password
        $customer->update([
            'portal_password' => Hash::make($request->new_password)
        ]);

        return back()->with('success', 'Password updated successfully');
    }

    /**
     * Update customer contact info
     */
    public function updateContact(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:15',
            'address' => 'required|string|max:500',
        ]);

        $customer = auth('customer')->user();

        $customer->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        return back()->with('success', 'Contact information updated successfully');
    }
}
