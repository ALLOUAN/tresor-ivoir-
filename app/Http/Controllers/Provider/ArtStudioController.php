<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\ArtCommission;
use App\Models\ArtistProfile;
use App\Models\ArtworkOffer;
use App\Models\ArtworkOrder;
use App\Models\Provider;
use App\Services\ArtCommissionService;
use App\Services\ArtSalesService;
use App\Services\ImageUploadSecurityService;
use App\Notifications\VisitorSystemNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/** Espace artiste : page publique, offres reçues, expédition et retours, commandes sur mesure. */
class ArtStudioController extends Controller
{
    private function provider(): Provider
    {
        $provider = Provider::forUserQuery(Auth::id())->first();
        abort_unless($provider, 404, 'Aucune fiche prestataire trouvée.');
        abort_unless($provider->hasSector('art-creations'), 403, 'Réservé aux artistes (Art & Créations).');

        return $provider;
    }

    public function editProfile(): View
    {
        return view('provider.art.profile', ['profile' => ArtistProfile::forProvider($this->provider())]);
    }

    public function updateProfile(Request $request, ImageUploadSecurityService $uploads): RedirectResponse
    {
        $profile = ArtistProfile::forProvider($this->provider());
        $d = $request->validate([
            'display_name' => ['required', 'string', 'max:150'], 'tagline' => ['nullable', 'string', 'max:200'],
            'bio' => ['nullable', 'string', 'max:5000'], 'statement' => ['nullable', 'string', 'max:5000'],
            'city' => ['nullable', 'string', 'max:120'], 'birth_year' => ['nullable', 'integer', 'min:1900', 'max:'.now()->year],
            'education' => ['nullable', 'string', 'max:3000'], 'exhibitions' => ['nullable', 'string', 'max:5000'],
            'photo' => ['nullable', 'image', 'max:6144'], 'studio_photo' => ['nullable', 'image', 'max:6144'],
            'commission_from_xof' => ['nullable', 'integer', 'min:0'], 'commission_deposit_percent' => ['required', 'integer', 'in:30,40,50'],
            'pickup_address' => ['nullable', 'string', 'max:255'], 'abidjan_delivery_xof' => ['nullable', 'integer', 'min:0'],
            'country_delivery_xof' => ['nullable', 'integer', 'min:0'], 'large_supplement_xof' => ['nullable', 'integer', 'min:0'],
            'insurance_percent' => ['required', 'numeric', 'min:0', 'max:10'],
        ]);
        foreach (['photo', 'studio_photo'] as $k) {
            unset($d[$k]);
            if ($request->hasFile($k)) {
                $d[$k] = $uploads->store($request->file($k), 'artists/'.$profile->provider_id, $k);
            }
        }
        $profile->update($d + ['accepts_commissions' => $request->boolean('accepts_commissions'), 'returns_accepted' => $request->boolean('returns_accepted')]);

        return back()->with('success', 'Page artiste mise à jour.');
    }

    /* ── Offres ── */

    public function offers(): View
    {
        $p = $this->provider();

        return view('provider.art.offers', ['offers' => ArtworkOffer::with(['artwork', 'user'])->where('provider_id', $p->id)->latest()->paginate(20)]);
    }

    public function respondOffer(Request $request, ArtworkOffer $offer, ArtSalesService $sales): RedirectResponse
    {
        abort_unless((int) $offer->provider_id === (int) $this->provider()->id, 403);
        $d = $request->validate(['action' => ['required', 'in:accept,counter,decline'], 'counter_xof' => ['required_if:action,counter', 'nullable', 'integer', 'min:1000']]);
        try {
            $sales->respondOffer($offer->load('artwork'), $d['action'], isset($d['counter_xof']) ? (int) $d['counter_xof'] : null);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Réponse envoyée à l\'acheteur.');
    }

    /* ── Commande : expédition, retour ── */

