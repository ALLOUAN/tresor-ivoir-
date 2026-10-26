<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\ActivityBooking;
use App\Models\ActivityWaitlist;
use App\Models\LeisurePackage;
use App\Models\LeisurePass;
use App\Models\LeisurePassPurchase;
use App\Models\LeisureResource;
use App\Models\Provider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Espace prestataire Loisirs : réservations en ligne (billets, décharges, liste d'attente), ressources
 * partagées, formules anniversaire / groupe / entreprise, cartes multi-séances et abonnements.
 */
class LeisureOfferController extends Controller
{
    private function provider(): Provider
    {
        $provider = Provider::forUserQuery(Auth::id())->first();
        abort_unless($provider, 404, 'Aucune fiche prestataire trouvée.');
        abort_unless($provider->hasSector(['loisirs-culture', 'sites-touristiques']), 403, 'Réservé aux prestataires Loisirs & Culture et Sites Touristiques.');

        return $provider;
    }

    /* ── Réservations ── */

    public function bookings(Request $request): View
    {
        $provider = $this->provider();
        $date = $request->query('date', now('Africa/Abidjan')->toDateString());
        $bookings = ActivityBooking::with(['activity', 'package', 'passPurchase'])->where('provider_id', $provider->id)
            ->whereIn('status', [ActivityBooking::STATUS_PAID, ActivityBooking::STATUS_CANCELLED, ActivityBooking::STATUS_REFUNDED])
            ->whereDate('booking_date', $date)->orderBy('booking_time')->get();
        $upcoming = ActivityBooking::where('provider_id', $provider->id)->where('status', ActivityBooking::STATUS_PAID)
            ->whereDate('booking_date', '>=', now()->toDateString())->selectRaw('booking_date, count(*) as n, sum(participants) as p')
            ->groupBy('booking_date')->orderBy('booking_date')->take(14)->get();
        $waitlist = ActivityWaitlist::with(['activity', 'user'])->whereIn('activity_id', \App\Models\Activity::where('provider_id', $provider->id)->pluck('id'))
            ->whereIn('status', [ActivityWaitlist::STATUS_WAITING, ActivityWaitlist::STATUS_NOTIFIED])->whereDate('booking_date', '>=', now()->toDateString())
            ->orderBy('booking_date')->orderBy('booking_time')->get();

        return view('provider.leisure.bookings', compact('bookings', 'upcoming', 'waitlist', 'date'));
    }

    /* ── Ressources ── */

    public function resources(): View
    {
        $provider = $this->provider();

        return view('provider.leisure.resources', [
            'resources' => LeisureResource::where('provider_id', $provider->id)->with('activities')->orderBy('name')->get(),
            'activities' => \App\Models\Activity::where('provider_id', $provider->id)->orderBy('name')->get(),
        ]);
    }

    public function storeResource(Request $request): RedirectResponse
    {
        $provider = $this->provider();
        $data = $request->validate(['name' => ['required', 'string', 'max:120'], 'units' => ['required', 'integer', 'min:1', 'max:1000']]);
        LeisureResource::create($data + ['provider_id' => $provider->id]);

        return back()->with('success', 'Ressource ajoutée.');
    }

    /** Nom, quantité, et activités qui l'utilisent (avec unités, par participant ou par réservation). */
    public function updateResource(Request $request, LeisureResource $resource): RedirectResponse
    {
        abort_unless((int) $resource->provider_id === (int) $this->provider()->id, 403);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'], 'units' => ['required', 'integer', 'min:1', 'max:1000'],
            'links' => ['nullable', 'array'], 'links.*.units' => ['nullable', 'integer', 'min:1', 'max:1000'], 'links.*.mode' => ['nullable', 'in:booking,participant'],
        ]);
        $resource->update(['name' => $data['name'], 'units' => $data['units'], 'is_active' => $request->boolean('is_active', true)]);
        $owned = \App\Models\Activity::where('provider_id', $resource->provider_id)->pluck('id')->all();
        $sync = collect($request->input('links', []))->filter(fn ($l, $id) => in_array((int) $id, $owned, true) && ! empty($l['on']))
            ->mapWithKeys(fn ($l, $id) => [(int) $id => ['units' => (int) ($l['units'] ?? 1), 'per_participant' => ($l['mode'] ?? 'booking') === 'participant']])->all();
        $resource->activities()->sync($sync);

        return back()->with('success', 'Ressource mise à jour.');
    }

    public function destroyResource(LeisureResource $resource): RedirectResponse
    {
        abort_unless((int) $resource->provider_id === (int) $this->provider()->id, 403);
        $resource->delete();

        return back()->with('success', 'Ressource supprimée.');
    }

    /* ── Formules ── */

    public function packages(): View
    {
        $provider = $this->provider();

        return view('provider.leisure.packages', [
            'packages' => LeisurePackage::where('provider_id', $provider->id)->with('resource')->orderBy('name')->get(),
            'resources' => LeisureResource::where('provider_id', $provider->id)->orderBy('name')->get(),
        ]);
    }

    public function storePackage(Request $request): RedirectResponse
    {
        $provider = $this->provider();
        LeisurePackage::create($this->packageData($request, $provider) + ['provider_id' => $provider->id]);

        return back()->with('success', 'Formule créée.');
    }

    public function updatePackage(Request $request, LeisurePackage $package): RedirectResponse
    {
        $provider = $this->provider();
        abort_unless((int) $package->provider_id === (int) $provider->id, 403);
        $package->update($this->packageData($request, $provider));

        return back()->with('success', 'Formule mise à jour.');
    }

    public function destroyPackage(LeisurePackage $package): RedirectResponse
    {
        abort_unless((int) $package->provider_id === (int) $this->provider()->id, 403);
        $package->update(['is_active' => false]);

        return back()->with('success', 'Formule désactivée (les réservations existantes sont conservées).');
    }

    private function packageData(Request $request, Provider $provider): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'], 'kind' => ['required', 'in:'.implode(',', array_keys(LeisurePackage::KINDS))],
            'description' => ['nullable', 'string', 'max:2000'], 'includes' => ['nullable', 'string', 'max:2000'],
            'weekday_price_xof' => ['required', 'integer', 'min:0'], 'weekend_price_xof' => ['nullable', 'integer', 'min:0'],
            'included_participants' => ['required', 'integer', 'min:1', 'max:500'], 'extra_participant_xof' => ['nullable', 'integer', 'min:0'],
            'min_participants' => ['required', 'integer', 'min:1', 'max:500'], 'max_participants' => ['nullable', 'integer', 'gte:min_participants', 'max:1000'],
            'duration_minutes' => ['required', 'integer', 'min:15', 'max:1440'], 'deposit_xof' => ['nullable', 'integer', 'min:0'],
            'slot_times_text' => ['required', 'string', 'max:300'], 'free_cancellation_days' => ['required', 'integer', 'min:0', 'max:60'],
            'leisure_resource_id' => ['nullable', 'integer'],
        ], ['slot_times_text.required' => 'Indiquez au moins un horaire de début (ex : 14:00, 16:30).']);
        $slots = collect(preg_split('/[\s,;]+/', $data['slot_times_text']))->filter()->map(fn ($t) => \App\Models\TouristVisitType::normalizeTime($t));
        if ($slots->contains(null)) {
            throw \Illuminate\Validation\ValidationException::withMessages(['slot_times_text' => 'Horaire invalide (format attendu 14:00).']);
        }
        unset($data['slot_times_text']);
        $data['slot_times'] = $slots->unique()->sort()->values()->all();
        $data['extra_participant_xof'] = (int) ($data['extra_participant_xof'] ?? 0);
        $data['deposit_xof'] = (int) ($data['deposit_xof'] ?? 0);
        $data['leisure_resource_id'] = LeisureResource::where('provider_id', $provider->id)->whereKey($data['leisure_resource_id'] ?? 0)->value('id');
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }

    /* ── Cartes ── */

    public function passes(): View
    {
        $provider = $this->provider();

        return view('provider.leisure.passes', [
            'passes' => LeisurePass::where('provider_id', $provider->id)->orderBy('price_xof')->get(),
            'activities' => \App\Models\Activity::where('provider_id', $provider->id)->orderBy('name')->get(),
            'sold' => LeisurePassPurchase::with('user')->where('provider_id', $provider->id)->where('status', LeisurePassPurchase::STATUS_ACTIVE)->latest()->take(30)->get(),
        ]);
    }

    public function storePass(Request $request): RedirectResponse
    {
        $provider = $this->provider();
        LeisurePass::create($this->passData($request, $provider) + ['provider_id' => $provider->id]);

        return back()->with('success', 'Carte créée.');
    }

    public function updatePass(Request $request, LeisurePass $pass): RedirectResponse
    {
        $provider = $this->provider();
        abort_unless((int) $pass->provider_id === (int) $provider->id, 403);
        $pass->update($this->passData($request, $provider));

        return back()->with('success', 'Carte mise à jour (les cartes déjà vendues gardent leurs conditions).');
    }

    private function passData(Request $request, Provider $provider): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'], 'description' => ['nullable', 'string', 'max:1000'],
            'sessions' => ['nullable', 'integer', 'min:1', 'max:500'], 'validity_days' => ['required', 'integer', 'min:1', 'max:1095'],
            'price_xof' => ['required', 'integer', 'min:0'], 'activity_ids' => ['nullable', 'array'], 'activity_ids.*' => ['integer'],
        ]);
        $owned = \App\Models\Activity::where('provider_id', $provider->id)->pluck('id')->all();
        $data['activity_ids'] = array_values(array_intersect(array_map('intval', $data['activity_ids'] ?? []), $owned)) ?: null;
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }
}
