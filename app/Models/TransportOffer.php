<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Offre d'une compagnie de transport. Le `mode` décide de la tarification et du parcours de réservation :
 *  - transfer : prix fixe par zone depuis un point d'origine (aéroport), numéro de vol, attente gratuite ;
 *  - chauffeur : devis prise en charge + km + heure (minimum) ou forfait demi-journée / journée ;
 *  - rental : location par jour (tarif semaine), caution, km inclus, documents, état des lieux ;
 *  - line : ligne régulière à départs programmés, choix du siège, billet QR.
 * Sans mode, l'offre reste « sur demande » (ancien fonctionnement).
 */
class TransportOffer extends Model
{
    use SoftDeletes;

    public const MODES = [
        'transfer' => 'Transfert à prix fixe',
        'chauffeur' => 'Chauffeur privé (devis instantané)',
        'rental' => 'Location de véhicule',
        'line' => 'Ligne régulière / navette',
    ];

    public const MODE_ICONS = ['transfer' => 'fa-plane-arrival', 'chauffeur' => 'fa-user-tie', 'rental' => 'fa-car', 'line' => 'fa-bus'];

    public const VEHICLE_CATEGORIES = ['berline' => 'Berline', 'suv' => 'SUV', '4x4' => '4×4', 'van' => 'Van (7-8 places)', 'minibus' => 'Minibus', 'bus' => 'Car / bus'];

    protected $fillable = [
        'provider_id', 'category_id', 'name', 'slug', 'description', 'price_xof',
        'max_passengers', 'images', 'is_available', 'is_featured', 'sort_order',
        'mode', 'vehicle_category', 'max_luggage', 'zone_prices', 'origin_label', 'round_trip_discount_percent',
        'free_waiting_minutes', 'base_fare_xof', 'per_km_xof', 'per_hour_xof', 'minimum_xof', 'half_day_xof',
        'full_day_xof', 'included_km', 'weekly_daily_xof', 'deposit_xof', 'extra_km_xof', 'min_driver_age',
        'route_from', 'route_to', 'trip_minutes', 'extras', 'night_surcharge_percent', 'free_cancellation_hours', 'instant_booking',
    ];

    protected function casts(): array
    {
        return [
            'images' => 'array', 'is_available' => 'boolean', 'is_featured' => 'boolean', 'price_xof' => 'integer',
            'max_passengers' => 'integer', 'max_luggage' => 'integer', 'zone_prices' => 'array', 'extras' => 'array',
            'round_trip_discount_percent' => 'integer', 'free_waiting_minutes' => 'integer', 'base_fare_xof' => 'integer',
            'per_km_xof' => 'integer', 'per_hour_xof' => 'integer', 'minimum_xof' => 'integer', 'half_day_xof' => 'integer',
            'full_day_xof' => 'integer', 'included_km' => 'integer', 'weekly_daily_xof' => 'integer', 'deposit_xof' => 'integer',
            'extra_km_xof' => 'integer', 'min_driver_age' => 'integer', 'trip_minutes' => 'integer',
            'night_surcharge_percent' => 'integer', 'free_cancellation_hours' => 'integer', 'instant_booking' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (TransportOffer $o) {
            if (! $o->slug) {
                $o->forceFill(['slug' => Str::slug($o->name).'-'.$o->id])->saveQuietly();
            }
        });
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TransportCategory::class, 'category_id');
    }

    public function trips(): HasMany
    {
        return $this->hasMany(TransportTrip::class)->orderBy('departs_at');
    }

    public function upcomingTrips()
    {
        return $this->trips()->where('is_active', true)->where('departs_at', '>', now());
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_available', true);
    }

    public function company(): ?TransportCompany
    {
        return TransportCompany::where('provider_id', $this->provider_id)->where('is_active', true)->first();
    }

    public function publicUrl(): ?string
    {
        return $this->slug ? route('transport-offer.show', $this->slug) : null;
    }

    public function bookableOnline(): bool
    {
        return $this->is_available && $this->instant_booking && $this->mode !== null && match ($this->mode) {
            'transfer' => $this->zones() !== [],
            'chauffeur' => (bool) ($this->per_km_xof || $this->half_day_xof || $this->full_day_xof),
            'rental' => (int) $this->price_xof > 0,
            'line' => true,
            default => false,
        };
    }

    /** @return array<int, array{label: string, price_xof: int}> */
    public function zones(): array
    {
        return collect($this->zone_prices ?? [])
            ->filter(fn ($z) => is_array($z) && trim((string) ($z['label'] ?? '')) !== '' && is_numeric($z['price_xof'] ?? null))
            ->map(fn ($z) => ['label' => trim((string) $z['label']), 'price_xof' => (int) $z['price_xof']])->values()->all();
    }

    /** @return array<int, array{label: string, price_xof: int, per: string}> per : booking | day | passenger */
    public function extrasList(): array
    {
        return collect($this->extras ?? [])
            ->filter(fn ($e) => is_array($e) && trim((string) ($e['label'] ?? '')) !== '')
            ->map(fn ($e) => ['label' => trim((string) $e['label']), 'price_xof' => max(0, (int) ($e['price_xof'] ?? 0)), 'per' => in_array($e['per'] ?? '', ['day', 'passenger'], true) ? $e['per'] : 'booking'])
            ->values()->all();
    }

    public function fromPrice(): int
    {
        return (int) match ($this->mode) {
            'transfer' => collect($this->zones())->min('price_xof') ?? $this->price_xof,
            'chauffeur' => $this->minimum_xof ?: ($this->half_day_xof ?: $this->price_xof),
            'line' => $this->upcomingTrips()->min('price_xof') ?: $this->price_xof,
            default => $this->price_xof,
        };
    }

    public function cancellationLabel(): ?string
    {
        if ($this->free_cancellation_hours === null) {
            return null;
        }

        return $this->free_cancellation_hours === 0
            ? __('Annulation et modification gratuites jusqu\'au départ')
            : __('Annulation et modification gratuites jusqu\'à :h h avant', ['h' => $this->free_cancellation_hours]);
    }

    public function modeLabel(): string
    {
        return __(self::MODES[$this->mode] ?? 'Sur demande');
    }
}
