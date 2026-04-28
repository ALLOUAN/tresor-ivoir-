<?php

namespace App\Http\Controllers\Editor;

use App\Http\Controllers\Controller;
use App\Http\Requests\AutosaveEventRequest;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Provider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

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
        $providers = Provider::where('status', 'active')->orderBy('name')->get(['id', 'name']);

        return view('editor.events.create', compact('categories', 'providers'));
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
            'cover_url' => 'nullable|url|max:500|required_without:cover_image',
            'cover_alt' => 'nullable|string|max:300',
            'cover_image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:4096|required_without:cover_url',
            'starts_at' => 'required|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'provider_id' => 'nullable|exists:providers,id',
            'is_recurring' => 'boolean',
            'recurrence_rule' => 'nullable|string|max:255|required_if:is_recurring,1',
            'registration_deadline' => 'nullable|date|before_or_equal:starts_at',
            'timezone' => 'nullable|string|max:64',
            'capacity' => 'nullable|integer|min:1|max:1000000',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'is_free' => 'boolean',
            'price' => 'nullable|numeric|min:0',
            'ticket_url' => 'nullable|url|max:500',
            'location_name' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:150',
            'organizer_name' => 'nullable|string|max:255',
            'organizer_phone' => 'nullable|string|max:20',
            'organizer_email' => 'nullable|email|max:255',
            'status' => 'required|in:draft,published,cancelled',
            'meta_title_fr' => 'nullable|string|max:70',
            'meta_desc_fr' => 'nullable|string|max:165',
            'meta_title_en' => 'nullable|string|max:70',
            'meta_desc_en' => 'nullable|string|max:165',
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title_fr']);
        }

        if ($request->hasFile('cover_image')) {
            $data['cover_url'] = $this->storeEventCover($request->file('cover_image'));
        }

        unset($data['cover_image']);

        $data['created_by'] = Auth::id();
        $data['is_free'] = $request->boolean('is_free');
        $data['is_recurring'] = $request->boolean('is_recurring');
        if (! $data['is_recurring']) {
            $data['recurrence_rule'] = null;
        }
        if ($data['is_free']) {
            $data['price'] = 0;
        }

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
        $providers = Provider::where('status', 'active')->orderBy('name')->get(['id', 'name']);

        return view('editor.events.edit', compact('event', 'categories', 'providers'));
    }

    public function preview(Event $event): View
    {
        $this->authorizeEdit($event);
        $event->load(['category', 'creator', 'provider']);

        return view('editor.events.preview', compact('event'));
    }

    public function autosave(AutosaveEventRequest $request, Event $event): JsonResponse
    {
        $this->authorizeEdit($event);

        $validated = $request->validated();
        $patch = [];
        foreach ($validated as $key => $value) {
            if (! $request->exists($key)) {
                continue;
            }
            if ($key === 'title_fr' && is_string($value) && trim($value) === '') {
                continue;
            }
            $patch[$key] = $value;
        }

        if (isset($patch['slug']) && trim((string) $patch['slug']) === '') {
            unset($patch['slug']);
        }

        if (isset($patch['slug'])) {
            $patch['slug'] = $this->ensureUniqueEventSlug((string) $patch['slug'], $event->id);
        }

        if ($patch !== []) {
            $event->fill($patch);
        }

        if (! $event->is_recurring) {
            $event->recurrence_rule = null;
        }
        if ($event->is_free) {
            $event->price = 0;
        }
        if ($event->status === 'published' && empty($event->cover_url)) {
            throw ValidationException::withMessages([
                'cover_url' => 'Une image de couverture est obligatoire pour publier un événement.',
            ]);
        }

        if ($event->isDirty('status') && $event->status === 'published' && ! $event->published_at) {
            $event->published_at = now();
        }

        if ($event->isDirty()) {
            $event->save();
        }

        return response()->json([
            'ok' => true,
            'saved_at' => $event->fresh()->updated_at?->toIso8601String(),
        ]);
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
            'cover_alt' => 'nullable|string|max:300',
            'cover_image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:4096',
            'starts_at' => 'required|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'provider_id' => 'nullable|exists:providers,id',
            'is_recurring' => 'boolean',
            'recurrence_rule' => 'nullable|string|max:255|required_if:is_recurring,1',
            'registration_deadline' => 'nullable|date|before_or_equal:starts_at',
            'timezone' => 'nullable|string|max:64',
            'capacity' => 'nullable|integer|min:1|max:1000000',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'is_free' => 'boolean',
            'price' => 'nullable|numeric|min:0',
            'ticket_url' => 'nullable|url|max:500',
            'location_name' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:150',
            'organizer_name' => 'nullable|string|max:255',
            'organizer_phone' => 'nullable|string|max:20',
            'organizer_email' => 'nullable|email|max:255',
            'status' => 'required|in:draft,published,cancelled',
            'meta_title_fr' => 'nullable|string|max:70',
            'meta_desc_fr' => 'nullable|string|max:165',
            'meta_title_en' => 'nullable|string|max:70',
            'meta_desc_en' => 'nullable|string|max:165',
        ]);

        $data['is_free'] = $request->boolean('is_free');
        $data['is_recurring'] = $request->boolean('is_recurring');
        if (! $data['is_recurring']) {
            $data['recurrence_rule'] = null;
        }
        if ($data['is_free']) {
            $data['price'] = 0;
        }

        if ($request->hasFile('cover_image')) {
            $this->deleteStoredPublicFile($event->cover_url);
            $data['cover_url'] = $this->storeEventCover($request->file('cover_image'));
        }

        unset($data['cover_image']);

        $effectiveCoverUrl = $data['cover_url'] ?? $event->cover_url;
        if (empty($effectiveCoverUrl)) {
            throw ValidationException::withMessages([
                'cover_url' => 'Une image de couverture est obligatoire pour enregistrer un événement.',
            ]);
        }

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
        if ($request->status === 'published' && empty($event->cover_url)) {
            throw ValidationException::withMessages([
                'cover_url' => 'Ajoutez une image de couverture avant de publier cet événement.',
            ]);
        }
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

    private function ensureUniqueEventSlug(string $slug, int $ignoreId): string
    {
        $base = $slug;
        $n = 1;
        while (Event::where('slug', $slug)->where('id', '!=', $ignoreId)->exists()) {
            $slug = $base.'-'.$n;
            $n++;
        }

        return $slug;
    }

    private function storeEventCover(UploadedFile $file): string
    {
        $path = $file->store('events/covers', 'public');

        return '/storage/'.$path;
    }

    private function deleteStoredPublicFile(?string $storedUrl): void
    {
        if (! $storedUrl || ! str_starts_with($storedUrl, '/storage/')) {
            return;
        }

        $relative = ltrim(substr($storedUrl, strlen('/storage/')), '/');
        if ($relative !== '') {
            Storage::disk('public')->delete($relative);
        }
    }
}
