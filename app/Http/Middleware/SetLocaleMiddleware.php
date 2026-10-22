<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Le site est en français par défaut, quelle que soit APP_LOCALE (souvent restée à « en ») :
        // l'anglais n'est servi qu'après un choix explicite via /lang/en.
        $locale = session('locale', 'fr');

        if (in_array($locale, ['fr', 'en'])) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}
