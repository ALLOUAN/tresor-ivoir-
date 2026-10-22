<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Calcul d'itinéraires via Google Routes API (computeRoutes).
 * Utilisé par le bouton « Afficher mon trajet » des fiches, et réutilisable
 * par le planificateur de séjour (étapes intermédiaires) et l'API mobile.
 *
 * @see https://developers.google.com/maps/documentation/routes/compute_route_directions
 */
class GoogleRoutesService
{
    private const ENDPOINT = 'https://routes.googleapis.com/directions/v2:computeRoutes';

    private const TRAVEL_MODES = [
        'driving' => 'DRIVE',
        'walking' => 'WALK',
        'bicycling' => 'BICYCLE',
        'two_wheeler' => 'TWO_WHEELER',
    ];

    public function isConfigured(): bool
    {
        return filled(config('services.google_maps.server_key'));
    }

    /**
     * @param  array{0: float, 1: float}  $origin  [lat, lng]
     * @param  array{0: float, 1: float}  $destination  [lat, lng]
     * @param  array<int, array{0: float, 1: float}>  $waypoints  étapes intermédiaires, dans l'ordre
     * @return array{distance_m: int, duration_s: int, distance_text: ?string, duration_text: ?string, polyline: string}|null
     */
    public function computeRoute(array $origin, array $destination, string $mode = 'driving', array $waypoints = [], string $language = 'fr'): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        $travelMode = self::TRAVEL_MODES[$mode] ?? 'DRIVE';

        // Origine arrondie à ~100 m : les visiteurs proches partagent le cache.
        $cacheKey = 'google-routes:'.md5(json_encode([
            round($origin[0], 3), round($origin[1], 3),
            $destination, $waypoints, $travelMode, $language,
        ]));

        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $body = [
            'origin' => $this->waypoint($origin),
            'destination' => $this->waypoint($destination),
            'travelMode' => $travelMode,
            'languageCode' => $language,
            'units' => 'METRIC',
        ];
        if ($waypoints !== []) {
            $body['intermediates'] = array_map(fn ($p) => $this->waypoint($p), $waypoints);
        }
        if ($travelMode === 'DRIVE' || $travelMode === 'TWO_WHEELER') {
            $body['routingPreference'] = 'TRAFFIC_AWARE';
        }

        try {
            $response = Http::timeout(8)
                ->withHeaders([
                    'X-Goog-Api-Key' => config('services.google_maps.server_key'),
                    'X-Goog-FieldMask' => 'routes.distanceMeters,routes.duration,routes.polyline.encodedPolyline,routes.localizedValues',
                ])
                ->post(self::ENDPOINT, $body);
        } catch (Throwable $e) {
            Log::warning('Google Routes API injoignable', ['error' => $e->getMessage()]);

            return null;
        }

        if ($response->failed()) {
            Log::warning('Google Routes API en erreur', ['status' => $response->status(), 'body' => $response->json('error.message')]);

            return null;
        }

        $route = $response->json('routes.0');
        if (! $route || empty($route['polyline']['encodedPolyline'])) {
            return null; // aucun trajet trouvé (ex. pas de route praticable)
        }

        $result = [
            'distance_m' => (int) ($route['distanceMeters'] ?? 0),
            'duration_s' => (int) rtrim($route['duration'] ?? '0s', 's'),
            'distance_text' => $route['localizedValues']['distance']['text'] ?? null,
            'duration_text' => $route['localizedValues']['duration']['text'] ?? null,
            'polyline' => $route['polyline']['encodedPolyline'],
        ];

        Cache::put($cacheKey, $result, now()->addMinutes(config('services.google_maps.route_cache_minutes', 10)));

        return $result;
    }

    /**
     * Journée multi-étapes : départ et retour à $home, Google réordonne les étapes pour minimiser
     * le temps de route (optimizeWaypointOrder, facturé en « ComputeRoutes-Advanced »).
     * Résultat mis en cache 7 jours : une journée de séjour ne coûte qu'un appel.
     *
     * @param  array<int, array{0: float, 1: float}>  $stops
     * @return array{order: int[], legs: array<int, array{distance_m: int, duration_s: int}>, distance_m: int, duration_s: int, polyline: string}|null
     */
    public function optimizeDay(array $home, array $stops, string $language = 'fr'): ?array
    {
        if (! $this->isConfigured() || $stops === [] || count($stops) > 25) {
            return null;
        }

        $cacheKey = 'google-routes-day:'.md5(json_encode([$home, $stops, $language]));
        if (($cached = Cache::get($cacheKey)) !== null) {
            return $cached;
        }

        $body = [
            'origin' => $this->waypoint($home),
            'destination' => $this->waypoint($home),
            'intermediates' => array_map(fn ($p) => $this->waypoint($p), $stops),
            'travelMode' => 'DRIVE',
            'routingPreference' => 'TRAFFIC_UNAWARE',
            'optimizeWaypointOrder' => count($stops) > 1,
            'languageCode' => $language,
            'units' => 'METRIC',
        ];

        try {
            $response = Http::timeout(20)
                ->withHeaders([
                    'X-Goog-Api-Key' => config('services.google_maps.server_key'),
                    'X-Goog-FieldMask' => 'routes.distanceMeters,routes.duration,routes.polyline.encodedPolyline,routes.legs.distanceMeters,routes.legs.duration,routes.optimizedIntermediateWaypointIndex',
                ])
                ->post(self::ENDPOINT, $body);
        } catch (Throwable $e) {
            Log::warning('Google Routes API (journée) injoignable', ['error' => $e->getMessage()]);

            return null;
        }

        $route = $response->successful() ? $response->json('routes.0') : null;
        if (! $route || empty($route['polyline']['encodedPolyline'])) {
            if ($response->failed()) {
                Log::warning('Google Routes API (journée) en erreur', ['status' => $response->status(), 'body' => $response->json('error.message')]);
            }

            return null;
        }

        $order = $route['optimizedIntermediateWaypointIndex'] ?? range(0, count($stops) - 1);
        if ($order === [] || in_array(-1, $order, true)) {
            $order = range(0, count($stops) - 1);
        }

        $result = [
            'order' => array_map('intval', $order),
            'legs' => array_map(fn ($l) => [
                'distance_m' => (int) ($l['distanceMeters'] ?? 0),
                'duration_s' => (int) rtrim($l['duration'] ?? '0s', 's'),
            ], $route['legs'] ?? []),
            'distance_m' => (int) ($route['distanceMeters'] ?? 0),
            'duration_s' => (int) rtrim($route['duration'] ?? '0s', 's'),
            'polyline' => $route['polyline']['encodedPolyline'],
        ];

        Cache::put($cacheKey, $result, now()->addDays(7));

        return $result;
    }

    private function waypoint(array $point): array
    {
        return ['location' => ['latLng' => ['latitude' => (float) $point[0], 'longitude' => (float) $point[1]]]];
    }
}
