<?php

namespace App\Http\Controllers;

use App\Mail\BookingRequestMail;
use App\Models\BookingRequest;
use App\Models\Provider;
use App\Models\TourPackage;
use App\Notifications\VisitorSystemNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

/**
 * Demandes de réservation côté client : création depuis la fiche d'un restaurant, d'un lieu de
 * loisir ou d'une agence (circuit), suivi et annulation depuis l'espace client.
 */
class BookingRequestController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('booking', [
            'bookable_type' => ['required', 'in:'.implode(',', array_keys(BookingRequest::TYPES))],
            'bookable_id' => ['required', 'integer'],
            'tour_package_id' => ['nullable', 'integer'],
            'activity_id' => ['nullable', 'integer'],
            'custom_quote' => ['nullable', 'boolean'],
            'transport_offer_id' => ['nullable', 'integer'],
            'pickup_location' => ['required_if:bookable_type,transport', 'nullable', 'string', 'max:255'],
            'dropoff_location' => ['required_if:bookable_type,transport', 'nullable', 'string', 'max:255'],
            'booking_date' => ['required', 'date', 'after_or_equal:today', 'before:+1 year'],
            'booking_time' => ['nullable', 'date_format:H:i'],
            // Commande de plats : le nombre de plats est calculé à partir des lignes de commande.
            'party_size' => ['required_unless:bookable_type,restaurant_order', 'nullable', 'integer', 'min:1', 'max:200'],
            'order' => ['nullable', 'array'],
            'order.*' => ['nullable', 'integer', 'min:0', 'max:50'],
            'fulfillment' => ['nullable', 'in:pickup,delivery'],
            'delivery_address' => ['nullable', 'string', 'max:255'],
            'restaurant_table_id' => ['nullable', 'integer'],
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'message' => ['nullable', 'required_if:custom_quote,1', 'string', 'max:2000'],
            'promo_code' => ['nullable', 'string', 'max:30'],
        ], ['message.required_if' => __('Décrivez votre projet de voyage (destinations, envies, budget…).')], [
            'booking_date' => 'date', 'booking_time' => 'heure', 'party_size' => 'nombre de personnes',
            'full_name' => 'nom', 'tour_package_id' => 'circuit', 'transport_offer_id' => 'offre de transport',
            'pickup_location' => 'lieu de prise en charge', 'dropoff_location' => 'destination',
        ]);

        // Une heure déjà passée aujourd'hui (heure d'Abidjan) ne peut pas être réservée.
        if (! empty($validated['booking_time'])) {
            $when = \Carbon\CarbonImmutable::parse($validated['booking_date'].' '.$validated['booking_time'], 'Africa/Abidjan');
            if ($when->isPast()) {
                return back()->withInput()->withErrors(['booking_time' => __('Cette heure est déjà passée : choisissez une heure à venir.')], 'booking');
            }
        }

        $modelClass = BookingRequest::TYPES[$validated['bookable_type']]['model'];
        if ($validated['bookable_type'] === BookingRequest::TYPE_ART) {
            // Commande sur mesure : la cible est l'artiste lui-même (fiche prestataire Art & Créations).
            $target = Provider::where('status', 'active')->find($validated['bookable_id']);
            $provider = $target && $target->isArtist() ? $target : null;
            if ($provider && trim((string) ($validated['message'] ?? '')) === '') {
                return back()->withInput()->withErrors(['message' => __('Décrivez l\'œuvre souhaitée (format, technique, sujet, délai…).')], 'booking');
            }
        } else {
            $target = $modelClass::query()->where('is_active', true)->find($validated['bookable_id']);
            $provider = $target?->provider_id ? Provider::find($target->provider_id) : null;
        }
        if (! $target || ! $provider) {
            return back()->withInput()->withErrors(['bookable_id' => __('Cet établissement ne prend pas de réservation en ligne.')], 'booking');
        }

        // Code promo de l'établissement : vérifié maintenant, déduit du montant fixé à la confirmation.
        $promo = app(\App\Services\ProviderPromoService::class)->check($provider->id, $validated['promo_code'] ?? null);
        if ($promo['error']) {
            return back()->withInput()->withErrors(['promo_code' => $promo['error']], 'booking');
        }
        $promo = $promo['promo'];

        $itemName = null;
        $itemId = null;
        $suggestedAmount = null;
        $departure = null;
        $customQuote = $validated['bookable_type'] === BookingRequest::TYPE_TOUR && $request->boolean('custom_quote');
        if ($customQuote) {
            // Devis sur mesure : l'agence répond avec un programme et un prix, payable ensuite en ligne.
            $itemName = __('Voyage sur mesure');
        } elseif ($validated['bookable_type'] === BookingRequest::TYPE_TOUR) {
            $tour = TourPackage::query()->available()
                ->where('provider_id', $provider->id)
                ->find($validated['tour_package_id'] ?? 0);
            if (! $tour) {
                return back()->withInput()->withErrors(['tour_package_id' => __('Choisissez un circuit disponible.')], 'booking');
            }
            if ($tour->max_participants && $validated['party_size'] > $tour->max_participants) {
                return back()->withInput()->withErrors(['party_size' => 'Ce circuit accueille au maximum '.$tour->max_participants.' voyageurs.'], 'booking');
            }
            $itemName = $tour->name;
            $itemId = $tour->id;
            $suggestedAmount = (int) $tour->price_xof * (int) $validated['party_size'];

            // Circuit à départs programmés : la date doit être un départ ouvert, avec assez de places.
            if ($tour->upcomingDepartures()->exists()) {
                $departure = $tour->upcomingDepartures()->whereDate('departure_date', $validated['booking_date'])->first();
                if (! $departure) {
                    return back()->withInput()->withErrors(['booking_date' => __('Choisissez l\'une des dates de départ proposées.')], 'booking');
                }
                $suggestedAmount = $departure->pricePerPerson() * (int) $validated['party_size'];
            }
        }

        if ($validated['bookable_type'] === BookingRequest::TYPE_LEISURE && ! empty($validated['activity_id'])) {
            $activity = \App\Models\Activity::query()->where('is_available', true)->where('provider_id', $provider->id)->find($validated['activity_id']);
            if (! $activity) {
                return back()->withInput()->withErrors(['activity_id' => __('Choisissez une activité disponible.')], 'booking');
            }
            if ($activity->max_participants && $validated['party_size'] > $activity->max_participants) {
                return back()->withInput()->withErrors(['party_size' => __('Cette activité accueille au maximum :max participants.', ['max' => $activity->max_participants])], 'booking');
            }
            $itemName = $activity->name;
            $itemId = $activity->id;
            $suggestedAmount = $activity->price_xof ? (int) $activity->price_xof * (int) $validated['party_size'] : null;
        }

        if ($validated['bookable_type'] === BookingRequest::TYPE_TRANSPORT) {
            $offer = \App\Models\TransportOffer::query()->where('is_available', true)
                ->where('provider_id', $provider->id)
                ->find($validated['transport_offer_id'] ?? 0);
            if (! $offer) {
                return back()->withInput()->withErrors(['transport_offer_id' => __('Choisissez une offre de transport disponible.')], 'booking');
            }
            if ($offer->max_passengers && $validated['party_size'] > $offer->max_passengers) {
                return back()->withInput()->withErrors(['party_size' => __('Ce véhicule accueille au maximum :max passagers.', ['max' => $offer->max_passengers])], 'booking');
            }
            $itemName = $offer->name;
            $itemId = $offer->id;
            // Prix de la course ou de la location : l'entreprise le confirme ou l'ajuste.
            $suggestedAmount = (int) $offer->price_xof ?: null;
        }

        // Commande au restaurant : plats disponibles de la carte, quantités, retrait ou livraison.
        $orderItems = null;
        $fulfillment = null;
        if ($validated['bookable_type'] === BookingRequest::TYPE_RESTAURANT_ORDER) {
            if (! $target->accepts_orders) {
                return back()->withInput()->withErrors(['order' => __('Ce restaurant ne prend pas de commande en ligne.')], 'booking');
            }
            $quantities = collect($validated['order'] ?? [])->map(fn ($q) => (int) $q)->filter(fn ($q) => $q > 0);
            $dishes = \App\Models\MenuItem::query()->available()->where('provider_id', $provider->id)
                ->whereIn('id', $quantities->keys()->map(fn ($id) => (int) $id))->get()->keyBy('id');
            $orderItems = $dishes->map(fn ($d) => ['id' => $d->id, 'name' => $d->name, 'qty' => $quantities[$d->id], 'price_xof' => (int) $d->price_xof])->values()->all();
            if (! $orderItems) {
                return back()->withInput()->withErrors(['order' => __('Choisissez au moins un plat de la carte.')], 'booking');
            }
            $fulfillment = $validated['fulfillment'] ?? 'pickup';
            if ($fulfillment === 'delivery' && ! $target->offers_delivery) {
                return back()->withInput()->withErrors(['fulfillment' => __('Ce restaurant ne livre pas : choisissez « À emporter ».')], 'booking');
            }
            if ($fulfillment === 'delivery' && trim((string) ($validated['delivery_address'] ?? '')) === '') {
                return back()->withInput()->withErrors(['delivery_address' => __('Indiquez l\'adresse de livraison.')], 'booking');
            }
            if (empty($validated['booking_time'])) {
                return back()->withInput()->withErrors(['booking_time' => __('Indiquez l\'heure de retrait ou de livraison souhaitée.')], 'booking');
            }
            if (! app(\App\Services\TableSlots::class)->isServing($target, $validated['booking_date'], $validated['booking_time'])) {
                return back()->withInput()->withErrors(['booking_time' => __('Le restaurant est fermé à cette heure-là.')], 'booking');
            }
            $validated['party_size'] = min(200, collect($orderItems)->sum('qty'));
            $suggestedAmount = collect($orderItems)->sum(fn ($l) => $l['qty'] * $l['price_xof']);
            $itemName = \Illuminate\Support\Str::limit(__(BookingRequest::FULFILLMENT_LABELS[$fulfillment]).' · '
                .collect($orderItems)->map(fn ($l) => $l['qty'].' × '.$l['name'])->join(', '), 250);
            if ($fulfillment === 'delivery') {
                // Affiché comme un trajet « restaurant → adresse » dans les listes et e-mails existants.
                $validated['pickup_location'] = $target->name;
                $validated['dropoff_location'] = trim($validated['delivery_address']);
            }
        }

        if ($validated['bookable_type'] === BookingRequest::TYPE_BUNDLE) {
            $people = (int) $validated['party_size'];
            if ($people < $target->min_people || ($target->max_people && $people > $target->max_people)) {
                return back()->withInput()->withErrors(['party_size' => __('Cette offre se réserve pour :min à :max voyageurs.', ['min' => $target->min_people, 'max' => $target->max_people ?: '∞'])], 'booking');
            }
            $suggestedAmount = $target->totalFor($people);
        }

        // Restaurant avec couverts par créneau : contrôle des places (verrou contre les demandes simultanées)
        // et confirmation instantanée si le restaurant l'a activée.
        $slots = app(\App\Services\TableSlots::class);
        $tableManaged = $validated['bookable_type'] === BookingRequest::TYPE_RESTAURANT && $slots->enabled($target);
        $autoConfirmed = false;
        if ($tableManaged) {
            $time = $validated['booking_time'] ?? null;
            $party = (int) $validated['party_size'];
            if (! $time) {
                return back()->withInput()->withErrors(['booking_time' => __('Choisissez l\'heure de votre table.')], 'booking');
            }
            if ($target->max_party_size && $party > $target->max_party_size) {
                return back()->withInput()->withErrors(['party_size' => __('Au-delà de :n couverts, contactez directement le restaurant.', ['n' => $target->max_party_size])], 'booking');
            }
            if (! $slots->isOpenAt($target, $validated['booking_date'], $time)) {
                return back()->withInput()->withErrors(['booking_time' => __('Le restaurant ne prend pas de réservation à cette heure-là (dernière table une heure avant la fermeture).')], 'booking');
            }
        }

        // Plan de salle : table précise choisie par le client (facultatif).
        $chosenTable = null;
        if ($validated['bookable_type'] === BookingRequest::TYPE_RESTAURANT && ! empty($validated['restaurant_table_id'])) {
            $chosenTable = \App\Models\RestaurantTable::query()->active()
                ->where('restaurant_id', $target->id)->find($validated['restaurant_table_id']);
            if (! $chosenTable) {
                return back()->withInput()->withErrors(['restaurant_table_id' => __('Cette table n\'est plus proposée : choisissez-en une autre.')], 'booking');
            }
            if (empty($validated['booking_time'])) {
                return back()->withInput()->withErrors(['booking_time' => __('Choisissez l\'heure de votre table.')], 'booking');
            }
            if ($chosenTable->seats < (int) $validated['party_size']) {
                return back()->withInput()->withErrors(['restaurant_table_id' => __('La table :t compte :n places : choisissez une table plus grande.', ['t' => $chosenTable->label, 'n' => $chosenTable->seats])], 'booking');
            }
            if (! $tableManaged && ! $slots->isOpenAt($target, $validated['booking_date'], $validated['booking_time'])) {
                return back()->withInput()->withErrors(['booking_time' => __('Le restaurant ne prend pas de réservation à cette heure-là (dernière table une heure avant la fermeture).')], 'booking');
            }
            $chosenTable->setRelation('restaurant', $target);
            $itemName = $chosenTable->displayName();
        }
        $tableTaken = false;

        $booking = \Illuminate\Support\Facades\DB::transaction(function () use ($tableManaged, $slots, $target, $validated, $provider, $itemName, $itemId, $customQuote, $departure, $suggestedAmount, $orderItems, $fulfillment, $chosenTable, &$tableTaken, &$autoConfirmed, &$promo) {
            if ($chosenTable) {
                // Verrou sur la table : deux clients ne peuvent pas réserver la même table au même moment.
                \App\Models\RestaurantTable::whereKey($chosenTable->id)->lockForUpdate()->first();
                if (! $slots->isTableFree($chosenTable, $validated['booking_date'], $validated['booking_time'])) {
                    $tableTaken = true;

                    return null;
                }
            }
            if ($departure) {
                $locked = \App\Models\TourDeparture::whereKey($departure->id)->lockForUpdate()->first();
                if ($locked->seatsLeft() < (int) $validated['party_size']) {
                    return null;
                }
            }
            if ($tableManaged) {
                \App\Models\Restaurant::whereKey($target->id)->lockForUpdate()->first();
                if ($slots->remaining($target, $validated['booking_date'], $validated['booking_time']) < (int) $validated['party_size']) {
                    return null;
                }
                $autoConfirmed = (bool) $target->auto_confirm;
            } elseif ($chosenTable) {
                $autoConfirmed = (bool) $target->auto_confirm;
            }

            if ($promo && ! app(\App\Services\ProviderPromoService::class)->consume($promo)) {
                $promo = null;
            }

            return BookingRequest::create([
            'provider_id' => $provider->id,
            'user_id' => Auth::id(),
            'bookable_type' => $validated['bookable_type'],
            'bookable_id' => $target->id,
            'bookable_name' => $target->name ?? $target->title,
            'bundle_id' => $target instanceof \App\Models\Bundle ? $target->id : null,
            'item_name' => $itemName,
            'item_id' => $itemId,
            'restaurant_table_id' => $chosenTable?->id,
            'order_items' => $orderItems,
            'fulfillment' => $fulfillment,
            'is_custom_quote' => $customQuote,
            'promo_code' => $promo?->code,
            'pickup_location' => $validated['pickup_location'] ?? null,
            'dropoff_location' => $validated['dropoff_location'] ?? null,
            // Prix indicatif (circuit) : l'établissement le confirme ou l'ajuste en répondant.
            'amount_due_xof' => $suggestedAmount ?: null,
            'booking_date' => $validated['booking_date'],
            'booking_time' => $validated['booking_time'] ?? null,
            'party_size' => $validated['party_size'],
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'message' => $validated['message'] ?? null,
            'status' => $autoConfirmed ? BookingRequest::STATUS_CONFIRMED : BookingRequest::STATUS_NEW,
            'answered_at' => $autoConfirmed ? now() : null,
            ]);
        });

        if (! $booking && $tableTaken) {
            return back()->withInput()->withErrors(['restaurant_table_id' => __('La table :t vient d\'être réservée à cette heure : choisissez une autre table.', ['t' => $chosenTable->label])], 'booking');
        }
        if (! $booking && $departure) {
            return back()->withInput()->withErrors(['party_size' => __('Plus assez de places pour ce départ (:n restante(s)).', ['n' => $departure->fresh()->seatsLeft()])], 'booking');
        }
        if (! $booking) {
            $alt = $slots->suggestions($target, $validated['booking_date'], $validated['booking_time'], (int) $validated['party_size']);

            return back()->withInput()->withErrors(['booking_time' => $alt
                ? __('Complet à :t. Créneaux disponibles : :alt.', ['t' => $validated['booking_time'], 'alt' => implode(', ', $alt)])
                : __('Complet ce jour-là pour :n couverts. Essayez une autre date.', ['n' => $validated['party_size']])], 'booking');
        }

        if ($autoConfirmed) {
            Mail::to($booking->email)->queue(new BookingRequestMail($booking, 'confirmed'));
        }

        $provider->user?->notify(new VisitorSystemNotification(
            $autoConfirmed ? 'Nouvelle table réservée (confirmée automatiquement)' : 'Nouvelle demande de réservation',
            $booking->full_name.' — '.$booking->party_size.' '.$booking->unitLabel().' le '.$booking->booking_date->format('d/m/Y').($itemName ? ' ('.$itemName.')' : '').'.',
            route('provider.booking-requests.index'),
        ));
        $providerEmail = $provider->email ?: $provider->user?->email;
        if ($providerEmail) {
            Mail::to($providerEmail)->queue(new BookingRequestMail($booking, 'received'));
        }
        app(\App\Services\TextMessenger::class)->sendLater($provider->phone, 'Trésors d\'Ivoire : demande '.$booking->reference.' — '.$booking->full_name.', '.$booking->party_size.' '.$booking->unitLabel().' le '.$booking->booking_date->format('d/m').($booking->booking_time ? ' à '.$booking->booking_time : '').'. Répondez depuis votre espace.');

        if ($booking->bookable_type === BookingRequest::TYPE_RESTAURANT_ORDER) {
            // Le panier du restaurant est vidé côté navigateur à l'affichage de ce message.
            return back()->with('order_placed', $target->id)->with('booking_success',
                __('Commande envoyée (:ref). Le restaurant la confirme avec son prix par e-mail ; suivez-la dans « Mes réservations ».', ['ref' => $booking->reference]));
        }

        return back()->with('booking_success', $autoConfirmed
            ? __('Table confirmée (:ref) ! Un e-mail de confirmation vous a été envoyé.', ['ref' => $booking->reference])
            : 'Demande envoyée ('.$booking->reference.'). L\'établissement vous répond par e-mail ; suivez-la dans « Mes réservations ».');
    }

    public function index(): View
    {
        $bookings = BookingRequest::ownedBy(Auth::user())->latest('booking_date')->paginate(20);

        return view('visitor.booking-requests.index', compact('bookings'));
    }

    public function cancel(BookingRequest $bookingRequest): RedirectResponse
    {
        abort_unless((int) $bookingRequest->user_id === (int) Auth::id(), 404);

        if (! $bookingRequest->isOpen()) {
            return back()->with('error', __('Cette demande ne peut plus être annulée.'));
        }

        $paid = $bookingRequest->payment_status === BookingRequest::PAYMENT_PAID;
        $bookingRequest->update([
            'status' => BookingRequest::STATUS_CANCELLED,
            'cancelled_at' => now(),
            // Montant payé en ligne : dû au client, la part du prestataire reste en attente.
            'refund_due_xof' => $paid ? (int) $bookingRequest->amount_due_xof : null,
        ]);
        if ($paid) {
            \Illuminate\Support\Facades\Notification::send(
                \App\Models\User::query()->where('role', 'admin')->where('is_active', true)->get(),
                new VisitorSystemNotification('Remboursement à effectuer', 'Demande '.$bookingRequest->reference.' annulée par le client : '.number_format((int) $bookingRequest->amount_due_xof, 0, ',', ' ').' XOF payés en ligne à rembourser.', route('admin.booking-requests.index', ['statut' => 'cancelled']))
            );
        }

        $provider = $bookingRequest->provider;
        $provider?->user?->notify(new VisitorSystemNotification(
            'Demande de réservation annulée',
            $bookingRequest->full_name.' a annulé sa demande '.$bookingRequest->reference.' du '.$bookingRequest->booking_date->format('d/m/Y').'.',
            route('provider.booking-requests.index'),
        ));
        $providerEmail = $provider?->email ?: $provider?->user?->email;
        if ($providerEmail) {
            Mail::to($providerEmail)->queue(new BookingRequestMail($bookingRequest, 'cancelled'));
        }

        return back()->with('success', $paid ? __('Demande annulée. Votre paiement vous sera remboursé.') : __('Demande annulée.'));
    }
}
