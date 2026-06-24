<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CulturalDomain extends Model
{
    protected $fillable = [
        'parent_id', 'name', 'slug', 'icon', 'color',
        'description', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(CulturalDomain::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(CulturalDomain::class, 'parent_id')->orderBy('sort_order');
    }

    public function elements(): HasMany
    {
        return $this->hasMany(CulturalElement::class, 'domain_id');
    }

    public function activeElements(): HasMany
    {
        return $this->hasMany(CulturalElement::class, 'domain_id')->where('is_active', 1);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }
}
