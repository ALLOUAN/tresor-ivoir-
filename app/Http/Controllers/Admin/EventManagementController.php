<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventCategory;
use Illuminate\Http\Request;

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
}
