<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class PartnerAuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Accept partner role, and allow admin role for operational debug usage.
        $partner = User::where('email', $request->email)
            ->whereIn('role', ['partner', 'admin'])
            ->first();

        if (!$partner || !Hash::check($request->password, $partner->password)) {
            return response()->json(['message' => 'Email atau password salah'], 401);
        }

        // Delete old tokens to prevent token accumulation
        $partner->tokens()->delete();

        // Create new token with specific role ability
        $token = $partner->createToken('partner-app', ['role:partner'])->plainTextToken;

        return response()->json([
            'token' => $token,
            'partner' => [
                'id' => $partner->id,
                'name' => $partner->name,
                'email' => $partner->email,
                'role' => $partner->role,
                'area_id' => $partner->area_id,
                'area_name' => $partner->area?->name,
            ],
        ]);
    }
}
