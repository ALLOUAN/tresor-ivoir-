<?php

namespace App\Services\Ai;

final class Geo
{
    /** Distance à vol d'oiseau (haversine), en km. */
    public static function km(?float $lat1, ?float $lng1, ?float $lat2, ?float $lng2): ?float
    {
        if ($lat1 === null || $lng1 === null || $lat2 === null || $lng2 === null) {
            return null;
        }
        $r = 6371.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $r * 2 * asin(min(1.0, sqrt($a)));
    }

    /** Distance par la route estimée : l'à vol d'oiseau majorée de 30 %. */
    public static function roadKm(?float $lat1, ?float $lng1, ?float $lat2, ?float $lng2): ?float
    {
        $d = self::km($lat1, $lng1, $lat2, $lng2);

        return $d === null ? null : $d * 1.3;
    }
}
