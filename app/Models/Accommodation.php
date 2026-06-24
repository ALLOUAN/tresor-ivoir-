<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Accommodation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'city_id',
        'name', 'slug', 'type', 'stars',
        'short_description', 'description',
        'adresse', 'quartier', 'latitude', 'longitude',
        'phone', 'email', 'website',
        'thumbnail', 'cover_image',
        'check_in_time', 'check_out_time',
        'amenities', 'category_ids', 'room_types', 'booking_links',
        'is_featured', 'is_active', 'views_count', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'latitude'        => 'float',
            'longitude'       => 'float',
            'amenities'       => 'array',
            'category_ids'    => 'array',
            'room_types'      => 'array',
            'booking_links'   => 'array',
            'is_featured'     => 'boolean',
            'is_active'       => 'boolean',
            'check_in_time'   => 'string',
            'check_out_time'  => 'string',
        ];
    }

    /* ── Relations ───────────────────────────────────────────────── */

    public function city(): BelongsTo
    {
        return $this->belongsTo(TouristCity::class, 'city_id');
    }

    public function media(): HasMany
    {
        return $this->hasMany(AccommodationMedia::class)->orderBy('sort_order');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(AccommodationMedia::class)
            ->where('type', 'photo')
            ->orderBy('sort_order');
    }

    public function videos(): HasMany
    {
        return $this->hasMany(AccommodationMedia::class)
            ->where('type', 'video')
            ->orderBy('sort_order');
    }

    /* ── Accessors ───────────────────────────────────────────────── */

    /**
     * Retourne les TouristCategory correspondant à category_ids.
     */
    public function getCategoriesAttribute()
    {
        $ids = $this->category_ids ?? [];
        if (empty($ids)) {
            return collect();
        }
        return TouristCategory::whereIn('id', $ids)->orderBy('sort_order')->get();
    }

    /**
     * Lien booking officiel (is_official = true) s'il existe.
     */
    public function getOfficialBookingLinkAttribute(): ?array
    {
        return collect($this->booking_links ?? [])
            ->firstWhere('is_official', true);
    }

    /**
     * Prix de départ (chambre la moins chère).
     */
    public function getStartingPriceXofAttribute(): ?int
    {
        $prices = collect($this->room_types ?? [])
            ->pluck('price_xof')
            ->filter()
            ->values();

        return $prices->isNotEmpty() ? (int) $prices->min() : null;
    }

    public function getStartingPriceEurAttribute(): ?float
    {
        $prices = collect($this->room_types ?? [])
            ->pluck('price_eur')
            ->filter()
            ->values();

        return $prices->isNotEmpty() ? (float) $prices->min() : null;
    }

    /* ── Scopes ──────────────────────────────────────────────────── */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', 1);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', 1);
    }

    public function scopeForCity(Builder $query, int $cityId): Builder
    {
        return $query->where('city_id', $cityId);
    }

    /** Filtre par categorie touristique (JSON contains). */
    public function scopeForCategory(Builder $query, int $categoryId): Builder
    {
        return $query->whereJsonContains('category_ids', $categoryId);
    }

    /** Filtre par type d'hébergement. */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /** Filtre par nombre d'étoiles minimum. */
    public function scopeMinStars(Builder $query, int $stars): Builder
    {
        return $query->where('stars', '>=', $stars);
    }

    /* ── Helpers ─────────────────────────────────────────────────── */

    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    public function hasCoordinates(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    public function getGoogleMapsUrlAttribute(): ?string
    {
        if (!$this->hasCoordinates()) {
            return null;
        }
        return "https://maps.google.com/?q={$this->latitude},{$this->longitude}";
    }

    /** Libellé lisible du type. */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'hotel'      => 'Hôtel',
            'resort'     => 'Resort',
            'guesthouse' => 'Maison d\'hôtes',
            'hostel'     => 'Auberge de jeunesse',
            'auberge'    => 'Auberge',
            'villa'      => 'Villa',
            'eco_lodge'  => 'Éco-lodge',
            default      => ucfirst($this->type),
        };
    }

    /** Retourne les étoiles sous forme de tableau [filled, empty]. */
    public function getStarsArrayAttribute(): array
    {
        return [
            'filled' => $this->stars,
            'empty'  => max(0, 5 - $this->stars),
        ];
    }
}
