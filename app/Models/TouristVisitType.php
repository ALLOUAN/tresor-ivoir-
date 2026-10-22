<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Type de visite proposé par un site touristique (nom, prix par participant, guide, durée…).
 * La visite groupée reste gérée par TouristVisitSession.
 *
 * Fonctions « marché » (billetteries de sites et musées, GetYourGuide, Viator) :
 *  - créneaux horaires à places limitées (slot_times + slot_capacity) ;
 *  - tarifs par catégorie de visiteur (price_tiers ; à défaut, un seul tarif price_xof) ;
 *  - annulation gratuite jusqu'à X heures avant, paiement sur place possible ;
 *  - fiche : langues, inclus / non inclus, point de rendez-vous, accessibilité, à prévoir ;
 *  - tarif week-end (+/− %) et heures creuses (− % avant une heure donnée) ;
 *  - options payantes par personne ou par réservation (addons).
 */
class TouristVisitType extends Model
{
    /** Icônes proposées dans les formulaires (Font Awesome). */
    public const ICONS = [
        'fa-person-walking' => 'Marcheur',
        'fa-user-tie' => 'Guide',
        'fa-people-group' => 'Groupe',
        'fa-moon' => 'Nocturne',
        'fa-crown' => 'VIP',
        'fa-camera' => 'Photo',
        'fa-graduation-cap' => 'Scolaire',
        'fa-child-reaching' => 'Famille',
        'fa-person-hiking' => 'Randonnée',
        'fa-ticket' => 'Billet',
    ];

    protected $fillable = [
        'tourist_experience_id', 'name', 'description', 'icon', 'price_xof', 'includes_guide',
        'guide_supplement_xof', 'duration_minutes', 'max_participants', 'sort_order', 'is_active',
        'price_tiers', 'slot_times', 'slot_capacity', 'free_cancellation_hours', 'allow_pay_on_site', 'languages',
        'included', 'not_included', 'meeting_point', 'accessibility', 'to_bring',
        'weekend_adjust_percent', 'offpeak_until', 'offpeak_discount_percent', 'addons',
    ];

    protected function casts(): array
    {
        return [
            'price_xof' => 'integer',
            'includes_guide' => 'boolean',
            'guide_supplement_xof' => 'integer',
            'duration_minutes' => 'integer',
            'max_participants' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
            'price_tiers' => 'array',
            'slot_times' => 'array',
            'slot_capacity' => 'integer',
            'free_cancellation_hours' => 'integer',
            'allow_pay_on_site' => 'boolean',
            'languages' => 'array',
            'weekend_adjust_percent' => 'integer',
            'offpeak_discount_percent' => 'integer',
            'addons' => 'array',
        ];
    }

    public function experience(): BelongsTo
    {
        return $this->belongsTo(TouristExperience::class, 'tourist_experience_id');
    }

