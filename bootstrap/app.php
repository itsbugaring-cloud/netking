<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\TokenMismatchException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Security headers applied to all web responses
        $middleware->web(append: [
            \App\Http\Middleware\SecurityHeaders::class,
        ]);

        // Redirect unauthenticated users to admin login (fixes Route[login] not defined)
        $middleware->redirectGuestsTo(fn() => route('admin.login'));


        $middleware->alias([
            'admin'    => \App\Http\Middleware\AdminMiddleware::class,
            'partner'  => \App\Http\Middleware\PartnerMiddleware::class,
            'role'     => \App\Http\Middleware\EnsureWebRole::class,
            'guest'    => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'abilities' => \Laravel\Sanctum\Http\Middleware\CheckForAnyAbility::class,
            'ability'  => \Laravel\Sanctum\Http\Middleware\CheckAbilities::class,
            // Strict role middleware for API routes (replaces insecure ability:role:* pattern)
            'api.role' => \App\Http\Middleware\EnsureApiRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // 419 - Session expired / CSRF mismatch → redirect to login with message
        $exceptions->render(function (TokenMismatchException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Session expired. Please refresh and try again.'], 419);
            }
            return redirect()
                ->route('admin.login')
                ->withErrors(['session' => 'Sesi Anda sudah habis. Silakan login kembali.']);
        });
    })->create();
