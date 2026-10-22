<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Activité d'un lieu de loisirs (ou d'un site touristique). Fonctions « marché » : créneaux à places
 * limitées, ressources partagées, tarifs par catégorie et options, heures creuses / week-end,
 * conditions de participation, décharge, annulation gratuite et report.
 */
class Activity extends Model
{
    use SoftDeletes;

    public const LEVELS = ['debutant' => 'Débutant', 'intermediaire' => 'Intermédiaire', 'confirme' => 'Confirmé'];

    protected $fillable = [
        'provider_id', 'category_id', 'name', 'slug', 'description', 'price_xof',
        'duration_minutes', 'max_participants', 'images', 'is_available', 'is_featured', 'sort_order',
        'slot_times', 'slot_capacity', 'weekdays', 'instant_booking', 'price_tiers', 'addons',
        'weekend_adjust_percent', 'offpeak_until', 'offpeak_discount_percent', 'free_cancellation_hours',
        'min_age', 'min_height_cm', 'level', 'requirements', 'to_bring', 'requires_waiver', 'waiver_text',
    ];

    protected function casts(): array
    {
        return [
            'images' => 'array',
            'is_available' => 'boolean',
            'is_featured' => 'boolean',
            'price_xof' => 'integer',
            'duration_minutes' => 'integer',
            'max_participants' => 'integer',
            'slot_times' => 'array',
            'slot_capacity' => 'integer',
            'weekdays' => 'array',
            'instant_booking' => 'boolean',
            'price_tiers' => 'array',
            'addons' => 'array',
            'weekend_adjust_percent' => 'integer',
            'offpeak_discount_percent' => 'integer',
            'free_cancellation_hours' => 'integer',
            'min_age' => 'integer',
            'min_height_cm' => 'integer',
            'requires_waiver' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (Activity $a) {
            if (! $a->slug) {
                $a->forceFill(['slug' => Str::slug($a->name).'-'.$a->id])->saveQuietly();
            }
        });
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ActivityCategory::class, 'category_id');
    }

    public function resources(): BelongsToMany
    {
        return $this->belongsToMany(LeisureResource::class, 'activity_resource')->withPivot(['units', 'per_participant']);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(ActivityBooking::class);
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_available', true);
    }

    /** Lieu public de l'activité : lieu de loisirs, sinon site touristique du même prestataire. */
    public function place(): LeisureVenue|TouristExperience|null
    {
        return LeisureVenue::query()->where('provider_id', $this->provider_id)->where('is_active', true)->first()
            ?? TouristExperience::query()->where('provider_id', $this->provider_id)->where('is_active', true)->first();
    }

    public function publicUrl(): ?string
    {
        return $this->slug ? route('activity.show', $this->slug) : null;
    }

    /* ── Créneaux ── */

    /** @return array<int, string> */
    public function slots(): array
    {
        return collect($this->slot_times ?? [])
            ->map(fn ($t) => TouristVisitType::normalizeTime((string) $t))
            ->filter()->unique()->sort()->values()->all();
    }

    /** Réservation en ligne immédiate : seulement avec des créneaux définis. */
    public function bookableOnline(): bool
    {
        return $this->is_available && $this->instant_booking && $this->slots() !== [];
    }

    public function isOpenOn(Carbon $day): bool
    {
        $days = collect($this->weekdays ?? [])->map(fn ($d) => (int) $d)->filter();

        return $days->isEmpty() || $days->contains($day->isoWeekday());
    }

    /* ── Tarifs ── */

    /** @return array<int, array{label: string, price_xof: int}> */
    public function tiers(): array
    {
        $tiers = collect($this->price_tiers ?? [])
            ->filter(fn ($t) => is_array($t) && trim((string) ($t['label'] ?? '')) !== '' && is_numeric($t['price_xof'] ?? null))
            ->map(fn ($t) => ['label' => trim((string) $t['label']), 'price_xof' => max(0, (int) $t['price_xof'])])
            ->values()->all();

        return $tiers ?: [['label' => 'Participant', 'price_xof' => (int) $this->price_xof]];
    }

    public function fromPrice(): int
    {
        return (int) (collect($this->tiers())->pluck('price_xof')->filter(fn ($p) => $p > 0)->min() ?? 0);
    }

    /** @return array<int, array{label: string, price_xof: int, per: string}> */
    public function addonsList(): array
    {
        return collect($this->addons ?? [])
            ->filter(fn ($a) => is_array($a) && trim((string) ($a['label'] ?? '')) !== '')
            ->map(fn ($a) => ['label' => trim((string) $a['label']), 'price_xof' => max(0, (int) ($a['price_xof'] ?? 0)), 'per' => ($a['per'] ?? 'person') === 'booking' ? 'booking' : 'person'])
            ->values()->all();
    }

    public function adjustPercentFor(Carbon $date, ?string $time): int
    {
        $p = 0;
        if ($this->weekend_adjust_percent && $date->isWeekend()) {
            $p += (int) $this->weekend_adjust_percent;
        }
        if ($this->offpeak_discount_percent && $this->offpeak_until && $time && $time < $this->offpeak_until) {
            $p -= (int) $this->offpeak_discount_percent;
        }

        return max(-90, min(200, $p));
    }

    /**
     * Devis : quantités par catégorie, options choisies, ajustement de la date/heure.
     *
     * @param  array<int, int>  $quantities
     * @param  array<int, int|string>  $addonIndexes
     * @return array{participants: int, breakdown: array, addons: array, adjust: int, total: int}
     */
    public function quote(array $quantities, array $addonIndexes, Carbon $date, ?string $time): array
    {
        $adjust = $this->adjustPercentFor($date, $time);
        $tiers = $this->tiers();
        $breakdown = collect($tiers)->map(fn ($t, $i) => [
            'label' => $t['label'], 'qty' => max(0, (int) ($quantities[$i] ?? 0)), 'unit_price_xof' => TouristVisitType::applyPercent($t['price_xof'], $adjust),
        ])->filter(fn ($l) => $l['qty'] > 0)->values()->all();
        $participants = (int) collect($breakdown)->sum('qty');
        $offered = $this->addonsList();
        $addons = collect($addonIndexes)->map(fn ($i) => (int) $i)->unique()->filter(fn ($i) => isset($offered[$i]))
            ->map(fn ($i) => ['label' => $offered[$i]['label'], 'qty' => $offered[$i]['per'] === 'person' ? $participants : 1, 'unit_price_xof' => $offered[$i]['price_xof']])
            ->values()->all();
        $total = (int) (collect($breakdown)->sum(fn ($l) => $l['qty'] * $l['unit_price_xof']) + collect($addons)->sum(fn ($a) => $a['qty'] * $a['unit_price_xof']));

        return compact('participants', 'breakdown', 'addons', 'adjust', 'total');
    }

    /* ── Conditions ── */

    public static function lines(?string $text): array
    {
        return TouristVisitType::lines($text);
    }

    public function cancellationLabel(): ?string
    {
        if ($this->free_cancellation_hours === null) {
            return null;
        }

        return $this->free_cancellation_hours === 0
            ? __('Annulation et report gratuits jusqu\'au début')
            : __('Annulation et report gratuits jusqu\'à :h h avant', ['h' => $this->free_cancellation_hours]);
    }

    /** @return array<int, string> Conditions de participation affichées avant la réservation. */
    public function conditions(): array
    {
        return array_values(array_filter([
            $this->min_age ? __('À partir de :n ans', ['n' => $this->min_age]) : null,
            $this->min_height_cm ? __('Taille minimum : :n cm', ['n' => $this->min_height_cm]) : null,
            $this->level ? __('Niveau : :l', ['l' => __(self::LEVELS[$this->level] ?? $this->level)]) : null,
            ...self::lines($this->requirements),
        ]));
    }
}
