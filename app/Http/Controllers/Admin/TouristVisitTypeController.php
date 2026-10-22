<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ManagesTouristVisitTypes;
use App\Http\Controllers\Controller;
use App\Models\TouristExperience;
use App\Models\TouristVisitType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Types de visite d'un site touristique, gérés depuis l'administration. */
class TouristVisitTypeController extends Controller
{
    use ManagesTouristVisitTypes;

    protected function visitTypeRoutes(TouristExperience $experience): array
    {
        return [
            'index' => 'admin.tourist-experiences.visit-types.index',
            'create' => 'admin.tourist-experiences.visit-types.create',
            'store' => 'admin.tourist-experiences.visit-types.store',
            'edit' => 'admin.tourist-experiences.visit-types.edit',
            'update' => 'admin.tourist-experiences.visit-types.update',
            'toggle' => 'admin.tourist-experiences.visit-types.toggle',
            'destroy' => 'admin.tourist-experiences.visit-types.destroy',
            'params' => ['touristExperience' => $experience->id],
            'back' => route('admin.tourist-experiences.edit', $experience),
            'sessions' => null,
        ];
    }

    public function index(TouristExperience $touristExperience): View
    {
        return $this->renderVisitTypeIndex($touristExperience);
    }

    public function create(TouristExperience $touristExperience): View
    {
        return $this->renderVisitTypeForm($touristExperience, new TouristVisitType(['is_active' => true]));
    }

    public function store(Request $request, TouristExperience $touristExperience): RedirectResponse
    {
        return $this->storeVisitType($request, $touristExperience);
    }

    public function edit(TouristExperience $touristExperience, TouristVisitType $visitType): View
    {
        $this->ensureVisitTypeBelongs($touristExperience, $visitType);

        return $this->renderVisitTypeForm($touristExperience, $visitType);
    }

    public function update(Request $request, TouristExperience $touristExperience, TouristVisitType $visitType): RedirectResponse
    {
        return $this->updateVisitType($request, $touristExperience, $visitType);
    }

    public function toggle(TouristExperience $touristExperience, TouristVisitType $visitType): RedirectResponse
    {
        return $this->toggleVisitType($touristExperience, $visitType);
    }

    public function destroy(TouristExperience $touristExperience, TouristVisitType $visitType): RedirectResponse
    {
        return $this->destroyVisitType($touristExperience, $visitType);
    }
}
