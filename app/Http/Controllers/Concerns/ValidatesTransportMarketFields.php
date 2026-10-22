<?php

namespace App\Http\Controllers\Concerns;

use App\Models\TransportOffer;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/** Champs « marché » d'une offre de transport, partagés entre l'espace compagnie et l'administration. */
trait ValidatesTransportMarketFields
{
    /** @return array<string, mixed> */
    protected function transportMarketData(Request $request): array
    {
        $int = ['nullable', 'integer', 'min:0', 'max:100000000'];
        $d = $request->validate([
            'mode' => ['nullable', 'in:'.implode(',', array_keys(TransportOffer::MODES))],
            'vehicle_category' => ['nullable', 'in:'.implode(',', array_keys(TransportOffer::VEHICLE_CATEGORIES))],
            'max_luggage' => ['nullable', 'integer', 'min:0', 'max:60'],
            'zones' => ['nullable', 'array', 'max:30'], 'zones.*.label' => ['nullable', 'string', 'max:120'], 'zones.*.price_xof' => $int,
            'origin_label' => ['nullable', 'string', 'max:150'], 'round_trip_discount_percent' => ['nullable', 'integer', 'min:0', 'max:50'],
            'free_waiting_minutes' => ['nullable', 'integer', 'min:0', 'max:240'],
            'base_fare_xof' => $int, 'per_km_xof' => $int, 'per_hour_xof' => $int, 'minimum_xof' => $int, 'half_day_xof' => $int, 'full_day_xof' => $int,
            'included_km' => ['nullable', 'integer', 'min:0', 'max:5000'], 'weekly_daily_xof' => $int, 'deposit_xof' => $int, 'extra_km_xof' => $int,
            'min_driver_age' => ['nullable', 'integer', 'min:18', 'max:30'],
            'route_from' => ['nullable', 'string', 'max:120'], 'route_to' => ['nullable', 'string', 'max:120'], 'trip_minutes' => ['nullable', 'integer', 'min:5', 'max:2880'],
            'extras' => ['nullable', 'array', 'max:12'], 'extras.*.label' => ['nullable', 'string', 'max:80'], 'extras.*.price_xof' => $int, 'extras.*.per' => ['nullable', 'in:booking,day,passenger'],
            'night_surcharge_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
            'free_cancellation_hours' => ['nullable', 'integer', 'in:0,2,6,12,24,48,72'],
        ]);
        $rows = fn (string $k) => collect($d[$k] ?? [])->filter(fn ($r) => is_array($r) && trim((string) ($r['label'] ?? '')) !== '')->values();
        foreach (['zones', 'extras'] as $k) {
            if ($rows($k)->contains(fn ($r) => ($r['price_xof'] ?? null) === null || $r['price_xof'] === '')) {
                throw ValidationException::withMessages([$k => __('Indiquez le prix de chaque ligne.')]);
            }
        }
        $mode = $d['mode'] ?? null;
        if ($mode === 'transfer' && $rows('zones')->isEmpty()) {
            throw ValidationException::withMessages(['zones' => __('Un transfert à prix fixe a besoin d\'au moins une destination avec son prix.')]);
        }
        if ($mode === 'line' && (empty($d['route_from']) || empty($d['route_to']))) {
            throw ValidationException::withMessages(['route_from' => __('Indiquez le départ et l\'arrivée de la ligne.')]);
        }
        $keep = fn (string $k) => ($d[$k] ?? null) === '' ? null : ($d[$k] ?? null);

        return [
            'mode' => $mode, 'vehicle_category' => $keep('vehicle_category'), 'max_luggage' => $keep('max_luggage'),
            'zone_prices' => $rows('zones')->map(fn ($z) => ['label' => trim($z['label']), 'price_xof' => (int) $z['price_xof']])->all() ?: null,
            'origin_label' => $keep('origin_label'), 'round_trip_discount_percent' => $keep('round_trip_discount_percent'), 'free_waiting_minutes' => $keep('free_waiting_minutes'),
            'base_fare_xof' => $keep('base_fare_xof'), 'per_km_xof' => $keep('per_km_xof'), 'per_hour_xof' => $keep('per_hour_xof'), 'minimum_xof' => $keep('minimum_xof'),
            'half_day_xof' => $keep('half_day_xof'), 'full_day_xof' => $keep('full_day_xof'), 'included_km' => $keep('included_km'),
            'weekly_daily_xof' => $keep('weekly_daily_xof'), 'deposit_xof' => $keep('deposit_xof'), 'extra_km_xof' => $keep('extra_km_xof'), 'min_driver_age' => $keep('min_driver_age'),
            'route_from' => $keep('route_from'), 'route_to' => $keep('route_to'), 'trip_minutes' => $keep('trip_minutes'),
            'extras' => $rows('extras')->map(fn ($e) => ['label' => trim($e['label']), 'price_xof' => (int) $e['price_xof'], 'per' => in_array($e['per'] ?? '', ['day', 'passenger'], true) ? $e['per'] : 'booking'])->all() ?: null,
            'night_surcharge_percent' => $keep('night_surcharge_percent'), 'free_cancellation_hours' => $keep('free_cancellation_hours'),
            'instant_booking' => $request->has('instant_booking') ? $request->boolean('instant_booking') : true,
        ];
    }
}
