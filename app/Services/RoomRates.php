<?php

namespace App\Services;

use Carbon\CarbonImmutable;

/**
 * Tarif réel d'un séjour, nuit par nuit, à partir du JSON de la chambre :
 *  - price_xof : prix de base ;
 *  - weekend_price_xof : nuits du vendredi et du samedi ;
 *  - rate_periods : [{from, to, price_xof, label}] (dates incluses) — prioritaires sur le reste ;
 *  - long_stay_min_nights + long_stay_percent : remise long séjour ;
 *  - last_minute_days + last_minute_percent : remise si l'arrivée est dans les N jours.
 * Une seule remise s'applique : la plus avantageuse pour le client.
 */
class RoomRates
{
    /** Prix d'une nuit donnée (date d'arrivée de la nuit). */
    public function nightly(array $room, CarbonImmutable $night): int
    {
        $day = $night->format('Y-m-d');
        foreach ((array) ($room['rate_periods'] ?? []) as $p) {
            if (! empty($p['from']) && ! empty($p['to']) && (int) ($p['price_xof'] ?? 0) > 0 && $day >= $p['from'] && $day <= $p['to']) {
                return (int) $p['price_xof'];
            }
        }
        if ((int) ($room['weekend_price_xof'] ?? 0) > 0 && in_array($night->dayOfWeek, [5, 6], true)) {
            return (int) $room['weekend_price_xof'];
        }

        return (int) ($room['price_xof'] ?? 0);
    }

    /**
     * @return array{nights: int, subtotal: int, discount_xof: int, discount_label: ?string, total: int, variable: bool, per_night: array<string, int>}
     */
    public function quote(array $room, string $checkIn, string $checkOut, int $rooms = 1, ?CarbonImmutable $today = null): array
    {
        $in = CarbonImmutable::parse($checkIn)->startOfDay();
        $out = CarbonImmutable::parse($checkOut)->startOfDay();
        $rooms = max(1, $rooms);
        $perNight = [];
        for ($d = $in; $d < $out; $d = $d->addDay()) {
            $perNight[$d->format('Y-m-d')] = $this->nightly($room, $d);
        }
        $nights = count($perNight);
        $subtotal = array_sum($perNight) * $rooms;

        $today = ($today ?? CarbonImmutable::now())->startOfDay();
        $candidates = [];
        if ((int) ($room['long_stay_min_nights'] ?? 0) > 0 && (float) ($room['long_stay_percent'] ?? 0) > 0 && $nights >= (int) $room['long_stay_min_nights']) {
            $candidates[] = [(float) $room['long_stay_percent'], __('Long séjour (:n nuits et plus)', ['n' => (int) $room['long_stay_min_nights']])];
        }
        if ((int) ($room['last_minute_days'] ?? 0) > 0 && (float) ($room['last_minute_percent'] ?? 0) > 0 && $today->diffInDays($in, false) <= (int) $room['last_minute_days'] && $in >= $today) {
            $candidates[] = [(float) $room['last_minute_percent'], __('Dernière minute')];
        }
        usort($candidates, fn ($a, $b) => $b[0] <=> $a[0]);
        [$percent, $label] = $candidates[0] ?? [0.0, null];
        $discount = (int) round($subtotal * min(90, $percent) / 100);

        return [
            'nights' => $nights,
            'subtotal' => $subtotal,
            'discount_xof' => $discount,
            'discount_label' => $label ? $label.' −'.rtrim(rtrim(number_format($percent, 1, ',', ''), '0'), ',').' %' : null,
            'total' => max(0, $subtotal - $discount),
            'variable' => count(array_unique($perNight)) > 1,
            'per_night' => $perNight,
        ];
    }

    /** Badges de promotion à afficher sur la carte de la chambre. */
    public function badges(array $room): array
    {
        $b = [];
        if ((int) ($room['long_stay_min_nights'] ?? 0) > 0 && (float) ($room['long_stay_percent'] ?? 0) > 0) {
            $b[] = __('−:p % dès :n nuits', ['p' => rtrim(rtrim(number_format((float) $room['long_stay_percent'], 1, ',', ''), '0'), ','), 'n' => (int) $room['long_stay_min_nights']]);
        }
        if ((int) ($room['last_minute_days'] ?? 0) > 0 && (float) ($room['last_minute_percent'] ?? 0) > 0) {
            $b[] = __('Dernière minute −:p %', ['p' => rtrim(rtrim(number_format((float) $room['last_minute_percent'], 1, ',', ''), '0'), ',')]);
        }

        return $b;
    }
}
