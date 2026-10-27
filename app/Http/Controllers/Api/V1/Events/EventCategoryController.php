<?php

namespace App\Http\Controllers\Api\V1\Events;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\EventCategoryResource;
use App\Models\EventCategory;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class EventCategoryController extends Controller
{
    /** GET /api/v1/event-categories : catégories avec le nombre d'événements à venir ou en cours. */
    public function index(): AnonymousResourceCollection
    {
        $categories = EventCategory::query()
            ->withCount(['events' => fn ($q) => $q->where('status', 'published')->notFinished()])
            ->orderBy('sort_order')
            ->get();

        return EventCategoryResource::collection($categories);
    }
}
