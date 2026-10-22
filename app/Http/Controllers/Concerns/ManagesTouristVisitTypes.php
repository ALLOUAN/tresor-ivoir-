<?php

namespace App\Http\Controllers\Concerns;

use App\Models\TouristExperience;
use App\Models\TouristVisitType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Gestion des types de visite d'un site touristique, partagée entre l'espace prestataire
 * et l'administration. Chaque contrôleur fournit le site concerné et ses noms de routes.
 */
trait ManagesTouristVisitTypes
{
    /**
     * Noms de routes et paramètres pour les vues : ['index' => …, 'create' => …, 'store' => …,
     * 'edit' => …, 'update' => …, 'destroy' => …, 'toggle' => …, 'params' => [...], 'back' => url|null].
     *
     * @return array<string, mixed>
     */
    abstract protected function visitTypeRoutes(TouristExperience $experience): array;

    protected function renderVisitTypeIndex(TouristExperience $experience): View
    {
        return view('tourist-visit-types.index', [
            'experience' => $experience,
            'types' => $experience->visitTypes()->withCount('visits')->get(),
            'routes' => $this->visitTypeRoutes($experience),
        ]);
    }

    protected function renderVisitTypeForm(TouristExperience $experience, TouristVisitType $type): View
    {
        return view('tourist-visit-types.form', [
            'experience' => $experience,
            'type' => $type,
            'routes' => $this->visitTypeRoutes($experience),
        ]);
    }

    protected function storeVisitType(Request $request, TouristExperience $experience): RedirectResponse
    {
        $data = $this->validatedVisitType($request);
        $data['sort_order'] ??= (int) $experience->visitTypes()->max('sort_order') + 1;
        $experience->visitTypes()->create($data);

        return $this->backToVisitTypes($experience, __('Type de visite créé.'));
    }

    protected function updateVisitType(Request $request, TouristExperience $experience, TouristVisitType $type): RedirectResponse
    {
        $this->ensureVisitTypeBelongs($experience, $type);
        $type->update($this->validatedVisitType($request));

        return $this->backToVisitTypes($experience, __('Type de visite mis à jour.'));
    }

    protected function toggleVisitType(TouristExperience $experience, TouristVisitType $type): RedirectResponse
    {
        $this->ensureVisitTypeBelongs($experience, $type);
        $type->update(['is_active' => ! $type->is_active]);

        return $this->backToVisitTypes($experience, $type->is_active ? __('Type de visite activé.') : __('Type de visite désactivé.'));
    }

    protected function destroyVisitType(TouristExperience $experience, TouristVisitType $type): RedirectResponse
    {
        $this->ensureVisitTypeBelongs($experience, $type);
        // Les visites déjà réservées gardent le nom du type (visit_type_name) : rien n'est perdu.
        $type->delete();

        return $this->backToVisitTypes($experience, __('Type de visite supprimé.'));
    }

    protected function ensureVisitTypeBelongs(TouristExperience $experience, TouristVisitType $type): void
    {
        abort_unless((int) $type->tourist_experience_id === (int) $experience->id, 403);
    }

    private function backToVisitTypes(TouristExperience $experience, string $message): RedirectResponse
    {
        $routes = $this->visitTypeRoutes($experience);

        return redirect()->route($routes['index'], $routes['params'])->with('success', $message);
    }

