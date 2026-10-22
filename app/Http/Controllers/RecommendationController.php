<?php

namespace App\Http\Controllers;

use App\Services\Ai\InteractionStore;
use App\Services\Ai\ProfileStore;
use App\Services\Ai\Recommendations;
use App\Services\Ai\Taxonomy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

/** Module 1 : page « Pour vous », questionnaire de démarrage et export JSON (application mobile). */
class RecommendationController extends Controller
{
    public function __construct(
        private readonly Recommendations $reco,
        private readonly ProfileStore $profiles,
        private readonly InteractionStore $interactions,
    ) {}

    public function index(Request $request): View
    {
        abort_unless(Recommendations::enabled(), 404);
        $data = $this->reco->forRequest($request, 12);

        return view('ai.for-you', $data + [
            'interests' => Taxonomy::INTERESTS,
            'budgets' => Taxonomy::BUDGETS,
            'travelTypes' => Taxonomy::TRAVEL_TYPES,
            'cities' => $this->reco->catalog()->cities(),
            'hasHistory' => (bool) $this->interactions->history($this->interactions->actorKey($request)),
        ]);
    }

    public function saveProfile(Request $request): RedirectResponse
    {
        abort_unless(Recommendations::enabled(), 404);
        $data = $request->validate([
            'interests' => ['nullable', 'array'],
            'interests.*' => ['string'],
            'budget' => ['nullable', 'string'],
            'travel_type' => ['nullable', 'string'],
            'home_city_id' => ['nullable', 'integer'],
        ]);
        $this->profiles->save($request, $data);

        return redirect()->route('ai.for-you')->with('status', __('Vos préférences sont enregistrées : vos recommandations sont mises à jour.'));
    }

    public function clear(Request $request): RedirectResponse
    {
        abort_unless(Recommendations::enabled(), 404);
        $this->profiles->clear($request);
        $this->interactions->clear($this->interactions->actorKey($request));
        Cache::forget('ai.matrix');

        return redirect()->route('ai.for-you')->with('status', __('Vos données de personnalisation ont été effacées.'));
    }

    /** Même moteur pour le site et l'application mobile. */
    public function json(Request $request): JsonResponse
    {
        abort_unless(Recommendations::enabled(), 404);
        $k = max(1, min(24, (int) $request->query('k', 8)));
        $data = $this->reco->forRequest($request, $k);

        return response()->json([
            'personalized' => ! $data['cold'],
            'collaborative_share' => round($data['alpha'], 2),
            'items' => array_map(fn ($r) => [
                'key' => $r['item']->key, 'type' => $r['item']->type, 'type_label' => $r['item']->typeLabel,
                'title' => $r['item']->title, 'city' => $r['item']->city, 'url' => $r['item']->url, 'image' => $r['item']->image,
                'price_xof' => $r['item']->price, 'rating' => $r['item']->rating,
                'score' => round($r['score'], 3), 'reasons' => $r['reasons'],
            ], $data['recommendations']),
        ]);
    }
}
