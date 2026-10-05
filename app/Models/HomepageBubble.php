<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HomepageBubble extends Model
{
    /**
     * Sections publiques (hors accueil, toujours affiché) où une bulle peut être ajoutée,
     * en plus du raccourci "all" qui vaut pour toutes les pages du site. Chaque section
     * couvre sa page de liste ET ses pages internes (fiche détail, sous-pages) via les
     * préfixes de nom de route — ex: "articles." couvre articles.index ET articles.show.
     * Utilisé par le formulaire admin (liste à cocher), la validation et appliesToRoute().
     */
    public const SELECTABLE_PAGES = [
        'all' => ['label' => 'Toutes les pages', 'prefixes' => []],
        'tourist.cities' => ['label' => 'Tourisme', 'prefixes' => ['tourist.']],
        'accommodations.index' => ['label' => 'Hôtels & Résidences', 'prefixes' => ['accommodations.']],
        'restaurant.index' => ['label' => 'Restaurants & Gastronomie', 'prefixes' => ['restaurant.']],
        'tourist-experience.index' => ['label' => 'Sites Touristiques', 'prefixes' => ['tourist-experience.', 'tourist-visit.']],
        'travel-agency.index' => ['label' => 'Agences de Voyages & Tours', 'prefixes' => ['travel-agency.']],
        'leisure.index' => ['label' => 'Loisirs & Culture', 'prefixes' => ['leisure.']],
        'transport-company.index' => ['label' => 'Transports & Mobilité', 'prefixes' => ['transport-company.']],
        'art.index' => ['label' => 'Art & Créations', 'prefixes' => ['art.']],
        'cultural.peoples' => ['label' => 'Cultures & Traditions', 'prefixes' => ['cultural.']],
        'providers.index' => ['label' => 'Annuaire', 'prefixes' => ['providers.']],
        'articles.index' => ['label' => 'Magazine', 'prefixes' => ['articles.', 'discoveries.']],
        'events.index' => ['label' => 'Événements', 'prefixes' => ['events.']],
        'gallery.public' => ['label' => 'Galerie', 'prefixes' => ['gallery.']],
    ];

    protected $fillable = [
        'title', 'description', 'link_url', 'link_label',
        'position_top', 'position_left', 'size', 'color_hex', 'icon',
        'is_active', 'pages', 'display_order',
    ];

    protected function casts(): array
    {
        return [
            'position_top' => 'float',
            'position_left' => 'float',
            'is_active' => 'boolean',
            'pages' => 'array',
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

    /**
     * Vrai si cette bulle doit s'afficher sur la page publique courante (route nommée
     * $routeName), d'après sa sélection de sections ("all" ou préfixes de route).
     * L'accueil n'est volontairement pas concerné par cette méthode : il affiche
     * toujours toutes les bulles actives, quelle que soit leur sélection de pages.
     */
    public function appliesToRoute(?string $routeName): bool
    {
        if (! $routeName) {
            return false;
        }

        $pages = $this->pages ?? [];

        if (in_array('all', $pages, true)) {
            return true;
        }

        foreach ($pages as $pageKey) {
            foreach (self::SELECTABLE_PAGES[$pageKey]['prefixes'] ?? [] as $prefix) {
                if (str_starts_with($routeName, $prefix)) {
                    return true;
                }
            }
        }

        return false;
    }
}
