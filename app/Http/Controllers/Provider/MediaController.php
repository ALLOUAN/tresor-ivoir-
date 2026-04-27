<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Event;
use App\Models\Media;
use App\Models\Provider;
use Illuminate\Support\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MediaController extends Controller
{
    private function getProvider(): Provider
    {
        $provider = Provider::query()->where('user_id', Auth::id())->first();
        if (! $provider) {
            abort(404, 'Aucune fiche prestataire trouvée.');
        }

        return $provider;
    }

    public function index(): View
    {
        $provider = $this->getProvider();

        $articles = Article::query()
            ->where('sponsor_id', $provider->id)
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->get(['id', 'title_fr', 'slug_fr', 'status', 'published_at', 'created_at']);

        $events = Event::query()
            ->where('provider_id', $provider->id)
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->get(['id', 'title_fr', 'slug', 'status', 'starts_at', 'published_at', 'created_at']);

        $totalPublications = $articles->count() + $events->count();
        $publishedCount = $articles->where('status', 'published')->count() + $events->where('status', 'published')->count();
        $publishedPhotos = $this->collectPublishedPhotos($provider, $articles, $events);

        return view('provider.media.index', compact('provider', 'articles', 'events', 'totalPublications', 'publishedCount', 'publishedPhotos'));
    }

    public function store(Request $request): RedirectResponse
    {
        $provider = $this->getProvider()->load('activeSubscription.plan');

        $request->validate([
            'file' => ['required', 'file', 'max:51200'],
            'caption' => ['nullable', 'string', 'max:500'],
            'alt_text' => ['nullable', 'string', 'max:300'],
        ]);

        $file = $request->file('file');
        $mime = (string) $file->getMimeType();
        $type = $this->resolveMediaType($mime);
        $plan = $provider->activeSubscription?->plan;

        if ($type === 'image' && $plan && $plan->photos_limit !== null) {
            $imagesCount = $provider->media()->where('type', 'image')->count();
            if ($imagesCount >= (int) $plan->photos_limit) {
                return back()->with('error', 'Limite de photos atteinte pour votre forfait.');
            }
        }

        if ($type === 'video' && $plan && ! $plan->has_video) {
            return back()->with('error', 'Votre forfait actuel ne permet pas l\'ajout de vidéos.');
        }

        $path = $file->store('providers/media', 'public');
        $url = '/storage/'.$path;

        $provider->media()->create([
            'collection' => 'provider-gallery',
            'type' => $type,
            'mime_type' => $mime,
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'url' => $url,
            'thumb_url' => null,
            'size_bytes' => (int) $file->getSize(),
            'width' => null,
            'height' => null,
            'duration_sec' => null,
            'alt_text' => $request->input('alt_text'),
            'caption' => $request->input('caption'),
            'sort_order' => 0,
            'uploaded_by' => (int) Auth::id(),
        ]);

        return back()->with('success', 'Média ajouté avec succès.');
    }

    public function destroy(Media $media): RedirectResponse
    {
        $provider = $this->getProvider();

        if ((int) $media->mediable_id !== (int) $provider->id || $media->mediable_type !== Provider::class) {
            abort(403);
        }

        if ($media->file_path && Storage::disk('public')->exists($media->file_path)) {
            Storage::disk('public')->delete($media->file_path);
        }

        $media->delete();

        return back()->with('success', 'Média supprimé.');
    }

    private function resolveMediaType(string $mime): string
    {
        if (str_starts_with($mime, 'image/')) {
            return 'image';
        }
        if (str_starts_with($mime, 'video/')) {
            return 'video';
        }

        return 'document';
    }

    private function collectPublishedPhotos(Provider $provider, Collection $articles, Collection $events): Collection
    {
        $providerImages = $provider->media()
            ->where('type', 'image')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Media $m) => [
                'url' => $m->url,
                'title' => $m->caption ?: $m->original_name ?: 'Photo de la fiche',
                'source' => 'Fiche prestataire',
                'date' => $m->created_at,
            ]);

        $articleCovers = $articles
            ->filter(fn ($a) => ! empty($a->cover_url))
            ->map(fn ($a) => [
                'url' => $a->cover_url,
                'title' => $a->title_fr ?: 'Article',
                'source' => 'Article',
                'date' => $a->published_at ?: $a->created_at,
            ]);

        $eventCovers = $events
            ->filter(fn ($e) => ! empty($e->cover_url))
            ->map(fn ($e) => [
                'url' => $e->cover_url,
                'title' => $e->title_fr ?: 'Événement',
                'source' => 'Événement',
                'date' => $e->published_at ?: $e->created_at,
            ]);

        return $providerImages
            ->concat($articleCovers)
            ->concat($eventCovers)
            ->filter(fn ($photo) => ! empty($photo['url']))
            ->unique('url')
            ->sortByDesc(fn ($photo) => $photo['date']?->timestamp ?? 0)
            ->values();
    }
}

