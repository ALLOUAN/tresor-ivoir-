<?php

namespace App\Support;

/**
 * Liens Google Maps sans clé API (Maps URLs) : ouvrent l'application Google Maps
 * sur mobile ou le site sur ordinateur. L'origine est omise pour que Google
 * utilise la position actuelle du visiteur.
 *
 * @see https://developers.google.com/maps/documentation/urls/get-started
 */
class GoogleMaps
{
    public const TRAVEL_MODES = ['driving', 'walking', 'bicycling', 'transit'];

    /** Itinéraire de la position du visiteur jusqu'à la destination. */
    public static function directionsUrl(float|string $lat, float|string $lng, string $mode = 'driving'): string
    {
        return 'https://www.google.com/maps/dir/?'.http_build_query([
            'api' => 1,
            'destination' => self::point($lat, $lng),
            'travelmode' => in_array($mode, self::TRAVEL_MODES, true) ? $mode : 'driving',
        ]);
    }

    /**
     * Itinéraire à plusieurs étapes (planificateur « Mon séjour »).
     * Google limite les étapes intermédiaires à 9 dans une URL.
     *
     * @param  array<int, array{0: float|string, 1: float|string}>  $stops  [lat, lng] dans l'ordre de visite
     */
    public static function multiStopUrl(array $stops, string $mode = 'driving', bool $fromCurrentPosition = false): ?string
    {
        $points = array_map(fn ($s) => self::point($s[0], $s[1]), array_values($stops));

        if (count($points) < ($fromCurrentPosition ? 1 : 2)) {
            return null;
        }

        $params = ['api' => 1];
        if (! $fromCurrentPosition) {
            $params['origin'] = array_shift($points);
        }
        $params['destination'] = array_pop($points);
        if ($points !== []) {
            $params['waypoints'] = implode('|', array_slice($points, 0, 9));
        }
        $params['travelmode'] = in_array($mode, self::TRAVEL_MODES, true) ? $mode : 'driving';

        return 'https://www.google.com/maps/dir/?'.http_build_query($params);
    }

    private static function point(float|string $lat, float|string $lng): string
    {
        return ((float) $lat).','.((float) $lng);
    }
}
