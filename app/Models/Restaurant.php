<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Fiche riche « Restaurants & Gastronomie » — même patron que LeisureVenue.
 * Les offres proposées restent portées par MenuItem (via le Provider lié),
 * pas dupliquées ici — voir RestaurantController::show().
 */
class Restaurant extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'city_id',
        'provider_id',
        'name', 'slug',
        'short_description', 'description',
        'adresse', 'quartier', 'latitude', 'longitude',
        'phone', 'email', 'website',
        'thumbnail', 'cover_image',
        'amenities',
        'is_featured', 'is_active', 'views_count', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'amenities' => 'array',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /* ── Relations ───────────────────────────────────────────────── */

    public function city(): BelongsTo
    {
        return $this->belongsTo(TouristCity::class, 'city_id');
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable')->orderBy('sort_order');
    }

    public function photos(): MorphMany
    {
        return $this->media()->where('type', 'image');
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
        if (! $this->hasCoordinates()) {
            return null;
        }

        return "https://maps.google.com/?q={$this->latitude},{$this->longitude}";
    }

    public function getTypeLabelAttribute(): string
    {
        return 'Restaurants & Gastronomie';
    }
}
