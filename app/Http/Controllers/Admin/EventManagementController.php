<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EventManagementController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status');
        $search = $request->get('q');
        $category = $request->get('category');

        $query = Event::with(['creator', 'category'])->latest();

        if ($status) {
            $query->where('status', $status);
        }
        if ($category) {
            $query->where('category_id', $category);
        }
        if ($search) {
            $query->where('title_fr', 'like', "%{$search}%");
        }

        $events = $query->paginate(20)->withQueryString();
        $categories = EventCategory::orderBy('sort_order')->get();

        $counts = [
            'all' => Event::count(),
            'draft' => Event::where('status', 'draft')->count(),
            'published' => Event::where('status', 'published')->count(),
            'cancelled' => Event::where('status', 'cancelled')->count(),
            'past' => Event::where('status', 'past')->count(),
        ];

        return view('admin.events.index', compact('events', 'categories', 'counts', 'status', 'search', 'category'));
    }

    public function publish(Event $event)
    {
        $event->update(['status' => 'published', 'published_at' => $event->published_at ?? now()]);

        return back()->with('success', 'Événement publié.');
    }

    public function cancel(Event $event)
    {
        $event->update(['status' => 'cancelled']);

        return back()->with('success', 'Événement annulé.');
    }

    public function destroy(Event $event)
    {
        $event->delete();

        return back()->with('success', 'Événement supprimé.');
    }

    // ── CATÉGORIES ────────────────────────────────────────────────────────────

    public function categories()
    {
        $categories = EventCategory::withCount('events')->orderBy('sort_order')->get();

        return view('admin.events.categories', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        $data = $request->validate([
            'name_fr'    => 'required|string|max:150',
            'name_en'    => 'nullable|string|max:150',
            'icon'       => 'nullable|string|max:80',
            'color_hex'  => 'nullable|string|max:7',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $data['slug'] = Str::slug($data['name_fr']);

        EventCategory::create($data);

        return back()->with('success', "Catégorie « {$data['name_fr']} » créée.");
    }

    public function updateCategory(Request $request, EventCategory $category)
    {
        $data = $request->validate([
            'name_fr'    => 'required|string|max:150',
            'name_en'    => 'nullable|string|max:150',
            'icon'       => 'nullable|string|max:80',
            'color_hex'  => 'nullable|string|max:7',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $category->update($data);

        return back()->with('success', "Catégorie « {$category->name_fr} » mise à jour.");
    }

    public function destroyCategory(EventCategory $category)
    {
        $category->delete();

        return back()->with('success', 'Catégorie supprimée.');
    }
}
