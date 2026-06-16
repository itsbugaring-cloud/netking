<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Prevent clickjacking
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Prevent MIME type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Referrer Policy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // HTTP Strict Transport Security (1 year, include subdomains)
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');

        // Permissions Policy - disable unused browser features
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(), usb=()');

        // Content Security Policy — allow Cloudflare, CDNs, fonts used by the app
        $response->headers->set('Content-Security-Policy',
            "default-src 'self'; " .
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' " .
                "https://cdn.jsdelivr.net " .
                "https://unpkg.com " .
                "https://cdn.datatables.net " .
                "https://static.cloudflareinsights.com; " .
            "style-src 'self' 'unsafe-inline' " .
                "https://cdn.jsdelivr.net " .
                "https://unpkg.com " .
                "https://cdn.datatables.net " .
                "https://fonts.googleapis.com; " .
            "font-src 'self' " .
                "https://cdn.jsdelivr.net " .
                "https://fonts.gstatic.com " .
                "data:; " .
            "img-src 'self' data: blob: https:; " .
            "connect-src 'self' " .
                "https://a.nel.cloudflare.com " .
                "wss:; " .
            "frame-ancestors 'self'; " .
            "base-uri 'self'; " .
            "form-action 'self';"
        );

        // Remove fingerprinting headers
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        return $response;
    }
}
