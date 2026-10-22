<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use App\Models\TransportBooking;
use App\Models\TransportDriver;
use App\Models\TransportOffer;
use App\Models\TransportTrip;
use App\Models\TransportVehicle;
use App\Services\TransportBookingService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Espace compagnie de transport : planning des réservations (chauffeur, véhicule, statut du trajet,
 * état des lieux des locations), flotte, chauffeurs, départs des lignes régulières.
 */
class TransportOpsController extends Controller
{
    private function provider(): Provider
    {
        $provider = Provider::forUserQuery(Auth::id())->first();
        abort_unless($provider, 404, 'Aucune fiche prestataire trouvée.');
        $category = $provider->category;
        $root = $category?->parent_id ? $category->parent?->slug : $category?->slug;
        abort_unless($root === 'transports', 403, 'Réservé aux prestataires Transports & Mobilité.');

        return $provider;
    }

    private function owned(Provider $p, $model): void
    {
        abort_unless((int) $model->provider_id === (int) $p->id, 403);
    }

    /* ── Planning ── */

    public function bookings(Request $request): View
    {
        $p = $this->provider();
        $date = $request->query('date', now('Africa/Abidjan')->toDateString());
        $day = Carbon::parse($date, 'Africa/Abidjan');

        return view('provider.transport.bookings', [
            'date' => $date,
            'bookings' => TransportBooking::with(['offer', 'driver', 'vehicle', 'trip'])->where('provider_id', $p->id)
                ->whereIn('status', [TransportBooking::STATUS_PAID, TransportBooking::STATUS_REFUNDED, TransportBooking::STATUS_CANCELLED])
                ->where('starts_at', '<', $day->copy()->endOfDay()->utc())->where('ends_at', '>', $day->copy()->startOfDay()->utc())
                ->orderBy('starts_at')->get(),
            'upcoming' => TransportBooking::where('provider_id', $p->id)->where('status', TransportBooking::STATUS_PAID)->where('starts_at', '>=', now()->startOfDay())
                ->selectRaw('DATE(starts_at) as d, count(*) as n')->groupBy('d')->orderBy('d')->take(14)->get(),
            'drivers' => TransportDriver::where('provider_id', $p->id)->where('is_active', true)->orderBy('name')->get(),
            'vehicles' => TransportVehicle::where('provider_id', $p->id)->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function assign(Request $request, TransportBooking $booking, TransportBookingService $service): RedirectResponse
    {
        $p = $this->provider();
        $this->owned($p, $booking);
        $data = $request->validate(['transport_driver_id' => ['nullable', 'integer'], 'transport_vehicle_id' => ['nullable', 'integer']]);
        $driver = TransportDriver::where('provider_id', $p->id)->find($data['transport_driver_id'] ?? 0);
        $vehicle = TransportVehicle::where('provider_id', $p->id)->find($data['transport_vehicle_id'] ?? 0);
        if ($vehicle && $vehicle->id !== $booking->transport_vehicle_id) {
            $clash = TransportBooking::where('transport_vehicle_id', $vehicle->id)->where('status', TransportBooking::STATUS_PAID)->whereKeyNot($booking->id)
                ->where('starts_at', '<', $booking->ends_at)->where('ends_at', '>', $booking->starts_at)->exists();
            if ($clash || $vehicle->inMaintenance($booking->starts_at, $booking->ends_at)) {
                return back()->with('error', 'Ce véhicule est déjà pris ou en entretien sur ce créneau.');
            }
        }
        $booking->update(['transport_driver_id' => $driver?->id, 'transport_vehicle_id' => $vehicle?->id ?? $booking->transport_vehicle_id]);
        if ($driver && ! $booking->trip_status) {
            $service->updateTripStatus($booking->fresh(['driver', 'vehicle']), 'assigned');
        }

        return back()->with('success', 'Affectation enregistrée'.($driver ? ' : le client a reçu les coordonnées du chauffeur.' : '.'));
    }

    public function tripStatus(Request $request, TransportBooking $booking, TransportBookingService $service): RedirectResponse
    {
        $p = $this->provider();
        $this->owned($p, $booking);
        $status = $request->validate(['trip_status' => ['required', 'in:'.implode(',', array_keys(TransportBooking::TRIP_STATUSES))]])['trip_status'];
        abort_unless($booking->status === TransportBooking::STATUS_PAID, 422);
        $service->updateTripStatus($booking->load(['driver', 'vehicle']), $status);

        return back()->with('success', 'Statut mis à jour : '.$booking->fresh()->tripStatusLabel().'. Le client est prévenu.');
    }

    /** Location : état des lieux au départ (out) et au retour (in) — kilométrage, carburant, photos, remarques. */
    public function inspection(Request $request, TransportBooking $booking): RedirectResponse
    {
        $p = $this->provider();
        $this->owned($p, $booking);
        abort_unless($booking->mode === 'rental', 404);
        $data = $request->validate([
            'stage' => ['required', 'in:out,in'], 'km' => ['required', 'integer', 'min:0', 'max:2000000'],
            'fuel' => ['required', 'in:0,25,50,75,100'], 'notes' => ['nullable', 'string', 'max:1000'],
            'photos' => ['nullable', 'array', 'max:8'], 'photos.*' => ['image', 'max:6144'],
        ]);
        $photos = collect($request->file('photos', []))->map(fn ($f) => $f->store('transport-inspections/'.$booking->id, 'local'))->all();
        $insp = $booking->inspection ?? [];
        $insp[$data['stage']] = ['km' => (int) $data['km'], 'fuel' => (int) $data['fuel'], 'notes' => $data['notes'] ?? null, 'photos' => $photos, 'at' => now()->toIso8601String()];
        $extra = null;
        if ($data['stage'] === 'in' && isset($insp['out']['km'])) {
            $driven = max(0, (int) $data['km'] - (int) $insp['out']['km']);
            $days = max(1, (int) ceil($booking->starts_at->diffInMinutes($booking->ends_at) / 1440));
            $allowed = (int) ($booking->offer?->included_km ?? 0) * $days;
            $over = $allowed > 0 ? max(0, $driven - $allowed) : 0;
            $insp['in']['driven_km'] = $driven;
            $insp['in']['extra_km'] = $over;
            $insp['in']['extra_km_xof'] = $over * (int) ($booking->offer?->extra_km_xof ?? 0);
            $extra = $insp['in']['extra_km_xof'];
        }
        $booking->update(['inspection' => $insp, 'trip_status' => $data['stage'] === 'out' ? 'in_progress' : 'completed']);

        return back()->with('success', $data['stage'] === 'out' ? 'État des lieux de départ enregistré.' : 'État des lieux de retour enregistré'.($extra ? ' — km supplémentaires à encaisser : '.number_format($extra, 0, ',', ' ').' XOF.' : '.'));
    }

    /** Pièces du client ou photos d'état des lieux, servies depuis le disque privé. */
    public function file(TransportBooking $booking, string $kind, int $index = 0): StreamedResponse
    {
        $p = $this->provider();
        $this->owned($p, $booking);
        $path = match ($kind) {
            'license', 'id_card' => $booking->documents[$kind] ?? null,
            'out', 'in' => $booking->inspection[$kind]['photos'][$index] ?? null,
            default => null,
        };
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path);
    }

    /* ── Flotte ── */

    public function fleet(): View
    {
        $p = $this->provider();

        return view('provider.transport.fleet', [
            'vehicles' => TransportVehicle::where('provider_id', $p->id)->orderBy('category')->orderBy('name')->get(),
            'drivers' => TransportDriver::where('provider_id', $p->id)->orderBy('name')->get(),
        ]);
    }

    public function saveVehicle(Request $request, ?TransportVehicle $vehicle = null): RedirectResponse
    {
        $p = $this->provider();
        if ($vehicle?->exists) {
            $this->owned($p, $vehicle);
        }
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'], 'category' => ['required', 'in:'.implode(',', array_keys(TransportOffer::VEHICLE_CATEGORIES))],
            'plate' => ['nullable', 'string', 'max:20'], 'seats' => ['required', 'integer', 'min:1', 'max:90'], 'luggage' => ['required', 'integer', 'min:0', 'max:60'],
            'unavailable_from' => ['nullable', 'date'], 'unavailable_to' => ['nullable', 'date', 'after_or_equal:unavailable_from'],
        ]);
        $data += ['has_ac' => $request->boolean('has_ac', true), 'is_active' => $request->boolean('is_active', true)];
        $vehicle?->exists ? $vehicle->update($data) : TransportVehicle::create($data + ['provider_id' => $p->id]);

        return back()->with('success', 'Véhicule enregistré.');
    }

