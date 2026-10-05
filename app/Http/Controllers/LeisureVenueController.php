<?php

namespace App\Http\Controllers;

use App\Models\LeisureVenue;
use App\Models\TouristCity;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Fiche riche « Loisirs & Culture » — pilote du système déjà éprouvé par
 * AccommodationController, sans les concepts de réservation/paiement
 * (pas de dates, pas de budget, pas d'acompte) : ce secteur propose une fiche
 * de présentation + les activités du prestataire (via Activity), avec contact
 * direct plutôt qu'un paiement en ligne.
 */
class LeisureVenueController extends Controller
{
    public function index(Request $request): View
    {
        $cityId = $request->get('ville');
        $sort = $request->get('tri', 'pertinence');

        $query = LeisureVenue::active()->with(['city', 'provider']);

        if ($cityId) {
            $query->forCity((int) $cityId);
        }

        $venues = (match ($sort) {
            'recent' => $query->orderByDesc('created_at'),
            'popularite' => $query->orderByDesc('views_count'),
            default => $query->orderByDesc('is_featured'),
        })->get();

        $cities = $cityId
            ? TouristCity::where('id', $cityId)->get()
            : TouristCity::active()->orderBy('name')->get();

        return view('leisure-venues.index', [
            'venues' => $venues,
            'cities' => $cities,
            'cityId' => $cityId,
            'sort' => $sort,
        ]);
    }

    public function show(string $slug): View
    {
        $venue = LeisureVenue::active()
            ->where('slug', $slug)
            ->with(['city', 'provider', 'media'])
            ->firstOrFail();

        $venue->incrementViews();

        $activitiesByCategory = collect();
        if ($venue->provider) {
            $activitiesByCategory = $venue->provider->activities()
                ->where('is_available', true)
                ->with('category')
                ->orderBy('sort_order')
                ->get()
                ->groupBy(fn ($activity) => $activity->category?->name_fr ?? 'Autres');
        }

        $reviews = $venue->provider
            ? $venue->provider->approvedReviews()->latest()->take(5)->get()
            : collect();

        $related = LeisureVenue::active()
            ->where('id', '!=', $venue->id)
            ->where('city_id', $venue->city_id)
            ->limit(3)
            ->get();

        return view('leisure-venues.show', compact('venue', 'activitiesByCategory', 'reviews', 'related'));
    }

    /**
     * Page dédiée listant toutes les activités proposées — seule source d'affichage de la
     * liste illustrée, afin de ne pas dupliquer ce design sur la fiche générale.
     */
    public function activities(string $slug): View
    {
        $venue = LeisureVenue::active()
            ->where('slug', $slug)
            ->firstOrFail();

        $activitiesByCategory = collect();
        if ($venue->provider) {
            $activitiesByCategory = $venue->provider->activities()
                ->where('is_available', true)
                ->with('category')
                ->orderBy('sort_order')
                ->get()
                ->groupBy(fn ($activity) => $activity->category?->name_fr ?? 'Autres');
        }

        return view('leisure-venues.activities', compact('venue', 'activitiesByCategory'));
    }
}
