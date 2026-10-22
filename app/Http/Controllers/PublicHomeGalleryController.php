<?php

namespace App\Http\Controllers;

use App\Models\SiteMediaItem;
use App\Services\GalleryLikeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class PublicHomeGalleryController extends Controller
{
    private const PER_PAGE = 24;

    public function __construct(
        private readonly GalleryLikeService $likes,
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        if (! Schema::hasTable('site_media_items')) {
            return view('gallery.public', [
                'galleryImages' => new LengthAwarePaginator([], 0, self::PER_PAGE),
                'likedMediaIds' => [],
            ]);
        }

        // Tri choisi par le visiteur : récentes (ordre éditorial), les plus aimées, les plus téléchargées.
        $sort = in_array($request->query('tri'), ['populaires', 'telecharges'], true) ? $request->query('tri') : 'recentes';
        $galleryImages = SiteMediaItem::query()
            ->forPublicHomeGallery()
            ->when($sort === 'populaires', fn ($q) => $q->orderByDesc('likes_count'))
            ->when($sort === 'telecharges', fn ($q) => $q->orderByDesc('downloads_count'))
            ->orderedForPublicGallery()
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        $likerKey = $this->likes->resolveLikerKey($request);
        $likedMediaIds = $this->likes->likedMediaIds($likerKey, $galleryImages->pluck('id'));

        // Défilement infini (page suivante demandée en AJAX) : ne renvoie que le
        // fragment de cartes à ajouter, pas la page complète.
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'html' => $galleryImages->getCollection()->isEmpty()
                    ? ''
                    : view('gallery.partials.cards', compact('galleryImages', 'likedMediaIds'))->render(),
                'has_more' => $galleryImages->hasMorePages(),
                'next_page' => $galleryImages->currentPage() + 1,
            ]);
        }

        // Mosaïque d'en-tête : images mises en avant d'abord, puis l'ordre éditorial.
        $highlights = SiteMediaItem::query()->forPublicHomeGallery()
            ->orderByDesc('is_featured')->orderedForPublicGallery()->limit(3)->get();
        $totals = SiteMediaItem::query()->forPublicHomeGallery()
            ->selectRaw('COALESCE(SUM(likes_count), 0) as likes, COALESCE(SUM(downloads_count), 0) as downloads')->first();

        return view('gallery.public', compact('galleryImages', 'likedMediaIds', 'sort', 'highlights', 'totals'));
    }

    public function show(string $uuid): View
    {
        if (! Schema::hasTable('site_media_items')) {
            abort(404);
        }

        $media = SiteMediaItem::query()
            ->forPublicHomeGallery()
            ->where('uuid', $uuid)
            ->with('uploader:id,first_name,last_name')
            ->firstOrFail();

        $t = trim((string) ($media->title ?? ''));
        $pageTitle = $t !== '' ? $t : trim((string) ($media->original_name ?? 'Visuel'));

        // « Idées susceptibles de vous plaire » — autres visuels de la galerie,
        // pour continuer à naviguer sans revenir en arrière (logique Pinterest).
        $relatedImages = SiteMediaItem::query()
            ->forPublicHomeGallery()
            ->where('uuid', '!=', $uuid)
            ->inRandomOrder()
            ->limit(24)
            ->get();

        $likerKey = $this->likes->resolveLikerKey(request());
        $likedMediaIds = $this->likes->likedMediaIds(
            $likerKey,
            $relatedImages->pluck('id')->push($media->id)
        );

        return view('gallery.show', [
            'media' => $media,
            'pageTitle' => $pageTitle,
            'relatedImages' => $relatedImages,
            'likedMediaIds' => $likedMediaIds,
        ]);
    }
}
