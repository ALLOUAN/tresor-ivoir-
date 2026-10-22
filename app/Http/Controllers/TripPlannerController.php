<?php

namespace App\Http\Controllers;

use App\Models\TripPlan;
use App\Services\Ai\Catalog;
use App\Services\Ai\ItineraryNarrator;
use App\Services\Ai\ItineraryPlanner;
use App\Services\Ai\ProfileStore;
use App\Services\Ai\Recommendations;
use App\Services\Ai\Taxonomy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

/** Module 2 : « Mon séjour », planificateur d'itinéraires culturels. */
class TripPlannerController extends Controller
{
    public function __construct(
        private readonly Catalog $catalog,
        private readonly ItineraryPlanner $planner,
        private readonly ItineraryNarrator $narrator,
        private readonly ProfileStore $profiles,
    ) {}

    public function create(Request $request): View
    {
        abort_unless(Recommendations::enabled(), 404);
        $profile = $this->profiles->get($request);
        $old = [
            'interests' => $profile['interests'], 'pace' => 'modere', 'days' => 3, 'travelers' => 2,
            'budget_xof' => match ($profile['budget']) {'eco' => 100000, 'confort' => 500000, default => 250000},
        ];

        return view('ai.trip-planner', [
            'cities' => $this->catalog->cities(),
            'interests' => Taxonomy::INTERESTS,
            'defaults' => $old,
            'maxDays' => config('ai.planner.max_days'),
            'maxCities' => config('ai.planner.max_cities'),
            'recent' => $request->user() ? TripPlan::where('user_id', $request->user()->id)->latest()->limit(5)->get() : collect(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(Recommendations::enabled(), 404);
        $data = $request->validate([
            'city_ids' => ['required', 'array', 'min:1'],
            'city_ids.*' => ['integer'],
            'start_city_id' => ['nullable', 'integer'],
            'days' => ['required', 'integer', 'min:1', 'max:'.config('ai.planner.max_days')],
            'start_date' => ['nullable', 'date'],
            'budget_xof' => ['required', 'integer', 'min:10000', 'max:100000000'],
            'travelers' => ['required', 'integer', 'min:1', 'max:12'],
            'interests' => ['nullable', 'array'],
            'interests.*' => ['string'],
            'pace' => ['required', 'in:calme,modere,soutenu'],
        ], [
            'city_ids.required' => 'Choisissez au moins une destination.',
            'budget_xof.min' => 'Le budget minimum est de 10 000 FCFA.',
        ]);

        $plan = $this->planner->plan($data);
        if (! $plan['ok']) {
            return back()->withInput()->withErrors(['city_ids' => $plan['warnings'][0] ?? 'Destination invalide.']);
        }
        $story = $this->narrator->narrate($plan);

        $trip = TripPlan::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $request->user()?->id,
            'params' => $plan['params'],
            'plan' => $plan,
            'narrative' => json_encode(['intro' => $story['intro'], 'days' => $story['days']], JSON_UNESCAPED_UNICODE),
            'engine' => $story['engine'],
        ]);

        return redirect()->route('trip.show', $trip->uuid);
    }

    public function show(Request $request, string $uuid): View|JsonResponse
    {
        abort_unless(Recommendations::enabled(), 404);
        $trip = TripPlan::where('uuid', $uuid)->firstOrFail();
        $story = json_decode((string) $trip->narrative, true) ?: ['intro' => '', 'days' => []];

        if ($request->wantsJson()) {
            return response()->json(['uuid' => $trip->uuid, 'plan' => $trip->plan, 'story' => $story, 'engine' => $trip->engine]);
        }

        return view('ai.trip-show', ['trip' => $trip, 'plan' => $trip->plan, 'story' => $story]);
    }

    /**
     * Trajet routier réel d'une journée : départ et retour à l'hébergement (ou à la première étape),
     * étapes réordonnées par Google pour réduire le temps de route. Sans clé Google ou en cas
     * d'échec, renvoie l'ordre du programme avec des temps estimés (« estimate »).
     */
    public function dayRoute(string $uuid, int $day, \App\Services\GoogleRoutesService $routes): JsonResponse
    {
        abort_unless(Recommendations::enabled(), 404);
        $trip = TripPlan::where('uuid', $uuid)->firstOrFail();
        $days = $trip->plan['days'] ?? [];
        $d = collect($days)->firstWhere('index', $day);
        abort_unless($d, 404);

        // Étapes géolocalisées de la journée (activités et repas), dans l'ordre du programme.
        $stops = collect($d['stops'] ?? [])
            ->filter(fn ($s) => isset($s['item']['lat'], $s['item']['lng']) && $s['item']['lat'] !== null)
            ->map(fn ($s) => ['title' => $s['title'], 'slot' => $s['slot'], 'url' => $s['item']['url'] ?? null, 'lat' => (float) $s['item']['lat'], 'lng' => (float) $s['item']['lng']])
            ->values();

        // Point de départ : l'hébergement de la veille, sinon celui du soir, sinon la première étape.
        $prev = collect($days)->firstWhere('index', $day - 1);
        $lodging = $prev['lodging']['item'] ?? $d['lodging']['item'] ?? null;
        $home = $lodging && isset($lodging['lat'], $lodging['lng'])
            ? ['title' => $lodging['title'], 'lat' => (float) $lodging['lat'], 'lng' => (float) $lodging['lng']]
            : ($stops->isNotEmpty() ? $stops->shift() : null);

        if (! $home || $stops->isEmpty()) {
            return response()->json(['success' => true, 'engine' => 'none', 'home' => $home, 'stops' => [], 'legs' => []]);
        }

        $google = $routes->optimizeDay([$home['lat'], $home['lng']], $stops->map(fn ($s) => [$s['lat'], $s['lng']])->all(), app()->getLocale());

        if ($google) {
            $ordered = collect($google['order'])->map(fn ($i) => $stops[$i])->values();
            $legs = collect($google['legs'])->map(fn ($l) => ['km' => round($l['distance_m'] / 1000, 1), 'minutes' => (int) round($l['duration_s'] / 60)])->all();
            $total = ['km' => round($google['distance_m'] / 1000, 1), 'minutes' => (int) round($google['duration_s'] / 60)];
            $polyline = $google['polyline'];
            $engine = 'google';
        } else {
            $ordered = $stops;
            $points = collect([$home])->merge($ordered)->push($home)->values();
            $legs = [];
            for ($i = 1; $i < $points->count(); $i++) {
                $km = \App\Services\Ai\Geo::roadKm($points[$i - 1]['lat'], $points[$i - 1]['lng'], $points[$i]['lat'], $points[$i]['lng']) ?? 0;
                $legs[] = ['km' => round($km, 1), 'minutes' => (int) round($km / 40 * 60)];
            }
            $total = ['km' => round(array_sum(array_column($legs, 'km')), 1), 'minutes' => array_sum(array_column($legs, 'minutes'))];
            $polyline = null;
            $engine = 'estimate';
        }

        $mapsUrl = \App\Support\GoogleMaps::multiStopUrl(
            collect([$home])->merge($ordered)->push($home)->map(fn ($p) => [$p['lat'], $p['lng']])->all()
        );

        return response()->json([
            'success' => true,
            'engine' => $engine,
            'home' => $home,
            'stops' => $ordered->all(),
            'legs' => $legs,
            'total' => $total,
            'polyline' => $polyline,
            'maps_url' => $mapsUrl,
            'reordered' => $engine === 'google' && $ordered->pluck('title')->all() !== $stops->pluck('title')->all(),
        ]);
    }
}
