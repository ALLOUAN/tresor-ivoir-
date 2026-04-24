<?php

namespace App\Http\Controllers\Editor;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $status = $request->get('status');

        $query = Event::with(['category', 'creator'])->latest();

        if (! $user->isAdmin()) {
            $query->where('created_by', $user->id);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $events = $query->paginate(15)->withQueryString();

        $base = $user->isAdmin() ? Event::query() : Event::where('created_by', $user->id);
        $counts = [
            'all' => (clone $base)->count(),
            'draft' => (clone $base)->where('status', 'draft')->count(),
            'published' => (clone $base)->where('status', 'published')->count(),
            'cancelled' => (clone $base)->where('status', 'cancelled')->count(),
            'past' => (clone $base)->where('status', 'past')->count(),
        ];

        return view('editor.events.index', compact('events', 'counts', 'status'));
    }

    public function create()
    {
        $categories = EventCategory::orderBy('sort_order')->get();

        return view('editor.events.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title_fr' => 'required|string|max:255',
            'title_en' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:300|unique:events,slug',
            'category_id' => 'required|exists:event_categories,id',
            'description_fr' => 'nullable|string',
            'description_en' => 'nullable|string',
            'cover_url' => 'nullable|url|max:500',
            'starts_at' => 'required|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'is_free' => 'boolean',
            'price' => 'nullable|numeric|min:0',
            'ticket_url' => 'nullable|url|max:500',
            'location_name' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:150',
            'organizer_name' => 'nullable|string|max:255',
            'organizer_phone' => 'nullable|string|max:20',
            'status' => 'required|in:draft,published,cancelled',
            'meta_title_fr' => 'nullable|string|max:70',
            'meta_desc_fr' => 'nullable|string|max:165',
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title_fr']);
        }

        $data['created_by'] = Auth::id();
        $data['is_free'] = $request->boolean('is_free');
        $data['is_recurring'] = false;

        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        Event::create($data);

        return redirect()->route('editor.events.index')->with('success', 'Événement créé.');
    }

    public function edit(Event $event)
    {
        $this->authorizeEdit($event);
        $categories = EventCategory::orderBy('sort_order')->get();

        return view('editor.events.edit', compact('event', 'categories'));
    }

    public function update(Request $request, Event $event)
    {
        $this->authorizeEdit($event);

        $data = $request->validate([
            'title_fr' => 'required|string|max:255',
            'title_en' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:300|unique:events,slug,'.$event->id,
            'category_id' => 'required|exists:event_categories,id',
            'description_fr' => 'nullable|string',
            'description_en' => 'nullable|string',
            'cover_url' => 'nullable|url|max:500',
            'starts_at' => 'required|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'is_free' => 'boolean',
            'price' => 'nullable|numeric|min:0',
            'ticket_url' => 'nullable|url|max:500',
            'location_name' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:150',
            'organizer_name' => 'nullable|string|max:255',
            'organizer_phone' => 'nullable|string|max:20',
            'status' => 'required|in:draft,published,cancelled',
            'meta_title_fr' => 'nullable|string|max:70',
            'meta_desc_fr' => 'nullable|string|max:165',
        ]);

        $data['is_free'] = $request->boolean('is_free');

        if ($data['status'] === 'published' && ! $event->published_at) {
            $data['published_at'] = now();
        }

        $event->update($data);

        return redirect()->route('editor.events.index')->with('success', 'Événement mis à jour.');
    }

    public function destroy(Event $event)
    {
        $this->authorizeEdit($event);
        $event->delete();

        return back()->with('success', 'Événement supprimé.');
    }

    public function updateStatus(Request $request, Event $event)
    {
        $request->validate(['status' => 'required|in:draft,published,cancelled,past']);
        $this->authorizeEdit($event);

        $data = ['status' => $request->status];
        if ($request->status === 'published' && ! $event->published_at) {
            $data['published_at'] = now();
        }

        $event->update($data);

        return back()->with('success', 'Statut mis à jour.');
    }

    private function authorizeEdit(Event $event): void
    {
        $user = Auth::user();
        if (! $user->isAdmin() && $event->created_by !== $user->id) {
            abort(403, 'Accès refusé.');
        }
    }
}
