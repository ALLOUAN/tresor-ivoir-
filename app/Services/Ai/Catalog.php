<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Catalogue unifié des fiches publiées, étiqueté et vectorisé. Construit à partir des
 * tables existantes (lecture seule) et gardé en cache quelques minutes.
 */
class Catalog
{
    private const CACHE_KEY = 'ai.catalog.v2';

    private ?array $snapshot = null;

    /**
     * @return array{items: array<string,CatalogItem>, vectors: array<string,array>, idf: array,
     *               maxViews: int, medianViews: float, cities: array<int,array>}
     */
    public function snapshot(): array
    {
        if ($this->snapshot !== null) {
            return $this->snapshot;
        }
        $ttl = now()->addMinutes((int) config('ai.cache_minutes', 10));

        // Le cache ne restitue pas les objets (serializable_classes) : on y met des tableaux.
        $data = Cache::remember(self::CACHE_KEY, $ttl, function () {
            $snap = $this->build();
            $snap["items"] = array_map(fn (CatalogItem $i) => get_object_vars($i), $snap["items"]);

            return $snap;
        });
        $data["items"] = array_map(fn (array $a) => new CatalogItem(...$a), $data["items"]);

        return $this->snapshot = $data;
    }

    public static function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /** @return array<string,CatalogItem> */
    public function items(): array
    {
        return $this->snapshot()['items'];
    }

    public function get(string $key): ?CatalogItem
    {
        return $this->items()[$key] ?? null;
    }

    public function vectorizer(): TextVectorizer
    {
        return TextVectorizer::fromExport(['idf' => $this->snapshot()['idf'], 'default' => log(1 + max(1, count($this->items())))]);
    }

    /** @return array<int,array{id:int,name:string,lat:?float,lng:?float,slug:string}> */
    public function cities(): array
    {
        return $this->snapshot()['cities'];
    }

    // ── Construction ────────────────────────────────────────────────────────

