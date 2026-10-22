<?php

namespace App\Http\Controllers;

use App\Models\Video;
use App\Models\VideoLike;
use App\Services\GalleryLikeService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/** Médiathèque — vidéos publiques : liste, lecteur plein écran, vues, j'aime, partages, clics « réserver ». */
class VideoController extends Controller
{
    public function __construct(private readonly GalleryLikeService $likes) {}

    public function index(Request $request, ?Video $video = null): View
    {
        $category = $request->string('rubrique')->toString();
        $category = array_key_exists($category, Video::CATEGORIES) ? $category : null;

        $videos = Video::published()->with('city')
            ->when($category, fn ($q) => $q->where('category', $category))
            ->orderByDesc('is_featured')->orderBy('sort_order')->orderByDesc('published_at')
            ->limit(120)->get();

        // Lien direct vers une vidéo (partage) : elle s'ouvre dans le lecteur, même hors rubrique.
        if ($video) {
            abort_unless($video->status === Video::STATUS_PUBLISHED, 404);
            if (! $videos->contains('id', $video->id)) {
                $videos->prepend($video->load('city'));
            }
        }

        $likerKey = $this->likes->resolveLikerKey($request);
        $liked = VideoLike::where('liker_key', $likerKey)->whereIn('video_id', $videos->pluck('id'))->pluck('video_id')->all();

        $counts = Video::published()->select('category', DB::raw('count(*) as n'))->groupBy('category')->pluck('n', 'category');

        return view('videos.index', [
            'videos' => $videos,
            'featured' => $videos->firstWhere('is_featured', true) ?? $videos->first(),
            'category' => $category,
            'counts' => $counts,
            'liked' => $liked,
            'openSlug' => $video?->slug,
        ]);
    }

    public function show(Request $request, Video $video): View
    {
        return $this->index($request, $video);
    }

    /** Vue : comptée une seule fois par session et par vidéo. */
    public function view(Request $request, Video $video): JsonResponse
    {
        abort_unless($video->status === Video::STATUS_PUBLISHED, 404);
        $seen = (array) $request->session()->get('videos_seen', []);
        if (! in_array($video->id, $seen, true)) {
            $video->increment('views_count');
            $seen[] = $video->id;
            $request->session()->put('videos_seen', array_slice($seen, -300));
        }

        return response()->json(['views' => $video->fresh()->views_count]);
    }

    public function like(Request $request, Video $video): JsonResponse
    {
        abort_unless($video->status === Video::STATUS_PUBLISHED, 404);
        $key = $this->likes->resolveLikerKey($request);
        $existing = VideoLike::where('video_id', $video->id)->where('liker_key', $key)->first();
        if ($existing) {
            $existing->delete();
            Video::whereKey($video->id)->where('likes_count', '>', 0)->decrement('likes_count');
            $liked = false;
        } else {
            try {
                VideoLike::create(['video_id' => $video->id, 'liker_key' => $key]);
                $video->increment('likes_count');
            } catch (QueryException) {
                // Double clic simultané : la contrainte unique a déjà enregistré le j'aime.
            }
            $liked = true;
        }

        return response()->json(['liked' => $liked, 'likes' => $video->fresh()->likes_count]);
    }

    public function share(Video $video): JsonResponse
    {
        abort_unless($video->status === Video::STATUS_PUBLISHED, 404);
        $video->increment('shares_count');

        return response()->json(['ok' => true]);
    }

    /** Clic sur « Voir et réserver » : mesure ce que chaque vidéo amène vers les fiches. */
    public function click(Video $video): JsonResponse
    {
        abort_unless($video->status === Video::STATUS_PUBLISHED, 404);
        $video->increment('booking_clicks_count');

        return response()->json(['ok' => true]);
    }
}
