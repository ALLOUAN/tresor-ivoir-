<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Activity;
use App\Models\TouristVisitType;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/** Champs « marché » d'une activité, partagés entre l'espace prestataire et l'administration. */
trait ValidatesActivityMarketFields
{
    /** @return array<string, mixed> */
    protected function activityMarketData(Request $request): array
    {
        $data = $request->validate([
            'slot_times_text' => ['nullable', 'string', 'max:600'],
            'slot_capacity' => ['nullable', 'integer', 'min:1', 'max:5000'],
            'weekdays' => ['nullable', 'array'], 'weekdays.*' => ['integer', 'between:1,7'],
            'tiers' => ['nullable', 'array', 'max:8'], 'tiers.*.label' => ['nullable', 'string', 'max:60'], 'tiers.*.price_xof' => ['nullable', 'integer', 'min:0'],
            'addons' => ['nullable', 'array', 'max:12'], 'addons.*.label' => ['nullable', 'string', 'max:80'], 'addons.*.price_xof' => ['nullable', 'integer', 'min:0'], 'addons.*.per' => ['nullable', 'in:person,booking'],
            'weekend_adjust_percent' => ['nullable', 'integer', 'min:-50', 'max:100'],
            'offpeak_until' => ['nullable', 'date_format:H:i'], 'offpeak_discount_percent' => ['nullable', 'integer', 'min:1', 'max:90'],
            'free_cancellation_hours' => ['nullable', 'integer', 'in:0,2,6,12,24,48,72'],
            'min_age' => ['nullable', 'integer', 'min:1', 'max:99'], 'min_height_cm' => ['nullable', 'integer', 'min:50', 'max:220'],
            'level' => ['nullable', 'in:'.implode(',', array_keys(Activity::LEVELS))],
            'requirements' => ['nullable', 'string', 'max:2000'], 'to_bring' => ['nullable', 'string', 'max:2000'],
            'waiver_text' => ['nullable', 'string', 'max:5000'],
        ]);
        $rows = fn (string $k) => collect($data[$k] ?? [])->filter(fn ($r) => is_array($r) && trim((string) ($r['label'] ?? '')) !== '')->values();
        foreach (['tiers', 'addons'] as $k) {
            if ($rows($k)->contains(fn ($r) => ($r['price_xof'] ?? null) === null || $r['price_xof'] === '')) {
                throw ValidationException::withMessages([$k => __('Indiquez le prix de chaque ligne (0 pour gratuit).')]);
            }
        }
        $tokens = collect(preg_split('/[\s,;]+/', (string) ($data['slot_times_text'] ?? '')))->filter();
        if ($tokens->contains(fn ($t) => ! TouristVisitType::normalizeTime($t))) {
            throw ValidationException::withMessages(['slot_times_text' => __('Créneau invalide (format attendu 09:00).')]);
        }
        $requiresWaiver = $request->boolean('requires_waiver');
        if ($requiresWaiver && trim((string) ($data['waiver_text'] ?? '')) === '') {
            throw ValidationException::withMessages(['waiver_text' => __('Rédigez le texte de la décharge que les participants accepteront.')]);
        }
        $weekdays = collect($data['weekdays'] ?? [])->map(fn ($d) => (int) $d)->unique()->sort()->values()->all();

        return [
            'slot_times' => $tokens->map(fn ($t) => TouristVisitType::normalizeTime($t))->unique()->sort()->values()->all() ?: null,
            'slot_capacity' => $data['slot_capacity'] ?? null,
            'weekdays' => count($weekdays) === 7 || $weekdays === [] ? null : $weekdays,
            'instant_booking' => $request->has('instant_booking') ? $request->boolean('instant_booking') : true,
            'price_tiers' => $rows('tiers')->map(fn ($r) => ['label' => trim($r['label']), 'price_xof' => (int) $r['price_xof']])->all() ?: null,
            'addons' => $rows('addons')->map(fn ($r) => ['label' => trim($r['label']), 'price_xof' => (int) $r['price_xof'], 'per' => ($r['per'] ?? 'person') === 'booking' ? 'booking' : 'person'])->all() ?: null,
            'weekend_adjust_percent' => ($data['weekend_adjust_percent'] ?? 0) ?: null,
            'offpeak_until' => ($data['offpeak_until'] ?? null) && ($data['offpeak_discount_percent'] ?? null) ? $data['offpeak_until'] : null,
            'offpeak_discount_percent' => ($data['offpeak_until'] ?? null) && ($data['offpeak_discount_percent'] ?? null) ? $data['offpeak_discount_percent'] : null,
            'free_cancellation_hours' => $data['free_cancellation_hours'] ?? null,
            'min_age' => $data['min_age'] ?? null, 'min_height_cm' => $data['min_height_cm'] ?? null, 'level' => $data['level'] ?? null,
            'requirements' => $data['requirements'] ?? null, 'to_bring' => $data['to_bring'] ?? null,
            'requires_waiver' => $requiresWaiver, 'waiver_text' => $data['waiver_text'] ?? null,
        ];
    }
}
