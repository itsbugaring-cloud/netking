<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureWebRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! auth()->check()) {
            return redirect()->route('admin.login');
        }

        $role = auth()->user()->role ?? null;

        if (! in_array($role, $roles, true)) {
            return redirect()->route('admin.dashboard')->with('error', 'Anda tidak memiliki akses ke menu tersebut.');
        }

        return $next($request);
    }
}