    public function saveDriver(Request $request, ?TransportDriver $driver = null): RedirectResponse
    {
        $p = $this->provider();
        if ($driver?->exists) {
            $this->owned($p, $driver);
        }
        $data = $request->validate(['name' => ['required', 'string', 'max:120'], 'phone' => ['required', 'string', 'max:30'], 'languages' => ['nullable', 'string', 'max:120']]);
        $data['is_active'] = $request->boolean('is_active', true);
        $driver?->exists ? $driver->update($data) : TransportDriver::create($data + ['provider_id' => $p->id]);

        return back()->with('success', 'Chauffeur enregistré.');
    }

    /* ── Départs des lignes ── */

    public function trips(): View
    {
        $p = $this->provider();

        return view('provider.transport.trips', [
            'lines' => TransportOffer::where('provider_id', $p->id)->where('mode', 'line')->with(['trips' => fn ($q) => $q->where('departs_at', '>=', now()->subDay())->withCount('bookings')])->get(),
            'vehicles' => TransportVehicle::where('provider_id', $p->id)->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function storeTrips(Request $request): RedirectResponse
    {
        $p = $this->provider();
        $data = $request->validate([
            'transport_offer_id' => ['required', 'integer'], 'date' => ['required', 'date', 'after_or_equal:today'],
            'times' => ['required', 'string', 'max:200'], 'repeat_days' => ['nullable', 'integer', 'min:0', 'max:90'],
            'seats_total' => ['required', 'integer', 'min:1', 'max:90'], 'seats_per_row' => ['required', 'integer', 'min:2', 'max:6'],
            'price_xof' => ['nullable', 'integer', 'min:0'], 'transport_vehicle_id' => ['nullable', 'integer'],
        ], ['times.required' => 'Indiquez au moins une heure de départ (ex : 07:00, 14:00).']);
        $offer = TransportOffer::where('provider_id', $p->id)->where('mode', 'line')->findOrFail($data['transport_offer_id']);
        $times = collect(preg_split('/[\s,;]+/', $data['times']))->filter()->map(fn ($t) => \App\Models\TouristVisitType::normalizeTime($t));
        if ($times->contains(null)) {
            return back()->withErrors(['times' => 'Heure invalide (format attendu 07:00).']);
        }
        $vehicleId = TransportVehicle::where('provider_id', $p->id)->whereKey($data['transport_vehicle_id'] ?? 0)->value('id');
        $n = 0;
        for ($d = 0; $d <= (int) ($data['repeat_days'] ?? 0); $d++) {
            foreach ($times as $t) {
                $at = Carbon::parse($data['date'].' '.$t, 'Africa/Abidjan')->addDays($d);
                $trip = TransportTrip::firstOrCreate(['transport_offer_id' => $offer->id, 'departs_at' => $at->copy()->utc()], [
                    'seats_total' => $data['seats_total'], 'seats_per_row' => $data['seats_per_row'], 'price_xof' => $data['price_xof'] ?? null,
                    'transport_vehicle_id' => $vehicleId, 'is_active' => true,
                ]);
                $n += (int) $trip->wasRecentlyCreated;
            }
        }

        return back()->with('success', $n.' départ(s) ajouté(s).');
    }

    public function toggleTrip(TransportTrip $trip): RedirectResponse
    {
        $p = $this->provider();
        abort_unless((int) $trip->offer?->provider_id === (int) $p->id, 403);
        $trip->update(['is_active' => ! $trip->is_active]);

        return back()->with('success', $trip->is_active ? 'Départ rouvert à la vente.' : 'Départ fermé à la vente (les billets vendus restent valables).');
    }
}
