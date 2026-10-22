<?php

namespace App\Http\Controllers;

use App\Services\Ai\Catalog;
use App\Services\Ai\CatalogItem;
use App\Services\Ai\Geo;
use App\Services\Maps\OpenNow;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * « Autour de moi, maintenant » : ce qui est proche et ouvert, trié par temps d'accès,
 * à partir des fiches du site (aucun appel payant à Google : distances calculées localement,
 * la carte n'utilise que la clé navigateur déjà en place).
 */
class NearbyController extends Controller
{
    /** Familles affichées : filtre => types du catalogue. */
    public const FILTERS = [
        'all' => [],
        'eat' => ['restaurant'],
        'sleep' => ['accommodation'],
        'visit' => ['site', 'experience', 'event'],
        'leisure' => ['leisure'],
        'move' => ['transport', 'agency'],
    ];

    private const WALK_KMH = 4.5;

    private const DRIVE_KMH = 25.0; // circulation urbaine (Abidjan)

    public function index(): View
    {
        return view('nearby.index', [
            'cities' => collect(app(Catalog::class)->cities())->filter(fn ($c) => ! empty($c['lat']) && ! empty($c['lng']))->values(),
        ]);
    }

    public function places(Request $request, Catalog $catalog): JsonResponse
    {
        $v = $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'radius' => ['nullable', 'numeric', 'min:0.5', 'max:100'],
            'filter' => ['nullable', 'in:'.implode(',', array_keys(self::FILTERS))],
            'open_now' => ['nullable', 'boolean'],
        ]);
        $lat = (float) $v['lat'];
        $lng = (float) $v['lng'];
        $radius = (float) ($v['radius'] ?? 5);
        $types = self::FILTERS[$v['filter'] ?? 'all'];
        $openOnly = $request->boolean('open_now');

        $now = time();
        $candidates = array_values(array_filter($catalog->items(), function (CatalogItem $i) use ($types, $now) {
            // Position exacte seulement : une distance calculée depuis le centre-ville serait trompeuse.
            if (! $i->hasGeo() || $i->type === 'provider' || ! ($i->extra['geo_exact'] ?? false)) return false;
            if ($types && ! in_array($i->type, $types, true)) return false;
            if ($i->type === 'event' && $i->endsAt && $i->endsAt < $now) return false;

            return true;
        }));

        $open = new OpenNow;
        $open->preload($candidates);

        $rows = [];
        foreach ($candidates as $i) {
            $km = Geo::km($lat, $lng, $i->lat, $i->lng);
            if ($km === null || $km > $radius) continue;
            $status = $open->status($i);
            if ($openOnly && $status['status'] === OpenNow::CLOSED) continue;
            $road = $km * 1.3;
            $rows[] = [
                'key' => $i->key,
                'type' => $i->type,
                'type_label' => __($i->typeLabel),
                'title' => $i->title,
                'category' => $i->category,
                'city' => $i->city,
                'lat' => $i->lat,
                'lng' => $i->lng,
                'km' => round($km, 2),
                'walk_min' => (int) max(1, round($road / self::WALK_KMH * 60)),
                'drive_min' => (int) max(1, round($road / self::DRIVE_KMH * 60)),
                'open' => $status['status'],
                'until' => $status['until'],
                'rating' => $i->ratingCount > 0 ? round($i->rating, 1) : null,
                'price' => $i->price,
                'verified' => $i->verified,
                'url' => $i->url,
                'image' => $i->image,
                'directions' => 'https://www.google.com/maps/dir/?'.http_build_query(['api' => 1, 'origin' => "$lat,$lng", 'destination' => "{$i->lat},{$i->lng}"]),
            ];
        }

        // Ouverts d'abord, puis les plus proches ; à distance égale, les mieux notés.
        $rank = [OpenNow::OPEN => 0, OpenNow::UNKNOWN => 1, OpenNow::CLOSED => 2];
        usort($rows, fn ($a, $b) => [$rank[$a['open']], $a['km'], -($a['rating'] ?? 0)] <=> [$rank[$b['open']], $b['km'], -($b['rating'] ?? 0)]);

        return response()->json([
            'success' => true,
            'count' => count($rows),
            'places' => array_slice($rows, 0, 60),
            'now' => now('Africa/Abidjan')->format('H:i'),
        ]);
    }
}
