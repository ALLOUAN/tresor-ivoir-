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
    use \App\Http\Controllers\Concerns\ValidatesTourMarketFields;

    public function __construct(
        private readonly ImageUploadSecurityService $imageUpload,
    ) {}

    private function getProvider(): Provider
    {
        $provider = Provider::forUserQuery(Auth::id())->first();
        if (! $provider) {
            abort(404, 'Aucune fiche prestataire trouvée.');
        }

        abort_unless($provider->hasSector('agences-voyages'), 403, 'Cette fonctionnalité est réservée aux prestataires de la catégorie Agences de Voyages & Tours.');

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
        ]) + $this->tourMarketData($request);
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


    /* ── Départs programmés ─────────────────────────────────────────────── */

    public function departures(TourPackage $tour): View
    {
        $provider = $this->getProvider();
        abort_unless((int) $tour->provider_id === (int) $provider->id, 403);
        $departures = $tour->departures()->whereDate('departure_date', '>=', now()->subDays(30)->toDateString())
            ->with(['bookings' => fn ($q) => $q->whereIn('status', [\App\Models\TourBooking::STATUS_DEPOSIT_PAID, \App\Models\TourBooking::STATUS_PAID, \App\Models\TourBooking::STATUS_CANCELLED])->orderBy('created_at')])
            ->get();

        return view('provider.tours.departures', compact('tour', 'departures'));
    }

    public function storeDeparture(Request $request, TourPackage $tour): RedirectResponse
    {
        $provider = $this->getProvider();
        abort_unless((int) $tour->provider_id === (int) $provider->id, 403);
        $data = $request->validate([
            'departure_date' => ['required', 'date', 'after_or_equal:today'],
            'seats_total' => ['required', 'integer', 'min:1', 'max:500'],
            'min_participants' => ['nullable', 'integer', 'min:1', 'lte:seats_total'],
            'price_xof' => ['nullable', 'integer', 'min:0'],
            'repeat_weeks' => ['nullable', 'integer', 'min:0', 'max:52'],
        ], [], ['departure_date' => 'date de départ', 'seats_total' => 'places']);

        // Départ unique ou répété chaque semaine (ex. tous les samedis pendant 8 semaines).
        $created = 0;
        $date = \Carbon\CarbonImmutable::parse($data['departure_date']);
        for ($i = 0; $i <= (int) ($data['repeat_weeks'] ?? 0); $i++) {
            $d = $date->addWeeks($i)->toDateString();
            $dep = \App\Models\TourDeparture::firstOrCreate(
                ['tour_package_id' => $tour->id, 'departure_date' => $d],
                ['seats_total' => $data['seats_total'], 'min_participants' => $data['min_participants'] ?? null, 'price_xof' => $data['price_xof'] ?? null, 'is_active' => true]
            );
            $created += (int) $dep->wasRecentlyCreated;
        }

        return back()->with('success', $created.' départ(s) ajouté(s).');
    }

    /** Places, minimum, prix, ouverture des inscriptions. */
    public function updateDeparture(Request $request, TourPackage $tour, \App\Models\TourDeparture $departure): RedirectResponse
    {
        $this->ownedDeparture($tour, $departure);
        $data = $request->validate([
            'seats_total' => ['required', 'integer', 'min:'.max(1, $departure->seatsTaken()), 'max:500'],
            'min_participants' => ['nullable', 'integer', 'min:1', 'lte:seats_total'],
            'price_xof' => ['nullable', 'integer', 'min:0'],
        ], ['seats_total.min' => 'Impossible de descendre sous le nombre de voyageurs déjà inscrits (:min).']);
        $departure->update($data + ['is_active' => $request->boolean('is_active', true)]);
        app(\App\Services\TourBookingService::class)->guaranteeIfReached($departure);

        return back()->with('success', 'Départ mis à jour.');
    }

    /** Confirmer le départ même sans le minimum : les inscrits sont prévenus. */
    public function guaranteeDeparture(TourPackage $tour, \App\Models\TourDeparture $departure): RedirectResponse
    {
        $this->ownedDeparture($tour, $departure);
        abort_if($departure->cancelled_at, 422);
        app(\App\Services\TourBookingService::class)->guaranteeIfReached($departure, force: true);

        return back()->with('success', 'Départ garanti : les voyageurs inscrits sont prévenus.');
    }

    /** Annuler un départ : tous les inscrits sont remboursés à 100 % sur leur solde et prévenus. */
    public function cancelDeparture(Request $request, TourPackage $tour, \App\Models\TourDeparture $departure): RedirectResponse
    {
        $this->ownedDeparture($tour, $departure);
        $reason = $request->validate(['reason' => ['required', 'string', 'max:255']], ['reason.required' => 'Indiquez le motif, il sera transmis aux voyageurs.'])['reason'];
        $n = app(\App\Services\TourBookingService::class)->cancelDeparture($departure, 'agency', $reason);

        return back()->with('success', 'Départ annulé. '.$n.' réservation(s) remboursée(s) intégralement.');
    }

    private function ownedDeparture(TourPackage $tour, \App\Models\TourDeparture $departure): void
    {
        $provider = $this->getProvider();
        abort_unless((int) $tour->provider_id === (int) $provider->id && (int) $departure->tour_package_id === (int) $tour->id, 403);
    }

    public function destroyDeparture(TourPackage $tour, \App\Models\TourDeparture $departure): RedirectResponse
    {
        $provider = $this->getProvider();
        abort_unless((int) $tour->provider_id === (int) $provider->id && (int) $departure->tour_package_id === (int) $tour->id, 403);
        if ($departure->seatsTaken() > 0) {
            // Des voyageurs sont inscrits : on ferme les inscriptions au lieu de supprimer.
            $departure->update(['is_active' => false]);

            return back()->with('success', 'Inscriptions fermées pour ce départ (des voyageurs y sont déjà inscrits).');
        }
        $departure->delete();

        return back()->with('success', 'Départ supprimé.');
    }
}
