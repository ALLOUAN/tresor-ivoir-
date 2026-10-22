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
        'hotels' => ['model' => Accommodation::class, 'page' => 'accommodations.rooms', 'index' => 'accommodations.index', 'icon' => 'fa-hotel', 'cta' => 'Voir les chambres'],
        'restaurants' => ['model' => Restaurant::class, 'page' => 'restaurant.menu', 'index' => 'restaurant.index', 'icon' => 'fa-utensils', 'cta' => 'Voir la carte'],
        'sites-touristiques' => ['model' => TouristExperience::class, 'page' => 'tourist-experience.activities', 'index' => 'tourist-experience.index', 'icon' => 'fa-mountain-sun', 'cta' => 'Voir les activités'],
        'agences-voyages' => ['model' => TravelAgency::class, 'page' => 'travel-agency.tours', 'index' => 'travel-agency.index', 'icon' => 'fa-plane-departure', 'cta' => 'Voir les circuits'],
        'loisirs-culture' => ['model' => LeisureVenue::class, 'page' => 'leisure.activities', 'index' => 'leisure.index', 'icon' => 'fa-masks-theater', 'cta' => 'Voir les activités'],
        'transports' => ['model' => TransportCompany::class, 'page' => 'transport-company.offers', 'index' => 'transport-company.index', 'icon' => 'fa-van-shuttle', 'cta' => 'Voir les offres'],
    ];

    /**
     * @param  Collection<int, ProviderCategory>  $roots  catégories racine affichées en onglets
     * @param  int|null  $limit  nombre d'établissements par secteur ; `null` = tous (page dédiée « tous les établissements »)
     * @return array<string, array{items: Collection, index_url: string}>  indexé par slug de catégorie
     */
    /**
     * Photos d'un établissement, sans doublon : galerie (ordre choisi) puis couverture.
     * La vignette est exclue : c'est une copie réduite de la couverture.
     * La première illustre la carte, la deuxième apparaît au survol.
     *
     * @return list<string>
     */
    public static function photos($e): array
    {
        return collect($e->media->sortBy('sort_order'))
            // « image » (table media) ou « photo » (galerie des hébergements) ; les vidéos sont exclues.
            ->filter(fn ($m) => in_array($m->type ?? 'image', ['image', 'photo'], true))
            ->pluck('url')
            ->push($e->cover_image ?? null)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Établissements (prestataires) favoris du visiteur connecté, une seule requête par page.
     *
     * @return list<int>
     */
    public static function favoriteProviderIds(): array
    {
        return self::favoriteIds(\App\Models\Provider::class);
    }

    /** @return list<int> */
    public static function favoriteIds(string $modelClass): array
    {
        static $cache = [];
        $key = auth()->id().'|'.$modelClass;

        return $cache[$key] ??= auth()->user()?->role === 'visitor'
            ? \App\Models\VisitorFavorite::where('user_id', auth()->id())
                ->where('favoritable_type', $modelClass)
                ->pluck('favoritable_id')->map(fn ($id) => (int) $id)->all()
            : [];
    }

    private static function defaultFreeCancellationDays(): int
    {
        return once(function () {
            $v = \App\Models\PaymentSetting::where('key', 'free_cancellation_days')->value('value');

            return $v !== null && $v !== '' ? (int) $v : \App\Models\Reservation::DEFAULT_FREE_CANCELLATION_DAYS;
        });
    }

    /**
     * Données d'une carte d'établissement (partials.home-sector-card), pour l'accueil comme pour
     * les pages de liste de chaque secteur. $sectorSlug : clé de self::SECTORS (hotels, restaurants…).
     *
     * @return array<string, mixed>
     */
    public static function card($e, string $sectorSlug, ?string $sectorName = null): array
    {
        $cfg = self::SECTORS[$sectorSlug];
        $isStay = $cfg['model'] === Accommodation::class;

        return [
            'name' => $e->name,
            'url' => route($cfg['page'], $e->slug),
            'image' => self::photos($e)[0] ?? $e->thumbnail,
            'hover_image' => self::photos($e)[1] ?? null,
            'photo_count' => count(self::photos($e)),
            'stars' => $isStay && (int) $e->stars > 0 ? (int) $e->stars : null,
            'amenities' => collect($e->amenities ?? [])->filter(fn ($a) => ! empty($a['label']))->values(),
            'cta' => __($cfg['cta']),
            'provider_id' => $e->provider_id,
            'reviews' => (int) ($e->provider?->rating_count ?? 0),
            'response_badge' => $e->provider?->responseBadge(),
            // Annulation gratuite : hébergements réservables en ligne (prix connu), selon le délai de la fiche ou de la plateforme.
            'free_cancellation' => $isStay && $e->starting_price_xof
                && ($e->free_cancellation_days ?? self::defaultFreeCancellationDays()) > 0,
            'map_url' => $e->latitude && $e->longitude
                ? 'https://www.google.com/maps/dir/?api=1&destination='.$e->latitude.','.$e->longitude
                : 'https://www.google.com/maps/search/?api=1&query='.rawurlencode(trim($e->name.' '.($e->city?->name ?? '').' Côte d\'Ivoire')),
            'location' => trim(($e->city?->name ?? '').($e->city?->region_administrative ? ' · '.$e->city->region_administrative : '')),
            'badge' => $isStay ? $e->type_label : null,
            'description' => $isStay ? null : (method_exists($e, 'localized') ? $e->localized('short_description') : $e->short_description),
            'price' => $isStay ? $e->starting_price_xof : null,
            'rating' => $e->provider?->rating_avg ? (float) $e->provider->rating_avg : null,
            'verified' => (bool) ($e->provider?->is_verified),
            'icon' => $cfg['icon'],
            'sector' => $sectorName,
        ];
    }

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
                ->map(fn ($e) => self::card($e, $root->slug, $root->name_fr));

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