    private function build(): array
    {
        $cities = [];
        foreach (DB::table('tourist_cities')->where('is_active', 1)->orderBy('sort_order')->get() as $c) {
            $cities[(int) $c->id] = [
                'id' => (int) $c->id, 'name' => $c->name, 'slug' => $c->slug,
                'lat' => $c->latitude !== null ? (float) $c->latitude : null,
                'lng' => $c->longitude !== null ? (float) $c->longitude : null,
            ];
        }

        $providers = DB::table('providers')->whereNull('deleted_at')->where('status', 'active')->get()->keyBy('id');
        $planLevels = $this->planLevels();
        $menuAvg = DB::table('menu_items')->whereNull('deleted_at')->where('price_xof', '>', 0)
            ->select('provider_id', DB::raw('AVG(price_xof) as avg_price'))->groupBy('provider_id')->pluck('avg_price', 'provider_id');

        $items = [];
        $usedProviders = [];

        // Hébergements
        foreach (DB::table('accommodations')->where('is_active', 1)->whereNull('deleted_at')->get() as $r) {
            $rooms = json_decode((string) $r->room_types, true) ?: [];
            $prices = array_values(array_filter(array_map(fn ($x) => (int) ($x['price_xof'] ?? 0), $rooms)));
            $amen = array_merge($this->flat(json_decode((string) $r->amenities, true)), $this->flat(array_column($rooms, 'amenities')));
            $item = $this->make('accommodation', $r, $cities, $providers, $planLevels, [
                'typeLabel' => 'Hébergement', 'category' => (string) ($r->type ?: 'Hôtel'),
                'price' => $prices ? min($prices) : null,
                'body' => [$r->short_description, $r->description, implode(' ', $amen), $r->quartier, $r->adresse, $r->type, $r->stars ? $r->stars.' etoiles' : ''],
                'url' => route('accommodations.show', ['accommodation' => $r->slug]),
                'image' => $r->thumbnail ?: $r->cover_image,
                'extra' => ['stars' => $r->stars, 'rooms' => array_values(array_map(fn ($x) => ['name' => $x['name'] ?? 'Chambre', 'price' => (int) ($x['price_xof'] ?? 0)], array_filter($rooms, fn ($x) => (int) ($x['price_xof'] ?? 0) > 0)))],
            ]);
            $items[$item->key] = $item;
            $usedProviders[$r->provider_id] = true;
        }

        // Restaurants, loisirs, expériences, agences, transports
        $simple = [
            ['restaurants', 'restaurant', 'Restaurant', 'Restaurant', 'restaurant.show', fn ($r) => $menuAvg[$r->provider_id] ?? null],
            ['leisure_venues', 'leisure', 'Loisirs & culture', 'Loisirs', 'leisure.show', fn ($r) => null],
            ['tourist_experiences', 'experience', 'Expérience touristique', 'Expérience', 'tourist-experience.show', fn ($r) => $r->visit_individual_price_xof ? (int) $r->visit_individual_price_xof : null],
            ['travel_agencies', 'agency', 'Agence de voyages', 'Agence', 'travel-agency.show', fn ($r) => null],
            ['transport_companies', 'transport', 'Transport', 'Transport', 'transport-company.show', fn ($r) => null],
        ];
        foreach ($simple as [$table, $type, $label, $category, $route, $priceFn]) {
            foreach (DB::table($table)->where('is_active', 1)->whereNull('deleted_at')->get() as $r) {
                $amen = $this->flat(json_decode((string) $r->amenities, true));
                $price = $priceFn($r);
                $item = $this->make($type, $r, $cities, $providers, $planLevels, [
                    'typeLabel' => $label, 'category' => $category, 'price' => $price !== null ? (int) round($price) : null,
                    'body' => [$r->short_description, $r->description, implode(' ', $amen), $r->quartier, $r->adresse],
                    'url' => route($route, $r->slug),
                    'image' => $r->thumbnail ?: $r->cover_image,
                ]);
                $items[$item->key] = $item;
                $usedProviders[$r->provider_id] = true;
            }
        }

        // Sites touristiques
        $siteCats = DB::table('tourist_categories')->pluck('name', 'id');
        foreach (DB::table('tourist_sites')->where('is_active', 1)->whereNull('deleted_at')->get() as $r) {
            $cat = (string) ($siteCats[$r->category_id] ?? 'Site touristique');
            $item = $this->make('site', $r, $cities, $providers, $planLevels, [
                'typeLabel' => 'Site touristique', 'category' => $cat, 'price' => $this->parseFee($r->entrance_fee),
                'body' => [$r->short_description, $r->description, $cat, $r->localite, $r->departement, $r->point_repere, $r->practical_info],
                'url' => route('tourist.site', $r->slug),
                'image' => $r->thumbnail,
                'extra' => ['fee_label' => $r->entrance_fee],
            ]);
            $items[$item->key] = $item;
        }

        // Événements à venir ou en cours
        $eventCats = \App\Support\SchemaCache::hasTable('event_categories') ? DB::table('event_categories')->pluck('name_fr', 'id') : collect();
        $cityByName = collect($cities)->mapWithKeys(fn ($c) => [Str::lower(Str::ascii($c['name'])) => $c]);
        foreach (DB::table('events')->where('status', 'published')->get() as $r) {
            $end = $r->ends_at ?: $r->starts_at;
            if ($end && strtotime((string) $end) < time()) {
                continue;
            }
            $cat = (string) ($eventCats[$r->category_id] ?? 'Événement');
            $city = $cityByName[Str::lower(Str::ascii((string) $r->city))] ?? null;
            $item = $this->make('event', (object) array_merge((array) $r, [
                'name' => $r->title_fr, 'city_id' => $city['id'] ?? null,
                'is_featured' => 0, 'provider_id' => $r->provider_id, 'thumbnail' => $r->cover_url,
                'latitude' => $r->latitude ?? ($city['lat'] ?? null), 'longitude' => $r->longitude ?? ($city['lng'] ?? null),
            ]), $cities, $providers, $planLevels, [
                'typeLabel' => 'Événement', 'category' => $cat,
                'price' => ($r->is_free || (float) $r->price <= 0) ? 0 : (int) $r->price,
                'body' => [$r->subtitle_fr, $r->description_fr, $cat, $r->location_name, $r->city, $r->audience, $r->program],
                'url' => route('events.show', $r->slug), 'image' => $r->cover_url, 'city_text' => $r->city,
            ]);
            $item->startsAt = $r->starts_at ? strtotime((string) $r->starts_at) : null;
            $item->endsAt = $end ? strtotime((string) $end) : null;
            $items[$item->key] = $item;
        }

        // Annuaire : prestataires qui n'ont pas déjà leur propre fiche détaillée ci-dessus
        $cats = DB::table('provider_categories')->pluck('name_fr', 'id');
        foreach ($providers as $p) {
            if (isset($usedProviders[$p->id])) {
                continue;
            }
            $cat = (string) ($cats[$p->category_id] ?? 'Prestataire');
            $price = $p->price_min ? (int) $p->price_min : null;
            $item = $this->make('provider', (object) array_merge((array) $p, ['city_id' => null, 'is_featured' => $p->is_featured, 'thumbnail' => $p->logo_url]), $cities, $providers, $planLevels, [
                'typeLabel' => $cat, 'category' => $cat, 'price' => $price,
                'body' => [$p->short_desc_fr, $p->description_fr, $cat, $p->region, $p->address, $p->price_range],
                'url' => route('providers.show', $p->slug), 'image' => $p->cover_url ?: $p->logo_url, 'city_text' => $p->city,
                'provider_row' => $p,
            ]);
            $items[$item->key] = $item;
        }

        // Vectorisation (TF-IDF)
        $vectorizer = new TextVectorizer;
        $vectorizer->fit(array_map(fn (CatalogItem $i) => $i->tf, $items));
        $vectors = array_map(fn (CatalogItem $i) => $vectorizer->vector($i->tf), $items);

        $views = collect($items)->pluck('views')->sort()->values();
        $median = $views->isEmpty() ? 0.0 : (float) $views[(int) floor(($views->count() - 1) / 2)];

        return [
            'items' => $items,
            'vectors' => $vectors,
            'idf' => $vectorizer->exportIdf()['idf'],
            'maxViews' => (int) ($views->max() ?? 0),
            'medianViews' => $median,
            'cities' => $cities,
        ];
    }

