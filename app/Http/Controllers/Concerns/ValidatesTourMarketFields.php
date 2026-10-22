<?php

namespace App\Http\Controllers\Concerns;

use App\Models\TourPackage;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Champs « marché » d'un circuit, partagés entre l'espace agence et l'administration :
 * programme jour par jour, inclus / non inclus, prise en charge, tarifs, acompte, minimum, annulation.
 */
trait ValidatesTourMarketFields
{
    /** @return array<string, mixed> */
    protected function tourMarketData(Request $request): array
    {
        $data = $request->validate([
            'summary' => ['nullable', 'string', 'max:300'],
            'itinerary' => ['nullable', 'array', 'max:60'],
            'itinerary.*.title' => ['nullable', 'string', 'max:150'],
            'itinerary.*.description' => ['nullable', 'string', 'max:2000'],
            'itinerary.*.meals' => ['nullable', 'string', 'max:120'],
            'itinerary.*.accommodation' => ['nullable', 'string', 'max:150'],
            'included' => ['nullable', 'string', 'max:2000'],
            'not_included' => ['nullable', 'string', 'max:2000'],
            'pickup_points' => ['nullable', 'array', 'max:15'],
            'pickup_points.*.label' => ['nullable', 'string', 'max:150'],
            'pickup_points.*.time' => ['nullable', 'string', 'max:10'],
            'pickup_points.*.note' => ['nullable', 'string', 'max:200'],
            'child_price_xof' => ['nullable', 'integer', 'min:0', 'max:100000000'],
            'child_age_max' => ['nullable', 'integer', 'min:1', 'max:17'],
            'single_supplement_xof' => ['nullable', 'integer', 'min:0', 'max:100000000'],
            'group_prices' => ['nullable', 'array', 'max:8'],
            'group_prices.*.min' => ['nullable', 'integer', 'min:1', 'max:500'],
            'group_prices.*.price_xof' => ['nullable', 'integer', 'min:0', 'max:100000000'],
            'extras' => ['nullable', 'array', 'max:12'],
            'extras.*.label' => ['nullable', 'string', 'max:100'],
            'extras.*.price_xof' => ['nullable', 'integer', 'min:0', 'max:100000000'],
            'extras.*.per' => ['nullable', 'in:person,booking'],
            'min_participants' => ['nullable', 'integer', 'min:1', 'max:500'],
            'guarantee_days' => ['nullable', 'integer', 'min:1', 'max:60'],
            'deposit_percent' => ['nullable', 'integer', 'min:10', 'max:90'],
            'balance_due_days' => ['nullable', 'integer', 'min:1', 'max:120'],
            'cancellation_policy' => ['nullable', 'array', 'max:6'],
            'cancellation_policy.*.days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'cancellation_policy.*.percent' => ['nullable', 'integer', 'min:0', 'max:100'],
            'difficulty' => ['nullable', 'in:'.implode(',', array_keys(TourPackage::DIFFICULTIES))],
            'languages_text' => ['nullable', 'string', 'max:200'],
        ]);

        $rows = fn (string $key, string $required) => collect($data[$key] ?? [])
            ->filter(fn ($r) => is_array($r) && trim((string) ($r[$required] ?? '')) !== '')->values();

        foreach (['group_prices' => __('palier'), 'extras' => __('option')] as $key => $what) {
            if ($rows($key, $key === 'group_prices' ? 'min' : 'label')->contains(fn ($r) => ($r['price_xof'] ?? null) === null || $r['price_xof'] === '')) {
                throw ValidationException::withMessages([$key => __('Indiquez le prix de chaque :what.', ['what' => $what])]);
            }
        }
        if ($rows('cancellation_policy', 'days')->contains(fn ($r) => ($r['percent'] ?? null) === null || $r['percent'] === '')) {
            throw ValidationException::withMessages(['cancellation_policy' => __('Indiquez le pourcentage remboursé pour chaque ligne du barème.')]);
        }
        if (($data['deposit_percent'] ?? null) && ! ($data['balance_due_days'] ?? null)) {
            $data['balance_due_days'] = 15;
        }

        return [
            'summary' => $data['summary'] ?? null,
            'itinerary' => $rows('itinerary', 'title')->map(fn ($d) => collect($d)->only(['title', 'description', 'meals', 'accommodation'])->map(fn ($v) => trim((string) $v))->all())->all() ?: null,
            'included' => $data['included'] ?? null,
            'not_included' => $data['not_included'] ?? null,
            'pickup_points' => $rows('pickup_points', 'label')->map(fn ($p) => ['label' => trim($p['label']), 'time' => trim((string) ($p['time'] ?? '')), 'note' => trim((string) ($p['note'] ?? ''))])->all() ?: null,
            'child_price_xof' => $data['child_price_xof'] ?? null,
            'child_age_max' => $data['child_age_max'] ?? null,
            'single_supplement_xof' => ($data['single_supplement_xof'] ?? null) ?: null,
            'group_prices' => $rows('group_prices', 'min')->map(fn ($g) => ['min' => (int) $g['min'], 'price_xof' => (int) $g['price_xof']])->sortBy('min')->unique('min')->values()->all() ?: null,
            'extras' => $rows('extras', 'label')->map(fn ($e) => ['label' => trim($e['label']), 'price_xof' => (int) $e['price_xof'], 'per' => ($e['per'] ?? 'person') === 'booking' ? 'booking' : 'person'])->all() ?: null,
            'min_participants' => $data['min_participants'] ?? null,
            'guarantee_days' => $data['guarantee_days'] ?? 7,
            'deposit_percent' => $data['deposit_percent'] ?? null,
            'balance_due_days' => $data['balance_due_days'] ?? 15,
            'cancellation_policy' => $rows('cancellation_policy', 'days')->map(fn ($r) => ['days' => (int) $r['days'], 'percent' => (int) $r['percent']])->sortByDesc('days')->unique('days')->values()->all() ?: null,
            'instant_booking' => $request->has('instant_booking') ? $request->boolean('instant_booking') : true,
            'difficulty' => $data['difficulty'] ?? null,
            'languages' => collect(preg_split('/\s*,\s*/', (string) ($data['languages_text'] ?? '')))->map(fn ($l) => trim($l))->filter()->unique()->values()->all() ?: null,
        ];
    }
}
