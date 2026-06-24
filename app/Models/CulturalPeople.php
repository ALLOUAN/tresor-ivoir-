<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class CulturalPeople extends Model
{
    protected $table = 'cultural_peoples';

    protected $fillable = [
        'name', 'slug', 'zone_geographique', 'famille_linguistique',
        'langue_principale', 'population_estimee', 'capitale_culturelle',
        'description', 'histoire', 'symboles',
        'thumbnail', 'cover_image',
        'is_featured', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'symboles'    => 'array',
            'is_featured' => 'boolean',
            'is_active'   => 'boolean',
        ];
    }

    // Éléments culturels qui mentionnent ce peuple dans leur JSON people_roles
    public function elements()
    {
        return CulturalElement::whereJsonContains('people_roles', ['people_id' => $this->id])
            ->where('is_active', 1)
            ->orderBy('sort_order')
            ->get();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', 1);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', 1);
    }
}