    public function visits(): HasMany
    {
        return $this->hasMany(TouristVisit::class, 'tourist_visit_type_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /** Supplément guide réellement appliqué (seulement pour une visite guidée). */
    public function appliedGuideSupplement(): int
    {
        return $this->includes_guide ? (int) ($this->guide_supplement_xof ?? 0) : 0;
    }

    /** Catégories de tarif ; sans grille, un seul tarif « Participant » au prix de base. */
    public function tiers(): array
    {
        $tiers = collect($this->price_tiers ?? [])
            ->filter(fn ($t) => is_array($t) && trim((string) ($t['label'] ?? '')) !== '' && is_numeric($t['price_xof'] ?? null))
            ->map(fn ($t) => ['label' => trim((string) $t['label']), 'price_xof' => max(0, (int) $t['price_xof'])])
            ->values()->all();

        if ($tiers) {
            return $tiers;
        }

        // Sans grille propre : tarif de base, plus les tarifs enfant / résident de la fiche du site s'ils existent.
        $site = $this->experience;
        $extra = collect([
            ['label' => 'Enfant', 'price_xof' => $site?->visit_child_price_xof],
            ['label' => 'Résident', 'price_xof' => $site?->visit_resident_price_xof],
        ])->filter(fn ($t) => $t['price_xof'] !== null)->map(fn ($t) => ['label' => $t['label'], 'price_xof' => (int) $t['price_xof']]);

        return array_values(array_merge(
            [['label' => $extra->isEmpty() ? 'Participant' : 'Adulte', 'price_xof' => (int) ($this->price_xof ?? 0)]],
            $extra->all()
        ));
    }

    public function hasTiers(): bool
    {
        return count($this->tiers()) > 1;
    }

    /** Prix « à partir de » affiché sur la carte du type. */
    public function fromPrice(): int
    {
        $prices = collect($this->tiers())->pluck('price_xof');

        return (int) ($prices->filter(fn ($p) => $p > 0)->min() ?? 0);
    }

    /** @return array<int, array{label: string, price_xof: int, per: string}> */
    public function addonsList(): array
    {
        return collect($this->addons ?? [])
            ->filter(fn ($a) => is_array($a) && trim((string) ($a['label'] ?? '')) !== '')
            ->map(fn ($a) => ['label' => trim((string) $a['label']), 'price_xof' => max(0, (int) ($a['price_xof'] ?? 0)), 'per' => ($a['per'] ?? 'person') === 'booking' ? 'booking' : 'person'])
            ->values()->all();
    }

    /** @return array<int, string> Heures « HH:MM » triées. */
    public function slots(): array
    {
        return collect($this->slot_times ?? [])
            ->map(fn ($t) => self::normalizeTime((string) $t))
            ->filter()->unique()->sort()->values()->all();
    }

    public static function normalizeTime(string $time): ?string
    {
        return preg_match('/^([01]?\d|2[0-3])\s*[:hH]\s*([0-5]\d)?$/', trim($time), $m) ? sprintf('%02d:%02d', $m[1], $m[2] ?? 0) : null;
    }

    public function hasSlots(): bool
    {
        return $this->slots() !== [];
    }

    /** Places par créneau (ou par jour sans créneau) ; null = illimité. */
    public function capacity(): ?int
    {
        return $this->slot_capacity ?: null;
    }

    /** @return array<int, string> */
    public function languagesList(): array
    {
        return collect($this->languages ?? [])->map(fn ($l) => trim((string) $l))->filter()->unique()->values()->all();
    }

    /** Lignes d'un champ texte (une par ligne) : inclus, non inclus, à prévoir. */
    public static function lines(?string $text): array
    {
        return collect(preg_split('/\r\n|\r|\n/', (string) $text))->map(fn ($l) => trim(ltrim(trim($l), '-•*')))->filter()->values()->all();
    }

    public function hasDetails(): bool
    {
        return $this->languagesList() !== [] || $this->included || $this->not_included || $this->meeting_point || $this->accessibility || $this->to_bring;
    }

    public function cancellationLabel(): ?string
    {
        if ($this->free_cancellation_hours === null) {
            return null;
        }

        return $this->free_cancellation_hours === 0
            ? __('Annulation gratuite jusqu\'au début de la visite')
            : __('Annulation gratuite jusqu\'à :h h avant', ['h' => $this->free_cancellation_hours]);
    }

    /** Ajustement de prix (en %) pour une date et une heure : week-end, puis heures creuses. */
    public function adjustPercentFor(Carbon $date, ?string $time = null): int
    {
        $percent = 0;
        if ($this->weekend_adjust_percent && $date->isWeekend()) {
            $percent += (int) $this->weekend_adjust_percent;
        }
        if ($this->offpeak_discount_percent && $this->offpeak_until && $time && $time < $this->offpeak_until) {
            $percent -= (int) $this->offpeak_discount_percent;
        }

        return max(-90, min(200, $percent));
    }

    public static function applyPercent(int $amount, int $percent): int
    {
        return $percent === 0 ? $amount : (int) round($amount * (100 + $percent) / 100);
    }

    /** Places déjà prises : visites confirmées, à régler sur place, ou en cours de paiement (30 min). */
    public function bookedSeats(string $date, ?string $time = null): int
    {
        return (int) $this->visits()
            ->whereDate('desired_date', $date)
            ->when($time, fn ($q) => $q->where('visit_time', $time))
            ->where(fn ($q) => $q->whereIn('status', [TouristVisit::STATUS_PAID, TouristVisit::STATUS_BOOKED])
                ->orWhere(fn ($q2) => $q2->where('status', TouristVisit::STATUS_PENDING_PAYMENT)->where('created_at', '>=', now()->subMinutes(30))))
            ->sum('participants_count');
    }

    /**
     * Créneaux d'une date : places restantes, disponibilité et ajustement de prix.
     * Sans créneau défini, une seule entrée « journée » (time = null).
     *
     * @return array<int, array{time: ?string, remaining: ?int, available: bool, past: bool, adjust_percent: int}>
     */
    public function availabilityFor(string $date): array
    {
        $day = Carbon::parse($date, 'Africa/Abidjan')->startOfDay();
        $now = now('Africa/Abidjan');
        $capacity = $this->capacity();
        $times = $this->hasSlots() ? $this->slots() : [null];

        return collect($times)->map(function (?string $time) use ($day, $now, $capacity, $date) {
            $remaining = $capacity !== null ? max(0, $capacity - $this->bookedSeats($date, $time)) : null;
            $past = $time ? $day->copy()->setTimeFromTimeString($time)->lte($now) : $day->lt($now->copy()->startOfDay());

            return [
                'time' => $time,
                'remaining' => $remaining,
                'available' => ! $past && ($remaining === null || $remaining > 0),
                'past' => $past,
                'adjust_percent' => $this->adjustPercentFor($day, $time),
            ];
        })->all();
    }

    public function iconClass(): string
    {
        return $this->icon && array_key_exists($this->icon, self::ICONS) ? $this->icon : ($this->includes_guide ? 'fa-user-tie' : 'fa-ticket');
    }
}
