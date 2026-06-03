<?php

namespace App\Http\Middleware;

use App\Models\Customer;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Strict role-based authorization for API routes.
 *
 * Replaces the insecure `ability:role:admin` Sanctum middleware which
 * defaults to allowing all abilities when tokens are issued without
 * explicit scopes (effectively bypassing the check for SPA/session auth).
 *
 * Usage in routes: ->middleware('api.role:admin')
 *                  ->middleware('api.role:customer')
 *                  ->middleware('api.role:partner')
 */
class EnsureApiRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        // Customer API tokens are issued by Customer model, which does not
        // have a `role` column. Use model type check for customer guard.
        if ($role === 'customer' && $user instanceof Customer) {
            return $next($request);
        }

        // Allow admin to access partner API for debug/ops use case.
        if ($role === 'partner' && isset($user->role) && in_array($user->role, ['partner', 'admin'], true)) {
            return $next($request);
        }

        if (! isset($user->role) || $user->role !== $role) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        return $next($request);
    }
}