    /** @return array<string, mixed> */
    private function validatedVisitType(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
            'icon' => ['nullable', 'in:'.implode(',', array_keys(TouristVisitType::ICONS))],
            'price_xof' => ['nullable', 'integer', 'min:0', 'max:100000000'],
            'guide_supplement_xof' => ['nullable', 'integer', 'min:0', 'max:100000000'],
            'duration_minutes' => ['nullable', 'integer', 'min:1', 'max:10080'],
            'max_participants' => ['nullable', 'integer', 'min:1', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            // Tarifs par catégorie de visiteur
            'tiers' => ['nullable', 'array', 'max:8'],
            'tiers.*.label' => ['nullable', 'string', 'max:60'],
            'tiers.*.price_xof' => ['nullable', 'integer', 'min:0', 'max:100000000'],
            // Créneaux et places
            'slot_times_text' => ['nullable', 'string', 'max:600'],
            'slot_capacity' => ['nullable', 'integer', 'min:1', 'max:5000'],
            // Conditions
            'free_cancellation_hours' => ['nullable', 'integer', 'in:0,2,6,12,24,48,72,168'],
            'languages' => ['nullable', 'array', 'max:15'],
            'languages.*' => ['string', 'max:40'],
            'languages_other' => ['nullable', 'string', 'max:200'],
            'included' => ['nullable', 'string', 'max:1500'],
            'not_included' => ['nullable', 'string', 'max:1500'],
            'meeting_point' => ['nullable', 'string', 'max:255'],
            'accessibility' => ['nullable', 'string', 'max:255'],
            'to_bring' => ['nullable', 'string', 'max:1500'],
            // Tarification dynamique
            'weekend_adjust_percent' => ['nullable', 'integer', 'min:-50', 'max:100'],
            'offpeak_until' => ['nullable', 'date_format:H:i'],
            'offpeak_discount_percent' => ['nullable', 'integer', 'min:1', 'max:90'],
            // Options payantes
            'addons' => ['nullable', 'array', 'max:12'],
            'addons.*.label' => ['nullable', 'string', 'max:80'],
            'addons.*.price_xof' => ['nullable', 'integer', 'min:0', 'max:100000000'],
            'addons.*.per' => ['nullable', 'in:person,booking'],
        ], [
            'name.required' => __('Le nom du type de visite est obligatoire.'),
            'offpeak_until.date_format' => __('L\'heure des heures creuses doit être au format HH:MM.'),
        ]);

        // Lignes vides ignorées ; une ligne sans prix est refusée plutôt que mise à 0 en silence.
        $rows = fn (string $key) => collect($data[$key] ?? [])->filter(fn ($r) => trim((string) ($r['label'] ?? '')) !== '')->values();
        foreach (['tiers' => __('tarif'), 'addons' => __('option')] as $key => $what) {
            if ($rows($key)->contains(fn ($r) => ($r['price_xof'] ?? null) === null)) {
                throw \Illuminate\Validation\ValidationException::withMessages([$key => __('Indiquez le prix de chaque :what (0 pour gratuit).', ['what' => $what])]);
            }
        }
        $data['price_tiers'] = $rows('tiers')->map(fn ($r) => ['label' => trim($r['label']), 'price_xof' => (int) $r['price_xof']])->all() ?: null;
        unset($data['tiers']);
        // Avec une grille, le prix de base devient le premier tarif (affichage « à partir de » et anciens écrans).
        if ($data['price_tiers']) {
            $data['price_xof'] = $data['price_tiers'][0]['price_xof'];
        }
        $data['addons'] = $rows('addons')->map(fn ($r) => ['label' => trim($r['label']), 'price_xof' => (int) $r['price_xof'], 'per' => ($r['per'] ?? 'person') === 'booking' ? 'booking' : 'person'])->all() ?: null;

        $slotText = (string) ($data['slot_times_text'] ?? '');
        unset($data['slot_times_text']);
        $tokens = collect(preg_split('/[\s,;]+/', $slotText))->filter();
        $bad = $tokens->reject(fn ($t) => TouristVisitType::normalizeTime($t));
        if ($bad->isNotEmpty()) {
            throw \Illuminate\Validation\ValidationException::withMessages(['slot_times_text' => __('Créneau invalide : :t (format attendu 09:00).', ['t' => $bad->first()])]);
        }
        $data['slot_times'] = $tokens->map(fn ($t) => TouristVisitType::normalizeTime($t))->unique()->sort()->values()->all() ?: null;

        $languages = collect($data['languages'] ?? [])
            ->merge(preg_split('/\s*,\s*/', (string) ($data['languages_other'] ?? '')))
            ->map(fn ($l) => trim((string) $l))->filter()->unique()->take(15)->values()->all();
        $data['languages'] = $languages ?: null;
        unset($data['languages_other']);

        $data['allow_pay_on_site'] = $request->boolean('allow_pay_on_site');
        if (empty($data['offpeak_until']) || empty($data['offpeak_discount_percent'])) {
            $data['offpeak_until'] = $data['offpeak_discount_percent'] = null;
        }
        $data['weekend_adjust_percent'] = ($data['weekend_adjust_percent'] ?? 0) ?: null;

        $data['includes_guide'] = $request->boolean('includes_guide');
        $data['is_active'] = $request->boolean('is_active');
        if (! $data['includes_guide']) {
            $data['guide_supplement_xof'] = null;
        }
        if (($data['sort_order'] ?? null) === null) {
            unset($data['sort_order']);
        }

        return $data;
    }
}
