<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\BookingRequest;
use App\Models\Provider;
use App\Models\Reservation;
use App\Models\TourDeparture;
use App\Models\TouristVisit;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/** Planning du jour : arrivées, départs, tables, activités, visites et départs de circuits réunis sur une page. */
class AgendaController extends Controller
{
    public function index(Request $request): View
    {
        $provider = Provider::forUser(Auth::id());
        abort_unless($provider, 404);

        try {
            $day = CarbonImmutable::parse((string) $request->query('date', today()->toDateString()))->startOfDay();
        } catch (\Throwable) {
            $day = CarbonImmutable::today();
        }
        $date = $day->toDateString();

        $stays = Reservation::where('provider_id', $provider->id)->where('status', '!=', Reservation::STATUS_CANCELLED)
            ->where('check_in', '<=', $date)->where('check_out', '>=', $date)->orderBy('check_in')->get();

        $entries = collect();
        foreach ($stays as $r) {
            $kind = match (true) {
                $r->check_in->toDateString() === $date => ['arrival', 'Arrivée', 'fa-plane-arrival'],
                $r->check_out->toDateString() === $date => ['departure', 'Départ', 'fa-plane-departure'],
                default => ['inhouse', 'En séjour', 'fa-bed'],
            };
            $entries->push([
                'time' => $kind[0] === 'arrival' ? '14:00' : ($kind[0] === 'departure' ? '11:00' : null),
                'kind' => $kind[0], 'label' => $kind[1], 'icon' => $kind[2],
                'title' => $r->full_name.' · '.$r->room_name.($r->rooms_count > 1 ? ' ×'.$r->rooms_count : ''),
                'detail' => $r->guests_count.' pers. · '.$r->check_in->format('d/m').' → '.$r->check_out->format('d/m'),
                'status' => $r->status === Reservation::STATUS_NEW ? 'À confirmer' : null,
                'phone' => $r->phone,
                'url' => route('provider.reservations.show', $r),
            ]);
        }

        BookingRequest::where('provider_id', $provider->id)->whereDate('booking_date', $date)
            ->whereIn('status', [BookingRequest::STATUS_NEW, BookingRequest::STATUS_CONFIRMED])
            ->get()->each(fn ($b) => $entries->push([
                'time' => $b->booking_time ? substr((string) $b->booking_time, 0, 5) : null,
                'kind' => $b->bookable_type, 'label' => BookingRequest::TYPES[$b->bookable_type]['label'] ?? 'Réservation',
                'icon' => match ($b->bookable_type) { 'restaurant' => 'fa-utensils', 'restaurant_order' => 'fa-basket-shopping', 'leisure' => 'fa-person-swimming', 'transport' => 'fa-van-shuttle', 'art' => 'fa-palette', default => 'fa-route' },
                'title' => $b->full_name.' · '.($b->item_name ?: $b->bookable_name),
                'detail' => $b->party_size.' '.$b->unitLabel().($b->pickup_location ? ' · '.$b->pickup_location.' → '.$b->dropoff_location : ''),
                'status' => $b->status === BookingRequest::STATUS_NEW ? 'À confirmer' : ($b->payment_status === BookingRequest::PAYMENT_PAID ? 'Payé' : null),
                'phone' => $b->phone,
                'url' => route('provider.booking-requests.index', ['statut' => $b->status]),
            ]));

        TouristVisit::where('provider_id', $provider->id)->whereIn('status', [TouristVisit::STATUS_PAID, TouristVisit::STATUS_BOOKED])
            ->where(fn ($q) => $q->whereDate('session_date', $date)->orWhere(fn ($q2) => $q2->whereNull('session_date')->whereDate('desired_date', $date)))
            ->get()->each(fn ($v) => $entries->push([
                'time' => $v->visit_time ?: (preg_match('/\d{1,2}[:h]\d{2}/', (string) $v->session_time_label, $m) ? str_pad(str_replace('h', ':', $m[0]), 5, '0', STR_PAD_LEFT) : null),
                'kind' => 'visit', 'label' => 'Visite', 'icon' => 'fa-ticket',
                'title' => $v->full_name.' · '.$v->experience_name,
                'detail' => $v->participants_count.' pers.'.($v->with_guide ? ' · avec guide' : '').($v->checked_in_at ? ' · entré à '.$v->checked_in_at->format('H:i') : ''),
                'status' => $v->checked_in_at ? 'Arrivé' : ($v->status === TouristVisit::STATUS_BOOKED ? 'À encaisser' : null),
                'phone' => $v->phone,
                'url' => route('provider.tourist-visits.show', $v),
            ]));

        TourDeparture::whereHas('tour', fn ($q) => $q->where('provider_id', $provider->id))
            ->whereDate('departure_date', $date)->where('is_active', true)->with('tour')->get()
            ->each(fn ($d) => $entries->push([
                'time' => null, 'kind' => 'departure_tour', 'label' => 'Départ circuit', 'icon' => 'fa-bus',
                'title' => $d->tour?->name,
                'detail' => $d->seatsTaken().' voyageur(s) inscrit(s) sur '.$d->seats_total,
                'status' => null, 'phone' => null,
                'url' => route('provider.tours.departures', $d->tour_package_id),
            ]));

        // Les entrées sans heure (séjours en cours, départs de circuit) en tête, puis par heure.
        $entries = $entries->sortBy(fn ($e) => $e['time'] ?? '00:00')->values();

        $week = collect(range(-1, 5))->map(fn ($i) => $day->addDays($i));

        return view('provider.agenda.index', compact('provider', 'day', 'entries', 'week'));
    }
}
