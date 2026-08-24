<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HomepageBubble extends Model
{
    protected $fillable = [
        'title', 'description', 'link_url', 'link_label',
        'position_top', 'position_left', 'size', 'color_hex', 'icon',
        'is_active', 'display_order',
    ];

    protected function casts(): array
    {
        return [
            'position_top' => 'float',
            'position_left' => 'float',
            'is_active' => 'boolean',
        ];
    }

    public function images(): HasMany
    {
        return $this->hasMany(HomepageBubbleImage::class)->orderBy('display_order')->orderBy('id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('display_order')->orderBy('id');
    }
}
