<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PartnerMiddleware
{
    /**
     * Handle an incoming request.
     * Allows access for admin and partner roles only.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return redirect()->route('admin.login');
        }

        $role = auth()->user()->role ?? null;

        if (! in_array($role, ['admin', 'partner'], true)) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Anda tidak memiliki akses ke menu tersebut.');
        }

        return $next($request);
    }
}
