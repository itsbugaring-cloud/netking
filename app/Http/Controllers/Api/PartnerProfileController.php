<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Olt;
use App\Models\Customer;
use Illuminate\Support\Facades\Hash;

class PartnerProfileController extends Controller
{
    public function index(Request $request)
    {
        $partner = $request->user();
        $partner->load('area');

        $olt = Olt::where('area_id', $partner->area_id)->first();

        return response()->json([
            'data' => [
                'id' => $partner->id,
                'name' => $partner->name,
                'email' => $partner->email,
                'phone' => $partner->phone ?? null,
                'area' => $partner->area?->name,
                'customer_count' => Customer::where('area_id', $partner->area_id)->count(),
                'olt_ip' => $olt?->ip_address,
                'status' => 'active',
            ],
        ]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $partner = $request->user();

        if (!Hash::check($request->current_password, $partner->password)) {
            return response()->json(['message' => 'Password lama salah'], 422);
        }

        $partner->update(['password' => Hash::make($request->password)]);

        return response()->json(['message' => 'Password berhasil diubah']);
    }
}
