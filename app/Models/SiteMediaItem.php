<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SiteMediaItem extends Model
{
    protected $fillable = [
        'uuid',
        'type',
        'mime_type',
        'original_name',
        'title',
        'alt_text',
        'caption',
        'credit',
        'price',
        'section',
        'is_active',
        'is_featured',
        'display_order',
        'published_at',
        'file_path',
        'url',
        'size_bytes',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'published_at' => 'datetime',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'price' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (SiteMediaItem $item): void {
            $item->uuid ??= (string) Str::uuid();
        });
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Médias images éligibles pour la galerie publique d’accueil (mêmes règles que l’admin « Accueil - Galerie »).
     */
    public function scopeForPublicHomeGallery(Builder $query): Builder
    {
        $query->where('type', 'image');

        if (Schema::hasColumn('site_media_items', 'is_active')) {
            $query->where('is_active', true);
        }
        if (Schema::hasColumn('site_media_items', 'section')) {
            $query->where('section', 'home_gallery');
        }
        if (Schema::hasColumn('site_media_items', 'published_at')) {
            $query->where(function ($q): void {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
        }

        return $query;
    }

    public function scopeOrderedForPublicGallery(Builder $query): Builder
    {
        if (Schema::hasColumn('site_media_items', 'display_order')) {
            $query->orderBy('display_order');
        }
        if (Schema::hasColumn('site_media_items', 'published_at')) {
            $query->orderByDesc('published_at');
        }

        return $query->orderByDesc('id');
    }

    /**
     * Images publiées pour la galerie d’accueil (section admin « Accueil - Galerie »).
     */
    public static function publicHomeGalleryImages(int $limit = 60): Collection
    {
        if (! Schema::hasTable('site_media_items')) {
            return collect();
        }

        return static::query()
            ->forPublicHomeGallery()
            ->orderedForPublicGallery()
            ->limit($limit)
            ->get();
    }
}
