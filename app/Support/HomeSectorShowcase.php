<?php

namespace App\Support;

use App\Models\Accommodation;
use App\Models\LeisureVenue;
use App\Models\ProviderCategory;
use App\Models\Restaurant;
use App\Models\TouristExperience;
use App\Models\TransportCompany;
use App\Models\TravelAgency;
use Illuminate\Support\Collection;

/**
 * Vitrine de la section Annuaire de la page d'accueil : pour chaque secteur, les
 * établissements tels qu'ils apparaissent sur la page de listing du secteur
 * (/residences-hotels, /restaurants, /experiences-touristiques, /agences-voyages,
 * /loisirs-culture, /transports) — mêmes modèles, mêmes liens vers les pages dédiées.
 */
class HomeSectorShowcase
{
    /** slug de catégorie racine => configuration du secteur. */
    private const SECTORS = [
        'hotels' => ['model' => Accommodation::class, 'page' => 'accommodations.rooms', 'index' => 'accommodations.index', 'icon' => 'fa-hotel'],
        'restaurants' => ['model' => Restaurant::class, 'page' => 'restaurant.menu', 'index' => 'restaurant.index', 'icon' => 'fa-utensils'],
        'sites-touristiques' => ['model' => TouristExperience::class, 'page' => 'tourist-experience.activities', 'index' => 'tourist-experience.index', 'icon' => 'fa-mountain-sun'],
        'agences-voyages' => ['model' => TravelAgency::class, 'page' => 'travel-agency.tours', 'index' => 'travel-agency.index', 'icon' => 'fa-plane-departure'],
        'loisirs-culture' => ['model' => LeisureVenue::class, 'page' => 'leisure.activities', 'index' => 'leisure.index', 'icon' => 'fa-masks-theater'],
        'transports' => ['model' => TransportCompany::class, 'page' => 'transport-company.offers', 'index' => 'transport-company.index', 'icon' => 'fa-van-shuttle'],
    ];

    /**
     * @param  Collection<int, ProviderCategory>  $roots  catégories racine affichées en onglets
     * @param  int|null  $limit  nombre d'établissements par secteur ; `null` = tous (page dédiée « tous les établissements »)
     * @return array<string, array{items: Collection, index_url: string}>  indexé par slug de catégorie
     */
    public static function build(Collection $roots, ?int $limit = 4): array
    {
        $out = [];

        foreach ($roots as $root) {
            $cfg = self::SECTORS[$root->slug] ?? null;
            if (! $cfg) {
                continue;
            }

            /** @var class-string<\Illuminate\Database\Eloquent\Model> $model */
            $model = $cfg['model'];

            $items = $model::active()
                ->with(['city', 'provider', 'media'])
                ->orderByDesc('is_featured')
                ->orderByDesc('views_count')
                ->when($limit !== null, fn ($q) => $q->limit($limit))
                ->get()
                ->map(fn ($e) => [
                    'name' => $e->name,
                    'url' => route($cfg['page'], $e->slug),
                    'image' => $e->media->sortBy('sort_order')->first()?->url ?: $e->cover_image ?: $e->thumbnail,
                    'location' => trim(($e->city?->name ?? '').($e->city?->region_administrative ? ' · '.$e->city->region_administrative : '')),
                    'badge' => $model === Accommodation::class ? $e->type_label : null,
                    'description' => $model === Accommodation::class ? null : $e->short_description,
                    'price' => $model === Accommodation::class ? $e->starting_price_xof : null,
                    'rating' => $e->provider?->rating_avg ? (float) $e->provider->rating_avg : null,
                    'verified' => (bool) ($e->provider?->is_verified),
                    'icon' => $cfg['icon'],
                    'sector' => $root->name_fr,
                ]);

            $out[$root->slug] = [
                'items' => $items,
                'index_url' => route($cfg['index']),
            ];
        }

        return $out;
    }

    /**
     * Vue « Tous » : mélange de tous les secteurs en tourniquet — d'abord l'établissement
     * mis en avant de chaque secteur, puis le suivant de chacun, etc., jusqu'à $count cartes.
     *
     * @param  array<string, array{items: Collection, index_url: string}>  $showcase
     * @return Collection<int, array<string, mixed>>
     */
    public static function featured(array $showcase, int $count = 8): Collection
    {
        $picked = collect();
        $rounds = collect($showcase)->map(fn (array $s) => $s['items']->count())->max() ?? 0;

        for ($round = 0; $round < $rounds && $picked->count() < $count; $round++) {
            foreach ($showcase as $sector) {
                $item = $sector['items']->get($round);
                if ($item !== null && $picked->count() < $count) {
                    $picked->push($item);
                }
            }
        }

        return $picked->values();
    }
}
