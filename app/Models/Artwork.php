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
        'width_cm', 'height_cm', 'depth_cm', 'accepts_offers', 'min_offer_xof', 'layaway_allowed',
    ];

    protected function casts(): array
    {
        return [
            'images' => 'array',
            'is_featured' => 'boolean',
            'price_xof' => 'integer',
            'stock_quantity' => 'integer',
            'width_cm' => 'float',
            'height_cm' => 'float',
            'depth_cm' => 'float',
            'accepts_offers' => 'boolean',
            'min_offer_xof' => 'integer',
            'layaway_allowed' => 'boolean',
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

    public function editions(): HasMany
    {
        return $this->hasMany(ArtworkEdition::class)->orderBy('price_xof');
    }

    public function offers(): HasMany
    {
        return $this->hasMany(ArtworkOffer::class);
    }

    public function isAvailable(): bool
    {
        return $this->status === self::STATUS_PUBLISHED && $this->stock_quantity > 0;
    }

    /** Disponible à l'achat : l'original, ou au moins un tirage d'édition limitée. */
    public function isPurchasable(): bool
    {
        return $this->status === self::STATUS_PUBLISHED
            && ($this->stock_quantity > 0 || $this->editions()->where('is_active', true)->whereColumn('sold_count', '<', 'edition_size')->exists());
    }

    /** Réservée : l'original est bloqué par un paiement en plusieurs fois en cours. */
    public function isReserved(): bool
    {
        return $this->orders()->where('status', ArtworkOrder::STATUS_RESERVED)->exists();
    }

    /** Catégorie de taille pour la livraison : grand format au-delà de 100 cm. */
    public function isLarge(): bool
    {
        return max((float) $this->width_cm, (float) $this->height_cm) > 100;
    }

    public function artistProfile(): ?ArtistProfile
    {
        return ArtistProfile::where('provider_id', $this->provider_id)->first();
    }

    public function labelForStatus(): string
    {
        return __(self::STATUS_LABELS[$this->status] ?? $this->status);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }
}
