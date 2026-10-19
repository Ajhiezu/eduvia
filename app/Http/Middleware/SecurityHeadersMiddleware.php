<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    /**
     * Handle an incoming request and enforce robust defense-in-depth security headers.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $requestId = (string) ($request->header('X-Request-ID') ?: Str::uuid());
        $request->headers->set('X-Request-ID', $requestId);
        Log::shareContext(['request_id' => $requestId]);

        $response = $next($request);

        $response->headers->set('X-Request-ID', $requestId);

        // Clickjacking defense
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // MIME-type sniffing defense
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Cross-Site Scripting (XSS) filter for legacy clients
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Referrer Information Leakage defense
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Restrict unnecessary browser device features
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // Content Security Policy (CSP) Defense-in-Depth
        $cspPolicy = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://fonts.bunny.net",
            "font-src 'self' https://fonts.gstatic.com https://fonts.bunny.net data:",
            "img-src 'self' data: https: blob:",
            "connect-src 'self' https://app.sandbox.midtrans.com https://api.sandbox.midtrans.com https://app.midtrans.com https://api.midtrans.com",
            "frame-src 'self' https://app.sandbox.midtrans.com https://app.midtrans.com",
            "object-src 'none'",
            "base-uri 'self'",
        ]);
        $response->headers->set('Content-Security-Policy', $cspPolicy);

        // Enforce HTTPS HSTS only in production environments
        if (app()->environment('production')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        } else {
            // Prevent Windows PHP CLI server keep-alive socket deadlocks
            $response->headers->set('Connection', 'close');
        }

        return $response;
    }
}
