<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Artwork;
use App\Models\ArtworkCategory;
use App\Models\ArtworkOrder;
use App\Models\Provider;
use App\Models\Wallet;
use App\Services\ImageUploadSecurityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ArtworkController extends Controller
{
    public function __construct(
        private readonly ImageUploadSecurityService $imageUpload,
    ) {}

    private function getProvider(): Provider
    {
        $provider = Provider::query()->where('user_id', Auth::id())->first();
        if (! $provider) {
            abort(404, 'Aucune fiche prestataire trouvée.');
        }

        return $provider;
    }

    /** Page d'accueil de l'espace dédié Art & Créations. */
    public function dashboard(): View
    {
        $provider = $this->getProvider();

        $artworkCounts = [
            'published' => Artwork::where('provider_id', $provider->id)->where('status', Artwork::STATUS_PUBLISHED)->count(),
            'pending_review' => Artwork::where('provider_id', $provider->id)->where('status', Artwork::STATUS_PENDING_REVIEW)->count(),
            'sold' => Artwork::where('provider_id', $provider->id)->where('status', Artwork::STATUS_SOLD)->count(),
        ];

        $orderStats = [
            'to_ship' => ArtworkOrder::where('provider_id', $provider->id)->where('status', ArtworkOrder::STATUS_PAID)->count(),
            'shipped' => ArtworkOrder::where('provider_id', $provider->id)->where('status', ArtworkOrder::STATUS_SHIPPED)->count(),
        ];

        $wallet = Wallet::where('provider_id', $provider->id)->first();

        $recentOrders = ArtworkOrder::where('provider_id', $provider->id)
            ->with('artwork')
            ->latest()
            ->take(5)
            ->get();

        return view('provider.artworks.dashboard', compact('artworkCounts', 'orderStats', 'wallet', 'recentOrders'));
    }

    public function index(): View
    {
        $provider = $this->getProvider();

        $artworks = Artwork::where('provider_id', $provider->id)
            ->with('category')
            ->orderByDesc('id')
            ->paginate(15);

        return view('provider.artworks.index', compact('artworks'));
    }

    public function create(): View
    {
        $this->getProvider();

        $categories = ArtworkCategory::where('is_active', true)->orderBy('sort_order')->get();

        return view('provider.artworks.form', ['artwork' => new Artwork(), 'categories' => $categories]);
    }

    public function store(Request $request): RedirectResponse
    {
        $provider = $this->getProvider();

        $validated = $this->validated($request);

        $images = [];
        foreach ($this->uploadedImages($request) as $file) {
            $images[] = $this->imageUpload->store($file, 'artworks/'.$provider->uuid, 'oeuvre');
        }

        if (empty($images)) {
            return back()->withErrors(['images' => 'Ajoutez au moins une photo de l\'œuvre.'])->withInput();
        }

        Artwork::create([
            ...$validated,
            'provider_id' => $provider->id,
            'slug' => $this->uniqueSlug($validated['title']),
            'images' => $images,
            'status' => Artwork::STATUS_PENDING_REVIEW,
        ]);

        return redirect()->route('provider.artworks.index')->with('success', 'Œuvre soumise pour validation. Elle sera visible publiquement après approbation par l\'administration.');
    }

    public function edit(Artwork $artwork): View
    {
        $provider = $this->getProvider();
        abort_unless((int) $artwork->provider_id === (int) $provider->id, 403);

        $categories = ArtworkCategory::where('is_active', true)->orderBy('sort_order')->get();

        return view('provider.artworks.form', compact('artwork', 'categories'));
    }

    public function update(Request $request, Artwork $artwork): RedirectResponse
    {
        $provider = $this->getProvider();
        abort_unless((int) $artwork->provider_id === (int) $provider->id, 403);

        $validated = $this->validated($request);

        $images = $this->removeMarkedImages($request, $artwork->images ?? []);
        foreach ($this->uploadedImages($request) as $file) {
            $images[] = $this->imageUpload->store($file, 'artworks/'.$provider->uuid, 'oeuvre');
        }

        if (empty($images)) {
            return back()->withErrors(['images' => 'L\'œuvre doit conserver au moins une photo.'])->withInput();
        }

        $artwork->update([
            ...$validated,
            'images' => $images,
        ]);

        return redirect()->route('provider.artworks.index')->with('success', 'Œuvre mise à jour.');
    }

    public function destroy(Artwork $artwork): RedirectResponse
    {
        $provider = $this->getProvider();
        abort_unless((int) $artwork->provider_id === (int) $provider->id, 403);

        $artwork->delete();

        return redirect()->route('provider.artworks.index')->with('success', 'Œuvre retirée.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'integer', 'exists:artwork_categories,id'],
            'description' => ['nullable', 'string', 'max:5000'],
            'medium' => ['nullable', 'string', 'max:255'],
            'dimensions' => ['nullable', 'string', 'max:255'],
            'year_created' => ['nullable', 'integer', 'min:1900', 'max:'.(now()->year + 1)],
            'price_xof' => ['required', 'integer', 'min:1000'],
            'stock_quantity' => ['required', 'integer', 'min:0', 'max:9999'],
        ]);
    }

    /**
     * Retire de la liste les images cochées pour suppression (case "remove_images[]") et
     * supprime aussi les fichiers physiques correspondants du disque.
     *
     * @param  array<int, string>  $currentImages
     * @return array<int, string>
     */
    private function removeMarkedImages(Request $request, array $currentImages): array
    {
        $toRemove = array_filter((array) $request->input('remove_images', []));
        if (empty($toRemove)) {
            return $currentImages;
        }

        foreach ($toRemove as $url) {
            if (in_array($url, $currentImages, true)) {
                Storage::disk('public')->delete(ltrim(str_replace('/storage/', '', (string) $url), '/'));
            }
        }

        return array_values(array_diff($currentImages, $toRemove));
    }

    /** @return array<int, UploadedFile> */
    private function uploadedImages(Request $request): array
    {
        $files = $request->file('images', []);
        if (! is_array($files)) {
            return [];
        }

        return array_values(array_filter($files, function ($file) {
            if (! $file instanceof UploadedFile) {
                return false;
            }
            try {
                return $file->isValid() && $file->getError() === UPLOAD_ERR_OK;
            } catch (\ValueError) {
                return false;
            }
        }));
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $i = 1;
        while (Artwork::where('slug', $slug)->exists()) {
            $slug = $base.'-'.(++$i);
        }

        return $slug;
    }
}
