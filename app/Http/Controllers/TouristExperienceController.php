<?php

namespace App\Http\Controllers;

use App\Models\TouristCity;
use App\Models\TouristExperience;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Fiche riche « Sites Touristiques » (prestataires) — même patron que
 * LeisureVenueController, sans réservation/paiement : fiche de présentation +
 * les activités du prestataire (via Activity), avec contact direct plutôt
 * qu'un paiement en ligne. Sans rapport avec TouristSite (contenu touristique
 * édité en back-office, sans lien Provider).
 */
class TouristExperienceController extends Controller
{
    public function index(Request $request): View
    {
        $cityId = $request->get('ville');
        $sort = $request->get('tri', 'pertinence');

        $query = TouristExperience::active()->with(['city', 'provider']);

        if ($cityId) {
            $query->forCity((int) $cityId);
        }

        $experiences = (match ($sort) {
            'recent' => $query->orderByDesc('created_at'),
            'popularite' => $query->orderByDesc('views_count'),
            default => $query->orderByDesc('is_featured'),
        })->get();

        $cities = $cityId
            ? TouristCity::where('id', $cityId)->get()
            : TouristCity::active()->orderBy('name')->get();

        return view('tourist-experiences.index', [
            'experiences' => $experiences,
            'cities' => $cities,
            'cityId' => $cityId,
            'sort' => $sort,
        ]);
    }

    public function show(string $slug): View
    {
        $experience = TouristExperience::active()
            ->where('slug', $slug)
            ->with(['city', 'provider', 'media'])
            ->firstOrFail();

        $experience->incrementViews();

        $activitiesByCategory = collect();
        if ($experience->provider) {
            $activitiesByCategory = $experience->provider->activities()
                ->where('is_available', true)
                ->with('category')
                ->orderBy('sort_order')
                ->get()
                ->groupBy(fn ($activity) => $activity->category?->name_fr ?? 'Autres');
        }

        $reviews = $experience->provider
            ? $experience->provider->approvedReviews()->latest()->take(5)->get()
            : collect();

        $related = TouristExperience::active()
            ->where('id', '!=', $experience->id)
            ->where('city_id', $experience->city_id)
            ->limit(3)
            ->get();

        return view('tourist-experiences.show', compact('experience', 'activitiesByCategory', 'reviews', 'related'));
    }

    /**
     * Page dédiée listant toutes les activités proposées — seule source d'affichage de la
     * liste illustrée, afin de ne pas dupliquer ce design sur la fiche générale.
     */
    public function activities(string $slug): View
    {
        $experience = TouristExperience::active()
            ->where('slug', $slug)
            ->firstOrFail();

        $activitiesByCategory = collect();
        if ($experience->provider) {
            $activitiesByCategory = $experience->provider->activities()
                ->where('is_available', true)
                ->with('category')
                ->orderBy('sort_order')
                ->get()
                ->groupBy(fn ($activity) => $activity->category?->name_fr ?? 'Autres');
        }

        return view('tourist-experiences.activities', compact('experience', 'activitiesByCategory'));
    }
}