    public function ship(Request $request, ArtworkOrder $artworkOrder): RedirectResponse
    {
        abort_unless((int) $artworkOrder->provider_id === (int) $this->provider()->id, 403);
        abort_unless($artworkOrder->status === ArtworkOrder::STATUS_PAID, 422);
        $d = $request->validate(['carrier' => ['required', 'string', 'max:80'], 'tracking_number' => ['nullable', 'string', 'max:80']]);
        $artworkOrder->update($d + ['status' => ArtworkOrder::STATUS_SHIPPED, 'shipped_at' => now()]);
        $artworkOrder->buyer?->notify(new VisitorSystemNotification(__('Œuvre expédiée'), '"'.$artworkOrder->artwork?->title.'" — '.$d['carrier'].($d['tracking_number'] ?? null ? ' · '.__('suivi').' '.$d['tracking_number'] : '').'. '.__('Confirmez la réception dès l\'arrivée.'), route('visitor.art-orders.index')));

        return back()->with('success', 'Expédition enregistrée : l\'acheteur a reçu le suivi.');
    }

    public function returnDecision(Request $request, ArtworkOrder $artworkOrder, ArtSalesService $sales): RedirectResponse
    {
        abort_unless((int) $artworkOrder->provider_id === (int) $this->provider()->id, 403);
        $action = $request->validate(['action' => ['required', 'in:accept,refuse,received']])['action'];
        if ($action === 'received') {
            try {
                $sales->refundReturn($artworkOrder, Auth::user());
            } catch (\RuntimeException $e) {
                return back()->with('error', $e->getMessage());
            }

            return back()->with('success', 'Œuvre récupérée : l\'acheteur est remboursé et l\'œuvre est remise en vente.');
        }
        abort_unless($artworkOrder->return_status === 'requested', 422);
        $artworkOrder->update(['return_status' => $action === 'accept' ? 'accepted' : 'refused']);
        $artworkOrder->buyer?->notify(new VisitorSystemNotification(
            $action === 'accept' ? __('Retour accepté') : __('Retour refusé'),
            $action === 'accept' ? __('Renvoyez ":t" à l\'artiste dans son emballage d\'origine ; vous serez remboursé à réception.', ['t' => $artworkOrder->artwork?->title]) : __('L\'artiste refuse le retour de ":t". Contactez le support si vous contestez.', ['t' => $artworkOrder->artwork?->title]),
            route('visitor.art-orders.index'),
        ));

        return back()->with('success', $action === 'accept' ? 'Retour accepté.' : 'Retour refusé — l\'administration peut arbitrer.');
    }

    /* ── Sur mesure ── */

    public function commissions(): View
    {
        $p = $this->provider();

        return view('provider.art.commissions', ['commissions' => ArtCommission::with('user')->where('provider_id', $p->id)->latest()->paginate(15)]);
    }

    public function commissionAction(Request $request, ArtCommission $artCommission, ArtCommissionService $service, ImageUploadSecurityService $uploads): RedirectResponse
    {
        abort_unless((int) $artCommission->provider_id === (int) $this->provider()->id, 403);
        $d = $request->validate([
            'action' => ['required', 'in:quote,decline,sketch,deliver,cancel'],
            'quote_xof' => ['required_if:action,quote', 'nullable', 'integer', 'min:1000'], 'quote_days' => ['required_if:action,quote', 'nullable', 'integer', 'min:1', 'max:365'],
            'message' => ['nullable', 'string', 'max:2000'], 'sketches' => ['required_if:action,sketch', 'nullable', 'array', 'max:4'], 'sketches.*' => ['image', 'max:6144'],
        ]);
        try {
            match ($d['action']) {
                'quote' => $service->quote($artCommission, (int) $d['quote_xof'], (int) $d['quote_days'], $d['message'] ?? null),
                'decline' => $service->decline($artCommission, $d['message'] ?? null),
                'sketch' => $service->sendSketch($artCommission, collect($request->file('sketches', []))->map(fn ($f) => $uploads->store($f, 'art-commissions/'.$artCommission->provider_id, 'esquisse'))->all(), $d['message'] ?? null),
                'deliver' => $service->deliver($artCommission),
                'cancel' => $service->cancel($artCommission, true),
            };
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'C\'est fait : le client est prévenu.');
    }
}
