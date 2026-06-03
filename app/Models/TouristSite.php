<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TouristSite extends Model
{
    use SoftDeletes;

    protected $fillable = [
        // Relations
        'city_id', 'category_id',

        // Identité
        'name', 'slug', 'short_description', 'description', 'thumbnail',

        // Contact
        'entrance_fee', 'website', 'phone', 'email',

        // Situation géographique
        'latitude', 'longitude',
        'departement', 'sous_prefecture', 'localite',
        'altitude_m', 'superficie_ha', 'distance_centre_km',
        'point_repere', 'acces_description', 'map_embed_url',

        // JSON
        'schedules', 'practical_info',

        // Méta
        'is_featured', 'is_active', 'views_count', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'latitude'          => 'float',
            'longitude'         => 'float',
            'superficie_ha'     => 'float',
            'distance_centre_km'=> 'float',
            'schedules'         => 'array',
            'practical_info'    => 'array',
            'is_featured'       => 'boolean',
            'is_active'         => 'boolean',
        ];
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(TouristCity::class, 'city_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TouristCategory::class, 'category_id');
    }

    public function media(): HasMany
    {
        return $this->hasMany(TouristSiteMedia::class, 'site_id')->orderBy('sort_order');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(TouristSiteMedia::class, 'site_id')
            ->where('type', 'photo')
            ->orderBy('sort_order');
    }

    public function videos(): HasMany
    {
        return $this->hasMany(TouristSiteMedia::class, 'site_id')
            ->where('type', 'video')
            ->orderBy('sort_order');
    }

    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', 1);
    }

    public function scopeForCity($query, int $cityId)
    {
        return $query->where('city_id', $cityId);
    }

    public function scopeForCategory($query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    // Coordonnées GPS formatées pour Google Maps
    public function getGpsCoordinatesAttribute(): ?string
    {
        if ($this->latitude && $this->longitude) {
            return "{$this->latitude},{$this->longitude}";
        }
        return null;
    }
}
