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

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(self), payment=()');
        // CSP volontairement limitée aux directives qui ne peuvent casser aucune page (pas de script-src : les pages
        // utilisent des scripts en ligne et plusieurs CDN). Interdit plugins, <base> détourné et affichage du site dans un cadre tiers.
        $response->headers->set('Content-Security-Policy', "object-src 'none'; base-uri 'self'; frame-ancestors 'self'");
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

        return $response;
    }
}
