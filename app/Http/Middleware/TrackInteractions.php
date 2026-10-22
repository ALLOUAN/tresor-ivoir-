<?php

namespace App\Http\Middleware;

use App\Services\Ai\Analytics\ProviderAnalyticsCollector;
use App\Services\Ai\InteractionStore;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Enregistre la consultation d'une fiche publique (signal du filtrage collaboratif).
 * Sans effet si AI_TRACKING=false, si le navigateur envoie « Do Not Track », pour les robots,
 * le back-office et les pages qui ne sont pas des fiches. N'altère jamais la réponse.
 */
class TrackInteractions
{
    /** nom de route => [type de fiche, table, paramètre de route] */
    private const ROUTES = [
        'providers.show' => ['provider', 'providers', 'slug'],
        'accommodations.show' => ['accommodation', 'accommodations', 'accommodation'],
        'restaurant.show' => ['restaurant', 'restaurants', 'slug'],
        'tourist.site' => ['site', 'tourist_sites', 'slug'],
        'events.show' => ['event', 'events', 'slug'],
        'leisure.show' => ['leisure', 'leisure_venues', 'slug'],
        'tourist-experience.show' => ['experience', 'tourist_experiences', 'slug'],
        'travel-agency.show' => ['agency', 'travel_agencies', 'slug'],
        'transport-company.show' => ['transport', 'transport_companies', 'slug'],
    ];

    public function __construct(private readonly InteractionStore $store) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        try {
            $this->track($request, $response);
        } catch (\Throwable $e) {
            report($e);   // le suivi ne doit jamais casser une page
        }

        return $response;
    }

    private function track(Request $request, Response $response): void
    {
        $ai = config('ai.enabled', true) && config('ai.tracking', true);
        $analytics = (bool) config('ai.analytics.collect', true);
        if ((! $ai && ! $analytics)
            || ! $request->isMethod('GET') || $request->ajax() || $request->header('DNT') === '1'
            || $response->getStatusCode() !== 200
            || preg_match('/bot|crawl|spider|slurp|preview|facebookexternalhit|curl|wget|python|headless/i', (string) $request->userAgent())) {
            return;
        }
        $route = $request->route();
        $name = $route?->getName();
        if (! $name || ! isset(self::ROUTES[$name])) {
            return;
        }
        [$type, $table, $param] = self::ROUTES[$name];
        $value = $route->parameter($param);
        $id = is_object($value) ? $value->getKey() : DB::table($table)->where('slug', $value)->value('id');
        $actor = $this->store->actorKey($request);
        if (! $id || ! $actor) {
            return;
        }
        if ($ai) {
            $this->store->record($actor, $type.':'.$id, 'view');
        }

        // Statistiques du prestataire : une vue par visiteur et par demi-heure, indépendamment de l'IA.
        if ($analytics && Cache::add('pv.'.sha1($actor.'|'.$type.':'.$id), 1, 1800)) {
            $providerId = app(ProviderAnalyticsCollector::class)->providerIdFor($type, (int) $id);
            if ($providerId) {
                app(ProviderAnalyticsCollector::class)->recordView($providerId);
            }
        }
    }
}
