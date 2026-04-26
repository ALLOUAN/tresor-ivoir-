<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('q');
        $catSlug = $request->get('categorie');
        $city = $request->get('ville');
        $period = $request->get('periode', 'upcoming'); // upcoming | past | all

        $query = Event::with(['category', 'provider'])
            ->where('status', 'published');

        if ($period === 'upcoming') {
            $query->where('starts_at', '>=', now())->orderBy('starts_at');
        } elseif ($period === 'past') {
            $query->where('starts_at', '<', now())->orderByDesc('starts_at');
        } else {
            $query->orderByDesc('starts_at');
        }

        if ($search) {
            $query->where(fn ($q) => $q
                ->where('title_fr', 'like', "%{$search}%")
                ->orWhere('description_fr', 'like', "%{$search}%")
                ->orWhere('city', 'like', "%{$search}%")
            );
        }

        $activeCategory = null;
        if ($catSlug) {
            $activeCategory = EventCategory::where('slug', $catSlug)->first();
            if ($activeCategory) {
                $query->where('category_id', $activeCategory->id);
            }
        }

        if ($city) {
            $query->where('city', $city);
        }

        $events = $query->paginate(12)->withQueryString();
        $categories = EventCategory::withCount(['events' => fn ($q) => $q->where('status', 'published')])->orderBy('sort_order')->get();
        $cities = Event::where('status', 'published')->whereNotNull('city')->distinct()->orderBy('city')->pluck('city');

        $upcoming = Event::where('status', 'published')
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at')
            ->limit(3)
            ->get();

        return view('events.index', compact('events', 'categories', 'activeCategory', 'cities', 'search', 'city', 'period', 'upcoming'));
    }

    public function show(string $slug)
    {
        $event = Event::with(['category', 'creator', 'provider'])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        $event->increment('views_count');

        $related = Event::with('category')
            ->where('status', 'published')
            ->where('id', '!=', $event->id)
            ->where(fn ($q) => $q
                ->where('category_id', $event->category_id)
                ->orWhere('city', $event->city)
            )
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at')
            ->limit(3)
            ->get();

        $isFavorited = Auth::check() && Auth::user()->role === 'visitor'
            ? Auth::user()->favorites()
                ->where('favoritable_type', Event::class)
                ->where('favoritable_id', $event->id)
                ->exists()
            : false;

        return view('events.show', compact('event', 'related', 'isFavorited'));
    }
}
