<?php

namespace App\Http\Controllers;

use App\Models\Accommodation;
use App\Models\Reservation;
use App\Models\TouristCity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccommodationController extends Controller
{
    /** Tranches de budget (prix de départ, XOF/nuit) proposées à la recherche. */
    private const BUDGET_BRACKETS = [
        'low'    => [null, 50000],
        'mid'    => [50000, 100000],
        'high'   => [100000, 200000],
        'luxury' => [200000, null],
    ];

    public function index(Request $request): View
    {
        $region   = trim((string) $request->get('region', ''));
        $cityId   = $request->get('ville');
        $type     = trim((string) $request->get('type', ''));
        $checkIn  = $request->get('arrivee');
        $checkOut = $request->get('depart');
        $guests   = (int) $request->get('voyageurs', 0);
        $budget   = trim((string) $request->get('budget', ''));
        $sort     = $request->get('tri', 'pertinence');

        $query = Accommodation::active()->with(['city', 'media' => fn ($q) => $q->where('type', 'photo')->orderBy('sort_order')->limit(1)]);

        if ($region !== '') {
            $query->forRegion($region);
        }
        if ($cityId) {
            $query->forCity((int) $cityId);
        }
        if ($type !== '') {
            $query->ofType($type);
        }

        $accommodations = $query->get();

        // Filtre voyageurs : au moins une chambre pouvant accueillir le nombre demandé.
        if ($guests > 0) {
            $accommodations = $accommodations->filter(function (Accommodation $a) use ($guests) {
                return collect($a->room_types ?? [])->contains(function ($room) use ($guests) {
                    $capacity = (int) ($room['max_adults'] ?? 0) + (int) ($room['max_children'] ?? 0);
                    return $capacity >= $guests;
                });
            })->values();
        }

        // Filtre budget : sur le prix de départ (chambre la moins chère).
        if ($budget !== '' && isset(self::BUDGET_BRACKETS[$budget])) {
            [$min, $max] = self::BUDGET_BRACKETS[$budget];
            $accommodations = $accommodations->filter(function (Accommodation $a) use ($min, $max) {
                $price = $a->starting_price_xof;
                if ($price === null) {
                    return false;
                }
                return ($min === null || $price >= $min) && ($max === null || $price < $max);
            })->values();
        }

        // Filtre dates : exclure les hébergements dont toutes les chambres sont indisponibles.
        if ($checkIn && $checkOut) {
            $accommodations = $accommodations->filter(function (Accommodation $a) use ($checkIn, $checkOut) {
                $rooms = collect($a->room_types ?? []);
                if ($rooms->isEmpty()) {
                    return true;
                }
                return $rooms->contains(fn ($room) => ! Reservation::hasConflict(
                    $a->id,
                    $room['name'] ?? '',
                    $checkIn,
                    $checkOut
                ));
            })->values();
        }

        $accommodations = match ($sort) {
            'prix_asc'  => $accommodations->sortBy('starting_price_xof')->values(),
            'prix_desc' => $accommodations->sortByDesc('starting_price_xof')->values(),
            'popularite' => $accommodations->sortByDesc('views_count')->values(),
            default => $accommodations->sortByDesc('is_featured')->values(),
        };

        $regions = TouristCity::whereNotNull('region_administrative')
            ->where('region_administrative', '!=', '')
            ->distinct()
            ->orderBy('region_administrative')
            ->pluck('region_administrative');

        $cities = match (true) {
            (bool) $cityId => TouristCity::where('id', $cityId)->get(),
            $region !== '' => TouristCity::where('region_administrative', $region)->orderBy('name')->get(),
            default => TouristCity::active()->orderBy('name')->get(),
        };

        return view('accommodations.index', [
            'accommodations' => $accommodations,
            'regions'        => $regions,
            'cities'         => $cities,
            'region'         => $region,
            'cityId'         => $cityId,
            'type'           => $type,
            'checkIn'        => $checkIn,
            'checkOut'       => $checkOut,
            'guests'         => $guests,
            'budget'         => $budget,
            'sort'           => $sort,
        ]);
    }

    public function citiesForRegion(Request $request): JsonResponse
    {
        $region = trim((string) $request->get('region', ''));

        if ($region === '') {
            return response()->json(['cities' => []]);
        }

        $cities = TouristCity::where('region_administrative', $region)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json(['cities' => $cities]);
    }

    public function show(string $slug): View
    {
        $accommodation = Accommodation::active()
            ->where('slug', $slug)
            ->with(['city', 'provider', 'photos', 'videos'])
            ->firstOrFail();

        $accommodation->incrementViews();

        $reviews = $accommodation->provider
            ? $accommodation->provider->approvedReviews()->latest()->take(5)->get()
            : collect();

        $related = Accommodation::active()
            ->where('id', '!=', $accommodation->id)
            ->where('city_id', $accommodation->city_id)
            ->limit(3)
            ->get();

        return view('accommodations.show', compact('accommodation', 'reviews', 'related'));
    }

    /**
     * Page dédiée listant toutes les chambres d'un hébergement — seule source d'affichage
     * de la liste illustrée des chambres (photos, description, lits, conditions), afin de
     * ne pas dupliquer ce design sur la fiche générale de l'hébergement.
     */
    public function rooms(string $slug): View
    {
        $accommodation = Accommodation::active()
            ->where('slug', $slug)
            ->firstOrFail();

        return view('accommodations.rooms', compact('accommodation'));
    }
}
