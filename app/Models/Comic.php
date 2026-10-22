<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/** Bande dessinée de la rubrique « Les Astuces de Mian » (planches en images). */
class Comic extends Model
{
    public const CATEGORIES = [
        'educatif' => ['label' => 'Éducatif', 'icon' => 'book', 'tone' => 'palm'],
        'ludique' => ['label' => 'Ludique', 'icon' => 'star-shine', 'tone' => 'sun'],
        'divertissant' => ['label' => 'Divertissant', 'icon' => 'smile-circle', 'tone' => 'lagoon'],
    ];

    /** Formats acceptés pour la couverture et les planches. */
    public const IMAGE_MIMES = ['jpg', 'jpeg', 'png', 'webp'];

    public const IMAGE_MAX_KB = 8192;

    public const MAX_PAGES = 80;

    protected $fillable = [
        'title', 'slug', 'summary', 'category', 'age_label', 'cover_path', 'pages',
        'is_published', 'is_featured', 'views_count', 'sort_order', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'pages' => 'array',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Comic $c) {
            $c->slug ??= static::uniqueSlug($c->title);
        });
    }

    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'bd';
        $slug = $base;
        for ($i = 2; static::where('slug', $slug)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists(); $i++) {
            $slug = $base.'-'.$i;
        }

        return $slug;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePublished(Builder $q): Builder
    {
        return $q->where('is_published', true);
    }

    public function scopeOrdered(Builder $q): Builder
    {
        return $q->orderByDesc('is_featured')->orderBy('sort_order')->orderByDesc('published_at')->orderByDesc('id');
    }

    /** @return list<string> URL publiques des planches, dans l'ordre de lecture. */
    public function pageUrls(): array
    {
        return array_values(array_map(fn ($p) => Storage::disk('public')->url($p), array_filter((array) $this->pages)));
    }

    public function pageCount(): int
    {
        return count(array_filter((array) $this->pages));
    }

    /** Couverture : l'image dédiée, sinon la première planche. */
    public function coverUrl(): ?string
    {
        $path = $this->cover_path ?: (array_values(array_filter((array) $this->pages))[0] ?? null);

        return $path ? Storage::disk('public')->url($path) : null;
    }

    public function categoryLabel(): string
    {
        return __(self::CATEGORIES[$this->category]['label'] ?? ucfirst((string) $this->category));
    }

    public function categoryIcon(): string
    {
        return self::CATEGORIES[$this->category]['icon'] ?? 'book';
    }

    public function categoryTone(): string
    {
        return self::CATEGORIES[$this->category]['tone'] ?? 'palm';
    }

    /** Temps de lecture estimé : ~20 s par planche, au moins 1 min. */
    public function readingMinutes(): int
    {
        return max(1, (int) ceil($this->pageCount() * 20 / 60));
    }

    public function isNew(): bool
    {
        return $this->published_at !== null && $this->published_at->gt(now()->subDays(21));
    }
}
