<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/** Bannière publicitaire de la page d'accueil (emplacement entre deux sections). */
class HomePromoBanner extends Model
{
    public const PLACEMENTS = [
        'after_annuaire' => 'Après « Les meilleures adresses »',
        'after_cultures' => 'Après « Cultures ivoiriennes »',
    ];

    public const CACHE_KEY = 'home_promo_banners';

    protected $fillable = [
        'placement', 'kicker', 'title_strong', 'title_rest', 'accent', 'cta_label', 'link_url',
        'open_new_tab', 'image_url', 'images', 'icon', 'is_active', 'display_order',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'open_new_tab' => 'boolean', 'images' => 'array'];
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    /** Bannière active d'un emplacement (la première dans l'ordre), mise en cache 10 min. */
    public static function forPlacement(string $placement): ?self
    {
        if (! \App\Support\SchemaCache::hasTable('home_promo_banners')) {
            return null;
        }
        // Le cache ne restaure pas d'objets Eloquent (classes non autorisées à la désérialisation) :
        // on y met des tableaux simples, puis on reconstruit les modèles.
        $rows = Cache::remember(self::CACHE_KEY, 600, fn () => static::active()->orderBy('display_order')->orderBy('id')->get()->map->getAttributes()->all());

        return static::hydrate($rows)->firstWhere('placement', $placement);
    }

    public const MAX_IMAGES = 8;

    /**
     * Visuels qui défilent dans la bannière : la liste, sinon l'ancien visuel unique.
     *
     * @return list<string>
     */
    public function imageList(): array
    {
        $list = array_values(array_filter((array) ($this->images ?? []), fn ($u) => is_string($u) && $u !== ''));

        return $list ?: ($this->image_url ? [$this->image_url] : []);
    }

    public function placementLabel(): string
    {
        return self::PLACEMENTS[$this->placement] ?? $this->placement;
    }
}
