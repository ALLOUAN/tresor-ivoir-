<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use App\Models\TransportCategory;
use App\Models\TransportOffer;
use App\Services\ImageUploadSecurityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TransportController extends Controller
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

        $category = $provider->category;
        $rootSlug = $category?->parent_id ? $category->parent?->slug : $category?->slug;
        abort_unless($rootSlug === 'transports', 403, 'Cette fonctionnalité est réservée aux prestataires de la catégorie Transports & Mobilité.');

        return $provider;
    }

    /** Page d'accueil de l'espace dédié Transports & Mobilité. */
    public function dashboard(): View
    {
        $provider = $this->getProvider();

        $offerCounts = [
            'available' => TransportOffer::where('provider_id', $provider->id)->where('is_available', true)->count(),
            'unavailable' => TransportOffer::where('provider_id', $provider->id)->where('is_available', false)->count(),
        ];

        return view('provider.transport.dashboard', compact('offerCounts'));
    }

    public function index(): View
    {
        $provider = $this->getProvider();

        $offers = TransportOffer::where('provider_id', $provider->id)
            ->with('category')
            ->orderByDesc('id')
            ->paginate(15);

        return view('provider.transport.index', compact('offers'));
    }

    public function create(): View
    {
        $this->getProvider();

        $categories = TransportCategory::where('is_active', true)->orderBy('sort_order')->get();

        return view('provider.transport.form', ['offer' => new TransportOffer(), 'categories' => $categories]);
    }

    public function store(Request $request): RedirectResponse
    {
        $provider = $this->getProvider();

        $validated = $this->validated($request);

        $images = [];
        foreach ($this->uploadedImages($request) as $file) {
            $images[] = $this->imageUpload->store($file, 'transport/'.$provider->id, 'offre');
        }

        TransportOffer::create([
            ...$validated,
            'provider_id' => $provider->id,
            'images' => $images,
            'is_available' => $request->boolean('is_available', true),
        ]);

        return redirect()->route('provider.transport.index')->with('success', 'Offre ajoutée.');
    }

    public function edit(TransportOffer $offer): View
    {
        $provider = $this->getProvider();
        abort_unless((int) $offer->provider_id === (int) $provider->id, 403);

        $categories = TransportCategory::where('is_active', true)->orderBy('sort_order')->get();

        return view('provider.transport.form', ['offer' => $offer, 'categories' => $categories]);
    }

    public function update(Request $request, TransportOffer $offer): RedirectResponse
    {
        $provider = $this->getProvider();
        abort_unless((int) $offer->provider_id === (int) $provider->id, 403);

        $validated = $this->validated($request);

        $images = $this->removeMarkedImages($request, $offer->images ?? []);
        foreach ($this->uploadedImages($request) as $file) {
            $images[] = $this->imageUpload->store($file, 'transport/'.$provider->id, 'offre');
        }

        $offer->update([
            ...$validated,
            'images' => $images,
            'is_available' => $request->boolean('is_available', true),
        ]);

        return redirect()->route('provider.transport.index')->with('success', 'Offre mise à jour.');
    }

    public function destroy(TransportOffer $offer): RedirectResponse
    {
        $provider = $this->getProvider();
        abort_unless((int) $offer->provider_id === (int) $provider->id, 403);

        $offer->delete();

        return redirect()->route('provider.transport.index')->with('success', 'Offre retirée.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'integer', 'exists:transport_categories,id'],
            'description' => ['nullable', 'string', 'max:5000'],
            'price_xof' => ['required', 'integer', 'min:0'],
            'max_passengers' => ['nullable', 'integer', 'min:1', 'max:999'],
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

}
