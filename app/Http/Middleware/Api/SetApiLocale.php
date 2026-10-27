<?php

namespace App\Http\Middleware\Api;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Langue des réponses : en-tête Accept-Language envoyé par l'application (fr par défaut, en). */
class SetApiLocale
{
    public const SUPPORTED = ['fr', 'en'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->getPreferredLanguage(self::SUPPORTED) ?? 'fr';
        app()->setLocale(in_array($locale, self::SUPPORTED, true) ? $locale : 'fr');

        $response = $next($request);
        $response->headers->set('Content-Language', app()->getLocale());

        return $response;
    }
}
