<?php

namespace App\Http\Controllers\Api\V1\Events;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\EventDetailResource;
use App\Http\Resources\Api\V1\EventResource;
use App\Models\Event;
use App\Models\EventCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/** Événements publiés : mêmes règles que la page /evenements du site (filtres du modèle Event). */
class EventController extends Controller
{
    /**
     * GET /api/v1/events?periode=upcoming|past|all&categorie=slug&ville=Abidjan&q=texte&per_page=20&page=1
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->validate([
            'periode' => ['nullable', 'in:upcoming,past,all'],
            'categorie' => ['nullable', 'string', 'max:120'],
            'ville' => ['nullable', 'string', 'max:150'],
            'q' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $query = Event::query()
            ->with('category')
            ->where('status', 'published')
            ->forPeriod($filters['periode'] ?? 'upcoming');

        if (! empty($filters['q'])) {
            $query->search($filters['q']);
        }
        if (! empty($filters['categorie'])) {
            $query->whereIn('category_id', EventCategory::where('slug', $filters['categorie'])->select('id'));
        }
        if (! empty($filters['ville'])) {
            $query->where('city', $filters['ville']);
        }

        return EventResource::collection($query->paginate($filters['per_page'] ?? 20)->withQueryString());
    }

    /** GET /api/v1/events/{slug} */
    public function show(string $slug): EventDetailResource
    {
        $event = Event::query()
            ->with(['category', 'provider', 'media'])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        return EventDetailResource::make($event);
    }
}
