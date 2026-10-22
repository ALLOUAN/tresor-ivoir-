<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use App\Models\TouristCity;
use App\Models\Video;
use App\Services\Ai\Catalog;
use App\Services\VideoSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/** Espace prestataire — mes vidéos : proposées à la médiathèque, publiées après validation. */
class VideoController extends Controller
{
    public function __construct(private readonly VideoSubmission $submission) {}

    private function provider(): Provider
    {
        $provider = Provider::forUserQuery(Auth::id())->first();
        abort_unless($provider, 404, 'Aucune fiche prestataire trouvée.');

        return $provider;
    }

    /** Fiches du prestataire que ses vidéos peuvent mettre en avant. */
    private function ownListings(Provider $provider): array
    {
        $out = [];
        foreach (app(Catalog::class)->items() as $item) {
            if ($item->providerId === $provider->id && $item->type !== 'provider') {
                $out[$item->key] = $item->title.' · '.$item->typeLabel;
            }
        }

        return $out;
    }

    public function index(): View
    {
        $provider = $this->provider();

        return view('provider.videos.index', [
            'videos' => Video::where('provider_id', $provider->id)->latest()->get(),
        ]);
    }

    public function create(): View
    {
        $provider = $this->provider();

        return view('provider.videos.create', [
            'video' => new Video(['category' => 'destinations', 'orientation' => 'portrait', 'city_id' => $provider->city_id ?? null]),
            'cities' => TouristCity::where('is_active', true)->orderBy('sort_order')->get(['id', 'name']),
            'listings' => $this->ownListings($provider),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $provider = $this->provider();
        $video = $this->submission->fill(new Video, $request, array_keys($this->ownListings($provider)));
        $video->provider_id = $provider->id;
        $video->submitted_by = Auth::id();
        $video->status = Video::STATUS_PENDING;
        $video->save();

        return redirect()->route('provider.videos.index')->with('success', __('Vidéo envoyée : elle sera publiée dans la médiathèque après validation.'));
    }

    public function destroy(Video $video): RedirectResponse
    {
        abort_unless($video->provider_id === $this->provider()->id, 403);
        $this->submission->deleteFiles($video);
        $video->delete();

        return back()->with('success', __('Vidéo supprimée.'));
    }
}
