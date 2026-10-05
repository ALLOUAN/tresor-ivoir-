<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

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
        $event = Event::with(['category', 'creator', 'provider', 'media'])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        $event->increment('views_count');

        $photos = $event->media->where('type', 'image')->sortBy('sort_order')->values();
        $videos = $event->media->where('type', 'video')->sortBy('sort_order')->values();

        $related = Event::with('category')
            ->where('status', 'published')
            ->where('id', '!=', $event->id)
            ->where(fn ($q) => $q
                ->where('category_id', $event->category_id)
                ->orWhere('city', $event->city)
            )
            ->where('starts_at', '>=', now())
            ->orderByRaw('category_id = ? desc', [$event->category_id])
            ->orderBy('starts_at')
            ->limit(4)
            ->get();

        $isFavorited = Auth::check() && Auth::user()->role === 'visitor'
            ? Auth::user()->favorites()
                ->where('favoritable_type', Event::class)
                ->where('favoritable_id', $event->id)
                ->exists()
            : false;

        $metaTitle = $event->meta_title_fr ?: $event->title_fr;
        $metaDesc = $event->meta_desc_fr ?: Str::limit(strip_tags($event->description_fr ?? ''), 160);
        $canonicalUrl = route('events.show', $event->slug);

        $jsonLd = array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Event',
            'name' => $event->title_fr,
            'startDate' => $event->starts_at?->toIso8601String(),
            'endDate' => $event->ends_at?->toIso8601String(),
            'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
            'eventStatus' => 'https://schema.org/EventScheduled',
            'location' => array_filter([
                '@type' => 'Place',
                'name' => $event->location_name ?: $event->city,
                'address' => $event->address ?: $event->city,
                'geo' => ($event->latitude && $event->longitude) ? [
                    '@type' => 'GeoCoordinates',
                    'latitude' => (float) $event->latitude,
                    'longitude' => (float) $event->longitude,
                ] : null,
            ]),
            'image' => array_values(array_filter([$event->cover_url ? url($event->cover_url) : null])),
            'description' => $metaDesc,
            'offers' => $event->ticket_url ? array_filter([
                '@type' => 'Offer',
                'url' => $event->ticket_url,
                'price' => $event->is_free ? '0' : (string) $event->price,
                'priceCurrency' => 'XOF',
                'availability' => 'https://schema.org/InStock',
            ]) : null,
            'organizer' => $event->organizer_name ? [
                '@type' => 'Organization',
                'name' => $event->organizer_name,
            ] : null,
        ]);

        return view('events.show', compact(
            'event', 'related', 'isFavorited', 'photos', 'videos',
            'metaTitle', 'metaDesc', 'canonicalUrl', 'jsonLd'
        ));
    }

    public function downloadIcs(string $slug)
    {
        $event = Event::where('slug', $slug)->where('status', 'published')->firstOrFail();

        $escape = fn ($s) => addcslashes((string) $s, ",;\\");

        $dtStart = $event->starts_at->clone()->utc()->format('Ymd\THis\Z');
        $dtEnd = ($event->ends_at ?? $event->starts_at->clone()->addHours(2))->clone()->utc()->format('Ymd\THis\Z');

        $location = trim(($event->location_name ?? '') . ', ' . ($event->address ?? $event->city ?? ''), ', ');

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//TresorsIvoire//Events//FR',
            'CALSCALE:GREGORIAN',
            'BEGIN:VEVENT',
            'UID:' . $event->uuid . '@tresorsivoire',
            'DTSTAMP:' . now()->utc()->format('Ymd\THis\Z'),
            'DTSTART:' . $dtStart,
            'DTEND:' . $dtEnd,
            'SUMMARY:' . $escape($event->title_fr),
            'DESCRIPTION:' . $escape(Str::limit(strip_tags($event->description_fr ?? ''), 400)),
            'LOCATION:' . $escape($location),
            'URL:' . route('events.show', $event->slug),
            'END:VEVENT',
            'END:VCALENDAR',
        ];

        return response(implode("\r\n", $lines), 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . Str::slug($event->title_fr) . '.ics"',
        ]);
    }
}
