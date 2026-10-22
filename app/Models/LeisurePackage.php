<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Formule anniversaire / groupe / entreprise : prix forfaitaire (semaine ou week-end) pour un nombre de
 * participants inclus, supplément par participant, acompte en ligne pour bloquer la date, solde sur place.
 */
class LeisurePackage extends Model
{
    public const KINDS = ['birthday' => 'Anniversaire', 'group' => 'Groupe / sortie scolaire', 'corporate' => 'Entreprise / séminaire'];

    public const KIND_ICONS = ['birthday' => 'fa-cake-candles', 'group' => 'fa-people-group', 'corporate' => 'fa-briefcase'];

    protected $fillable = [
        'provider_id', 'name', 'slug', 'kind', 'description', 'includes', 'weekday_price_xof', 'weekend_price_xof',
        'included_participants', 'extra_participant_xof', 'min_participants', 'max_participants', 'duration_minutes',
        'deposit_xof', 'slot_times', 'leisure_resource_id', 'free_cancellation_days', 'images', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'weekday_price_xof' => 'integer', 'weekend_price_xof' => 'integer', 'included_participants' => 'integer',
            'extra_participant_xof' => 'integer', 'min_participants' => 'integer', 'max_participants' => 'integer',
            'duration_minutes' => 'integer', 'deposit_xof' => 'integer', 'slot_times' => 'array',
            'free_cancellation_days' => 'integer', 'images' => 'array', 'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(fn (LeisurePackage $p) => $p->slug ??= Str::slug($p->name).'-'.Str::lower(Str::random(5)));
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function resource(): BelongsTo
    {
        return $this->belongsTo(LeisureResource::class, 'leisure_resource_id');
    }

    /** @return array<int, string> */
    public function slots(): array
    {
        return collect($this->slot_times ?? [])->map(fn ($t) => TouristVisitType::normalizeTime((string) $t))->filter()->unique()->sort()->values()->all();
    }

    public function basePriceFor(Carbon $date): int
    {
        return $date->isWeekend() && $this->weekend_price_xof ? (int) $this->weekend_price_xof : (int) $this->weekday_price_xof;
    }

    public function priceFor(Carbon $date, int $participants): int
    {
        return $this->basePriceFor($date) + max(0, $participants - $this->included_participants) * (int) $this->extra_participant_xof;
    }

    public function kindLabel(): string
    {
        return __(self::KINDS[$this->kind] ?? $this->kind);
    }
}
