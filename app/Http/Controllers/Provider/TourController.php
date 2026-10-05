<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use App\Models\TourCategory;
use App\Models\TourPackage;
use App\Services\ImageUploadSecurityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TourController extends Controller
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
        abort_unless($rootSlug === 'agences-voyages', 403, 'Cette fonctionnalité est réservée aux prestataires de la catégorie Agences de Voyages & Tours.');

        return $provider;
    }

    /** Page d'accueil de l'espace dédié Agence de Voyages. */
    public function dashboard(): View
    {
        $provider = $this->getProvider();

        $tourCounts = [
            'available' => TourPackage::where('provider_id', $provider->id)->where('is_available', true)->count(),
            'unavailable' => TourPackage::where('provider_id', $provider->id)->where('is_available', false)->count(),
        ];

        return view('provider.tours.dashboard', compact('tourCounts'));
    }

    public function index(): View
    {
        $provider = $this->getProvider();

        $tours = TourPackage::where('provider_id', $provider->id)
            ->with('category')
            ->orderByDesc('id')
            ->paginate(15);

        return view('provider.tours.index', compact('tours'));
    }

    public function create(): View
    {
        $this->getProvider();

        $categories = TourCategory::where('is_active', true)->orderBy('sort_order')->get();

        return view('provider.tours.form', ['tour' => new TourPackage(), 'categories' => $categories]);
    }

    public function store(Request $request): RedirectResponse
    {
        $provider = $this->getProvider();

        $validated = $this->validated($request);

        $images = [];
        foreach ($this->uploadedImages($request) as $file) {
            $images[] = $this->imageUpload->store($file, 'tours/'.$provider->id, 'circuit');
        }

        TourPackage::create([
            ...$validated,
            'provider_id' => $provider->id,
            'images' => $images,
            'is_available' => $request->boolean('is_available', true),
        ]);

        return redirect()->route('provider.tours.index')->with('success', 'Circuit ajouté.');
    }

    public function edit(TourPackage $tour): View
    {
        $provider = $this->getProvider();
        abort_unless((int) $tour->provider_id === (int) $provider->id, 403);

        $categories = TourCategory::where('is_active', true)->orderBy('sort_order')->get();

        return view('provider.tours.form', ['tour' => $tour, 'categories' => $categories]);
    }

    public function update(Request $request, TourPackage $tour): RedirectResponse
    {
        $provider = $this->getProvider();
        abort_unless((int) $tour->provider_id === (int) $provider->id, 403);

        $validated = $this->validated($request);

        $images = $this->removeMarkedImages($request, $tour->images ?? []);
        foreach ($this->uploadedImages($request) as $file) {
            $images[] = $this->imageUpload->store($file, 'tours/'.$provider->id, 'circuit');
        }

        $tour->update([
            ...$validated,
            'images' => $images,
            'is_available' => $request->boolean('is_available', true),
        ]);

        return redirect()->route('provider.tours.index')->with('success', 'Circuit mis à jour.');
    }

    public function destroy(TourPackage $tour): RedirectResponse
    {
        $provider = $this->getProvider();
        abort_unless((int) $tour->provider_id === (int) $provider->id, 403);

        $tour->delete();

        return redirect()->route('provider.tours.index')->with('success', 'Circuit retiré.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'integer', 'exists:tour_categories,id'],
            'description' => ['nullable', 'string', 'max:5000'],
            'price_xof' => ['required', 'integer', 'min:0'],
            'duration_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'max_participants' => ['nullable', 'integer', 'min:1', 'max:999'],
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
