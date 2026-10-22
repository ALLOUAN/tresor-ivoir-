<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TouristCity;
use App\Models\Video;
use App\Services\Ai\Catalog;
use App\Services\VideoSubmission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

/** Médiathèque — gestion des vidéos (ajout, validation des vidéos des prestataires, mise en avant). */
class VideoManagementController extends Controller
{
    public function __construct(private readonly VideoSubmission $submission) {}

    public function index(Request $request): View
    {
        $status = $request->string('statut')->toString();
        $status = in_array($status, [Video::STATUS_PENDING, Video::STATUS_PUBLISHED, Video::STATUS_REJECTED], true) ? $status : null;

        return view('admin.videos.index', [
            'videos' => Video::with(['city', 'provider'])
                ->when($status, fn ($q) => $q->where('status', $status))
                ->orderByRaw("status = 'pending' desc")->latest()->paginate(30)->withQueryString(),
            'status' => $status,
            'counts' => Video::selectRaw('status, count(*) n')->groupBy('status')->pluck('n', 'status'),
        ]);
    }

    public function create(): View
    {
        return view('admin.videos.form', ['video' => new Video(['category' => 'destinations', 'orientation' => 'portrait']), 'cities' => $this->cities(), 'linked' => []]);
    }

    public function store(Request $request): RedirectResponse
    {
        $video = $this->submission->fill(new Video, $request);
        $video->submitted_by = Auth::id();
        $video->is_featured = $request->boolean('is_featured');
        $video->sort_order = (int) $request->input('sort_order', 0);
        $video->status = $request->boolean('publish', true) ? Video::STATUS_PUBLISHED : Video::STATUS_PENDING;
        $video->published_at = $video->status === Video::STATUS_PUBLISHED ? now() : null;
        $video->save();

        return redirect()->route('admin.videos.index')->with('success', __('Vidéo ajoutée.'));
    }

    public function edit(Video $video): View
    {
        return view('admin.videos.form', ['video' => $video, 'cities' => $this->cities(), 'linked' => $this->linked($video)]);
    }

    public function update(Request $request, Video $video): RedirectResponse
    {
        $this->submission->fill($video, $request);
        $video->is_featured = $request->boolean('is_featured');
        $video->sort_order = (int) $request->input('sort_order', 0);
        $video->save();

        return redirect()->route('admin.videos.index')->with('success', __('Vidéo enregistrée.'));
    }

    public function approve(Video $video): RedirectResponse
    {
        $video->update(['status' => Video::STATUS_PUBLISHED, 'rejection_reason' => null, 'published_at' => $video->published_at ?? now()]);

        return back()->with('success', __('Vidéo publiée.'));
    }

    public function reject(Request $request, Video $video): RedirectResponse
    {
        $data = $request->validate(['reason' => ['nullable', 'string', 'max:500']]);
        $video->update(['status' => Video::STATUS_REJECTED, 'rejection_reason' => $data['reason'] ?? null]);

        return back()->with('success', __('Vidéo refusée.'));
    }

    public function destroy(Video $video): RedirectResponse
    {
        $this->submission->deleteFiles($video);
        $video->delete();

        return back()->with('success', __('Vidéo supprimée.'));
    }

    /** Recherche de fiches à relier (« Voir et réserver »). */
    public function searchListings(Request $request, Catalog $catalog): JsonResponse
    {
        $q = Str::lower(Str::ascii(trim((string) $request->query('q'))));
        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }
        $out = [];
        foreach ($catalog->items() as $item) {
            if ($item->type === 'provider') {
                continue;
            }
            if (str_contains(Str::lower(Str::ascii($item->title.' '.$item->city)), $q)) {
                $out[] = ['key' => $item->key, 'title' => $item->title, 'meta' => trim($item->typeLabel.' · '.$item->city, ' ·')];
                if (count($out) >= 12) {
                    break;
                }
            }
        }

        return response()->json($out);
    }

    private function cities()
    {
        return TouristCity::where('is_active', true)->orderBy('sort_order')->get(['id', 'name']);
    }

    /** @return list<array{key: string, title: string, meta: string}> */
    private function linked(Video $video): array
    {
        $catalog = app(Catalog::class);
        $out = [];
        foreach ((array) $video->links as $key) {
            if ($item = $catalog->get($key)) {
                $out[] = ['key' => $key, 'title' => $item->title, 'meta' => trim($item->typeLabel.' · '.$item->city, ' ·')];
            }
        }

        return $out;
    }
}
