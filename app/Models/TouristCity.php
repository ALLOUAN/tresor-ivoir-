<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class TouristCity extends Model
{
    use \App\Models\Concerns\HasLocalizedFields;

    protected $fillable = [
        'name', 'slug', 'district', 'region_administrative',
        'description', 'thumbnail', 'cover_image', 'banners',
        'latitude', 'longitude', 'website',
        'is_featured', 'is_active', 'sort_order',
        'sources',
    ];

    /** Nombre maximal de bannières supplémentaires (diaporama) par ville. */
    public const MAX_BANNERS = 10;

    protected function casts(): array
    {
        return [
            'latitude'    => 'float',
            'longitude'   => 'float',
            'is_featured' => 'boolean',
            'sources' => 'array',
            'banners'     => 'array',
            'is_active'   => 'boolean',
        ];
    }

    /**
     * Images du haut de la page ville, dans l'ordre d'affichage : la bannière principale puis les bannières
     * supplémentaires (sans doublon). À défaut, la miniature.
     *
     * @return list<string>
     */
    public function bannerList(): array
    {
        $list = collect([$this->cover_image])->merge($this->banners ?? [])
            ->filter(fn ($u) => is_string($u) && trim($u) !== '')
            ->map(fn ($u) => trim($u))
            ->unique()
            ->values()
            ->all();

        return $list ?: array_values(array_filter([$this->thumbnail]));
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
