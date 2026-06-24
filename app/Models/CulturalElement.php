<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CulturalElement extends Model
{
    use SoftDeletes;

    protected $fillable = [
        // Relations
        'domain_id',

        // Identité
        'name', 'slug', 'short_description', 'description',
        'origine_historique', 'thumbnail', 'cover_image', 'website',

        // Patrimoine
        'niveau_risque', 'unesco_status',

        // JSON fusionnés
        'people_roles', 'city_ids',
        'meilleure_periode', 'practical_info',

        // Méta
        'is_featured', 'is_active', 'views_count', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'people_roles'      => 'array',
            'city_ids'          => 'array',
            'meilleure_periode' => 'array',
            'practical_info'    => 'array',
            'is_featured'       => 'boolean',
            'is_active'         => 'boolean',
        ];
    }

    public function domain(): BelongsTo
    {
        return $this->belongsTo(CulturalDomain::class, 'domain_id');
    }

    public function media(): HasMany
    {
        return $this->hasMany(CulturalElementMedia::class, 'element_id')->orderBy('sort_order');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(CulturalElementMedia::class, 'element_id')
            ->where('type', 'photo')
            ->orderBy('sort_order');
    }

    public function videos(): HasMany
    {
        return $this->hasMany(CulturalElementMedia::class, 'element_id')
            ->where('type', 'video')
            ->orderBy('sort_order');
    }

    public function audios(): HasMany
    {
        return $this->hasMany(CulturalElementMedia::class, 'element_id')
            ->where('type', 'audio')
            ->orderBy('sort_order');
    }

    // Charge les peuples associés depuis le JSON people_roles
    public function getPeoplesAttribute()
    {
        if (empty($this->people_roles)) {
            return collect();
        }
        $ids = collect($this->people_roles)->pluck('people_id')->filter();
        return CulturalPeople::whereIn('id', $ids)->orderBy('name')->get();
    }

    // Charge les villes touristiques associées depuis le JSON city_ids
    public function getCitiesAttribute()
    {
        if (empty($this->city_ids)) {
            return collect();
        }
        return TouristCity::whereIn('id', $this->city_ids)->orderBy('name')->get();
    }

    // Rôle d'un peuple spécifique pour cet élément
    public function getRoleForPeople(int $peopleId): ?string
    {
        foreach ($this->people_roles ?? [] as $entry) {
            if ((int) $entry['people_id'] === $peopleId) {
                return $entry['role'] ?? null;
            }
        }
        return null;
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

    public function scopeForDomain($query, int $domainId)
    {
        return $query->where('domain_id', $domainId);
    }

    public function scopeForPeople($query, int $peopleId)
    {
        return $query->whereJsonContains('people_roles', ['people_id' => $peopleId]);
    }

    public function scopeForCity($query, int $cityId)
    {
        return $query->whereJsonContains('city_ids', $cityId);
    }
}
