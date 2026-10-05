<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransportOffer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'provider_id', 'category_id', 'name', 'description', 'price_xof',
        'max_passengers', 'images', 'is_available', 'is_featured', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'images' => 'array',
            'is_available' => 'boolean',
            'is_featured' => 'boolean',
            'price_xof' => 'integer',
            'max_passengers' => 'integer',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TransportCategory::class, 'category_id');
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_available', true);
    }
}
