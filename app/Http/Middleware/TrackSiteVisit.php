<?php

namespace App\Http\Middleware;

use App\Services\SiteAudience;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Compte les visiteurs du site (pages publiques et espace client) pour le tableau de bord admin.
 * Ignorés : robots, requêtes AJAX / JSON, réponses non HTML ou en erreur, back-offices (admin, éditeur,
 * prestataire) et comptes administrateur / éditeur. N'altère jamais la réponse.
 */
class TrackSiteVisit
{
    private const BOTS = '/bot|crawl|spider|slurp|preview|facebookexternalhit|whatsapp|curl|wget|python|headless|lighthouse|monitor|uptime/i';

    private const EXCLUDED_PATHS = ['admin', 'admin/*', 'editor', 'editor/*', 'provider', 'provider/*', 'up', 'img/*', 'storage/*', 'livewire/*'];

    public function __construct(private readonly SiteAudience $audience) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        try {
            if ($this->shouldTrack($request, $response)) {
                $this->audience->record($request);
            }
        } catch (\Throwable $e) {
            report($e);   // le comptage ne doit jamais casser une page
        }

        return $response;
    }

    private function shouldTrack(Request $request, Response $response): bool
    {
        if (! $request->isMethod('GET') || $request->ajax() || $request->expectsJson() || $request->header('Purpose') === 'prefetch') {
            return false;
        }
        if ($response->getStatusCode() !== 200 || ! str_contains((string) $response->headers->get('Content-Type'), 'text/html')) {
            return false;
        }
        $ua = (string) $request->userAgent();
        if ($ua === '' || preg_match(self::BOTS, $ua)) {
            return false;
        }
        if ($request->is(...self::EXCLUDED_PATHS)) {
            return false;
        }

        return ! in_array($request->user()?->role, ['admin', 'editor'], true);
    }
}