    private function make(string $type, object $r, array $cities, $providers, array $planLevels, array $o): CatalogItem
    {
        $provider = $r->provider_id ?? null ? ($providers[$r->provider_id] ?? null) : null;
        if ($type === 'provider') {
            $provider = $o['provider_row'];
        }
        $cityId = isset($r->city_id) && $r->city_id ? (int) $r->city_id : null;
        $city = $cityId && isset($cities[$cityId]) ? $cities[$cityId]['name'] : ($o['city_text'] ?? null);
        $lat = isset($r->latitude) && $r->latitude !== null ? (float) $r->latitude : null;
        $lng = isset($r->longitude) && $r->longitude !== null ? (float) $r->longitude : null;
        // Coordonnées propres à la fiche ? Sinon repli sur le centre de la ville (position approximative).
        $geoExact = $lat !== null && $lng !== null;
        if (! $geoExact && $cityId && isset($cities[$cityId])) {
            [$lat, $lng] = [$cities[$cityId]['lat'], $cities[$cityId]['lng']];
        }

        $title = (string) ($r->name ?? '');
        $body = implode(' ', array_filter(array_map('strval', $o['body'])));
        $tf = TextVectorizer::termFrequencies([
            [$title, 3], [$o['category'], 2], [$o['typeLabel'], 1], [$city ?? '', 1], [$body, 1],
        ]);

        $image = $o['image'] ?? null;
        if ($image && ! str_starts_with($image, 'http') && ! str_starts_with($image, '/')) {
            $image = '/storage/'.$image;
        }

        return new CatalogItem(
            key: $type.':'.$r->id, type: $type, id: (int) $r->id, title: $title,
            typeLabel: $o['typeLabel'], category: $o['category'], city: $city, cityId: $cityId,
            lat: $lat, lng: $lng, price: $o['price'],
            rating: $provider ? (float) $provider->rating_avg : 0.0,
            ratingCount: $provider ? (int) $provider->rating_count : 0,
            views: (int) ($r->views_count ?? 0),
            featured: (bool) ($r->is_featured ?? false) || ($provider && (bool) $provider->is_featured),
            verified: $provider ? (bool) $provider->is_verified : false,
            planLevel: $provider ? ($planLevels[$provider->id] ?? 0.0) : 0.0,
            startsAt: null, endsAt: null,
            createdAt: ! empty($r->created_at) ? strtotime((string) $r->created_at) : null,
            tags: $this->tag($type, $tf),
            tf: $tf, url: $o['url'], image: $image,
            excerpt: Str::limit(trim(strip_tags((string) ($o['body'][0] ?: ($o['body'][1] ?? '')))), 140),
            providerId: $provider ? (int) $provider->id : null,
            extra: ($o['extra'] ?? []) + ['geo_exact' => $geoExact],
        );
    }

    /** Centres d'intérêt reconnus dans la fiche (au moins un mot-clé, ou a priori du type). */
    private function tag(string $type, array $tf): array
    {
        $scores = [];
        foreach (Taxonomy::INTERESTS as $key => $def) {
            $hits = 0;
            foreach ($def['keywords'] as $kw) {
                $stem = strlen($kw) > 4 && str_ends_with($kw, 's') ? substr($kw, 0, -1) : $kw;
                $hits += $tf[$stem] ?? 0;
            }
            if ($hits >= 2) {
                $scores[$key] = $hits;
            }
        }
        foreach (Taxonomy::TYPE_PRIORS[$type] ?? [] as $prior) {
            $scores[$prior] = ($scores[$prior] ?? 0) + 3;
        }
        arsort($scores);

        return array_slice(array_keys($scores), 0, 3);
    }

    /** @return array<int,float> provider_id => niveau de forfait actif */
    private function planLevels(): array
    {
        $levels = ['bronze' => 0.35, 'silver' => 0.65, 'gold' => 1.0];
        $out = [];
        $rows = DB::table('subscriptions')->join('subscription_plans', 'subscription_plans.id', '=', 'subscriptions.plan_id')
            ->where('subscriptions.status', 'active')->where('subscriptions.ends_at', '>', now())
            ->get(['subscriptions.provider_id', 'subscription_plans.code']);
        foreach ($rows as $row) {
            $out[$row->provider_id] = max($out[$row->provider_id] ?? 0, $levels[$row->code] ?? 0.35);
        }

        return $out;
    }

    private function parseFee(?string $fee): ?int
    {
        if ($fee === null || trim($fee) === '') {
            return null;
        }
        if (preg_match('/(\d[\d\s.]*)/', $fee, $m)) {
            return (int) preg_replace('/\D/', '', $m[1]);
        }

        return preg_match('/gratuit/i', $fee) ? 0 : null;
    }

    private function flat(mixed $v): array
    {
        $out = [];
        $v = (array) $v;
        array_walk_recursive($v, function ($x) use (&$out) {
            if (is_scalar($x)) {
                $out[] = (string) $x;
            }
        });

        return $out;
    }
}
