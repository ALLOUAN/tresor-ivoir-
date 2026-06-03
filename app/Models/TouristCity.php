<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class TouristCity extends Model
{
    protected $fillable = [
        'name', 'slug', 'district', 'region_administrative',
        'description', 'thumbnail', 'cover_image',
        'latitude', 'longitude',
        'is_featured', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'latitude'    => 'float',
            'longitude'   => 'float',
            'is_featured' => 'boolean',
            'is_active'   => 'boolean',
        ];
    }

    public function sites(): HasMany
    {
        return $this->hasMany(TouristSite::class, 'city_id');
    }

    public function activeSites(): HasMany
    {
        return $this->hasMany(TouristSite::class, 'city_id')->where('is_active', 1);
    }

    // Catégories disponibles pour cette ville (via les sites existants)
    public function categories()
    {
        return TouristCategory::whereIn('id',
            $this->sites()->where('is_active', 1)->pluck('category_id')->unique()
        )->orderBy('sort_order')->get();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', 1);
    }
}
