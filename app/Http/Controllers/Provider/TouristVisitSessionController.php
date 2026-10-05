<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use App\Models\TouristVisitSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/** CRUD des sessions de visite groupée — même gate que TouristExperienceController. */
class TouristVisitSessionController extends Controller
{
    private function getProvider(): Provider
    {
        $provider = Provider::query()->where('user_id', Auth::id())->first();
        if (! $provider) {
            abort(404, 'Aucune fiche prestataire trouvée.');
        }

        $category = $provider->category;
        $rootSlug = $category?->parent_id ? $category->parent?->slug : $category?->slug;
        abort_unless($rootSlug === 'sites-touristiques', 403, 'Cette fonctionnalité est réservée aux prestataires de la catégorie Sites Touristiques.');

        return $provider;
    }

    public function index(): View
    {
        $provider = $this->getProvider();
        $experience = $provider->touristExperience()->first();

        $sessions = $experience
            ? $experience->visitSessions()->orderByDesc('session_date')->paginate(15)
            : collect();

        return view('provider.tourist-experience.sessions.index', compact('experience', 'sessions'));
    }

    public function create(): View
    {
        $provider = $this->getProvider();
        $experience = $provider->touristExperience()->first();
        abort_unless($experience, 404, "Complétez d'abord la fiche établissement avant de créer une session.");

        return view('provider.tourist-experience.sessions.form', ['experience' => $experience, 'session' => new TouristVisitSession()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $provider = $this->getProvider();
        $experience = $provider->touristExperience()->first();
        abort_unless($experience, 404, "Complétez d'abord la fiche établissement avant de créer une session.");

        $validated = $this->validated($request);

        $experience->visitSessions()->create($validated);

        return redirect()->route('provider.tourist-experience.sessions.index')->with('success', 'Session de visite créée.');
    }

    public function edit(TouristVisitSession $session): View
    {
        $provider = $this->getProvider();
        $experience = $provider->touristExperience()->first();
        abort_unless($experience && (int) $session->tourist_experience_id === (int) $experience->id, 403);

        return view('provider.tourist-experience.sessions.form', ['experience' => $experience, 'session' => $session]);
    }

    public function update(Request $request, TouristVisitSession $session): RedirectResponse
    {
        $provider = $this->getProvider();
        $experience = $provider->touristExperience()->first();
        abort_unless($experience && (int) $session->tourist_experience_id === (int) $experience->id, 403);

        $validated = $this->validated($request);

        if ((int) $validated['capacity'] < $session->bookedSeats()) {
            return back()->withInput()->with('error', 'La capacité ne peut pas être inférieure au nombre de places déjà réservées ('.$session->bookedSeats().').');
        }

        $session->update($validated);

        return redirect()->route('provider.tourist-experience.sessions.index')->with('success', 'Session de visite mise à jour.');
    }

    public function destroy(TouristVisitSession $session): RedirectResponse
    {
        $provider = $this->getProvider();
        $experience = $provider->touristExperience()->first();
        abort_unless($experience && (int) $session->tourist_experience_id === (int) $experience->id, 403);

        // Suppression douce uniquement : l'historique des visites déjà réservées garde
        // son session_date/session_time_label figés (snapshot), pas de corruption possible.
        $session->delete();

        return redirect()->route('provider.tourist-experience.sessions.index')->with('success', 'Session de visite supprimée.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'session_date' => ['required', 'date', 'after_or_equal:today'],
            'period_label' => ['nullable', 'string', 'max:100'],
            'capacity' => ['required', 'integer', 'min:1', 'max:9999'],
            'price_per_person_xof' => ['nullable', 'integer', 'min:0'],
            'conditions' => ['nullable', 'string', 'max:2000'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        return $validated;
    }
}
