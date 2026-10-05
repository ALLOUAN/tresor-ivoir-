<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Artwork extends Model
{
    use SoftDeletes;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PENDING_REVIEW = 'pending_review';

    public const STATUS_PUBLISHED = 'published';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_SUSPENDED = 'suspended';

    public const STATUS_SOLD = 'sold';

    public const STATUS_WITHDRAWN = 'withdrawn';

    /** @var array<string, string> */
    public const STATUS_LABELS = [
        self::STATUS_DRAFT => 'Brouillon',
        self::STATUS_PENDING_REVIEW => 'En attente de validation',
        self::STATUS_PUBLISHED => 'Publiée',
        self::STATUS_REJECTED => 'Refusée',
        self::STATUS_SUSPENDED => 'Suspendue',
        self::STATUS_SOLD => 'Vendue',
        self::STATUS_WITHDRAWN => 'Retirée',
    ];

    protected $fillable = [
        'provider_id', 'category_id', 'title', 'slug', 'description',
        'medium', 'dimensions', 'year_created', 'images',
        'price_xof', 'stock_quantity', 'status', 'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'images' => 'array',
            'is_featured' => 'boolean',
            'price_xof' => 'integer',
            'stock_quantity' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(fn (Artwork $artwork) => $artwork->uuid ??= (string) Str::uuid());
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ArtworkCategory::class, 'category_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(ArtworkOrder::class);
    }

    public function isAvailable(): bool
    {
        return $this->status === self::STATUS_PUBLISHED && $this->stock_quantity > 0;
    }

    public function labelForStatus(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }
}
