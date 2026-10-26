<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Concerns\ManagesTouristVisitTypes;
use App\Http\Controllers\Controller;
use App\Models\Provider;
use App\Models\TouristExperience;
use App\Models\TouristVisitType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/** Types de visite du site touristique du prestataire — même gate que TouristVisitSessionController. */
class TouristVisitTypeController extends Controller
{
    use ManagesTouristVisitTypes;

    private function experience(): TouristExperience
    {
        $provider = Provider::forUserQuery(Auth::id())->first();
        abort_unless($provider, 404, 'Aucune fiche prestataire trouvée.');

        abort_unless($provider->hasSector('sites-touristiques'), 403, 'Cette fonctionnalité est réservée aux prestataires de la catégorie Sites Touristiques.');

        $experience = $provider->touristExperience()->first();
        abort_unless($experience, 404, "Complétez d'abord la fiche établissement avant de créer des types de visite.");

        return $experience;
    }

    protected function visitTypeRoutes(TouristExperience $experience): array
    {
        return [
            'index' => 'provider.tourist-experience.visit-types.index',
            'create' => 'provider.tourist-experience.visit-types.create',
            'store' => 'provider.tourist-experience.visit-types.store',
            'edit' => 'provider.tourist-experience.visit-types.edit',
            'update' => 'provider.tourist-experience.visit-types.update',
            'toggle' => 'provider.tourist-experience.visit-types.toggle',
            'destroy' => 'provider.tourist-experience.visit-types.destroy',
            'params' => [],
            'back' => route('provider.tourist-experience.dashboard'),
            'sessions' => route('provider.tourist-experience.sessions.index'),
        ];
    }

    public function index(): View
    {
        return $this->renderVisitTypeIndex($this->experience());
    }

    public function create(): View
    {
        return $this->renderVisitTypeForm($this->experience(), new TouristVisitType(['is_active' => true]));
    }

    public function store(Request $request): RedirectResponse
    {
        return $this->storeVisitType($request, $this->experience());
    }

    public function edit(TouristVisitType $visitType): View
    {
        $experience = $this->experience();
        $this->ensureVisitTypeBelongs($experience, $visitType);

        return $this->renderVisitTypeForm($experience, $visitType);
    }

    public function update(Request $request, TouristVisitType $visitType): RedirectResponse
    {
        return $this->updateVisitType($request, $this->experience(), $visitType);
    }

    public function toggle(TouristVisitType $visitType): RedirectResponse
    {
        return $this->toggleVisitType($this->experience(), $visitType);
    }

    public function destroy(TouristVisitType $visitType): RedirectResponse
    {
        return $this->destroyVisitType($this->experience(), $visitType);
    }
}
