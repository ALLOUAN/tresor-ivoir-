<?php

namespace Tests\Feature;

use App\Models\Provider;
use App\Models\ProviderCategory;
use App\Models\TouristCity;
use App\Models\TransportBooking;
use App\Models\TransportCategory;
use App\Models\TransportCompany;
use App\Models\TransportDriver;
use App\Models\TransportOffer;
use App\Models\TransportTrip;
use App\Models\TransportVehicle;
use App\Models\User;
use App\Models\Wallet;
use App\Services\TransportBookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Transports « marché » : transfert à prix fixe avec vol, devis chauffeur au km, flotte sans double
 * réservation, location (documents, état des lieux), lignes avec sièges et billet QR, options, suivi
 * du chauffeur, annulation et modification.
 */
class TransportMarketFeaturesTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Provider $provider;

    private User $client;

    private int $catId;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        $this->owner = User::factory()->provider()->create();
        $cat = ProviderCategory::firstOrCreate(['slug' => 'transports'], ['name_fr' => 'Transports', 'name_en' => 'Transport', 'is_active' => true]);
        $this->provider = Provider::create(['user_id' => $this->owner->id, 'category_id' => $cat->id, 'name' => 'Ivoire Cars', 'slug' => 'ivoire-cars', 'status' => 'active']);
        $city = TouristCity::create(['name' => 'Abidjan', 'slug' => 'abj-'.uniqid(), 'is_active' => true]);
        TransportCompany::create(['provider_id' => $this->provider->id, 'city_id' => $city->id, 'name' => 'Ivoire Cars', 'slug' => 'ivoire-cars', 'is_active' => true, 'phone' => '0707']);
        $this->catId = TransportCategory::firstOrCreate(['slug' => 'transferts'], ['name_fr' => 'Transferts', 'name_en' => 'Transfers', 'is_active' => true])->id;
        $this->client = User::factory()->create(['role' => 'visitor']);
        Wallet::forUser($this->client)->update(['balance_available_xof' => 1000000]);
    }

    private function offer(array $attrs): TransportOffer
    {
        return TransportOffer::create($attrs + ['provider_id' => $this->provider->id, 'category_id' => $this->catId, 'is_available' => true, 'instant_booking' => true, 'price_xof' => 0, 'free_cancellation_hours' => 24]);
    }

    private function transfer(array $extra = []): TransportOffer
    {
        return $this->offer($extra + [
            'name' => 'Transfert aéroport', 'mode' => 'transfer', 'vehicle_category' => 'berline', 'origin_label' => 'Aéroport FHB',
            'zone_prices' => [['label' => 'Plateau', 'price_xof' => 15000], ['label' => 'Assinie', 'price_xof' => 45000]],
            'round_trip_discount_percent' => 10, 'free_waiting_minutes' => 60, 'night_surcharge_percent' => 20, 'max_passengers' => 4,
            'extras' => [['label' => 'Siège enfant', 'price_xof' => 2000, 'per' => 'booking']],
        ]);
    }

    private function book(TransportOffer $o, array $in, ?User $as = null)
    {
        return $this->actingAs($as ?? $this->client)->post(route('transport-offer.book', $o->slug), $in + ['payment_method' => 'wallet', 'phone' => '0707070707']);
    }

    private function at(int $days, string $time): string
    {
        return now('Africa/Abidjan')->addDays($days)->format('Y-m-d').'T'.$time;
    }

    public function test_airport_transfer_fixed_price_flight_number_round_trip_night_and_extras(): void
    {
        $o = $this->transfer();
        $this->get(route('transport-offer.show', $o->slug))->assertOk()->assertSee('Suivi de votre vol')->assertSee('60 min d\'attente gratuite')->assertSee('Assinie');
        $this->book($o, ['direction' => 'from', 'zone' => 1, 'starts_at' => $this->at(3, '10:00')])->assertSessionHasErrors('booking', null, 'transport'); // vol obligatoire

        $this->book($o, ['direction' => 'from', 'zone' => 1, 'address' => 'Hôtel Assinie', 'starts_at' => $this->at(3, '23:30'), 'flight_number' => 'af 702',
            'round_trip' => 1, 'return_at' => $this->at(7, '14:00'), 'extras' => [0], 'passengers' => 2])->assertRedirect();
        $b = TransportBooking::sole();
        // 45 000 + nuit 20 % (9 000) + retour 45 000 − 10 % de 99 000 + siège enfant 2 000
        $this->assertSame(45000 + 9000 + 45000 - 9000 + 2000, $b->amount_total_xof); // remise sur les tarifs de base, pas sur le supplément de nuit
        $this->assertSame('AF702', $b->flight_number);
        $this->assertSame('Aéroport FHB', $b->pickup_label);
        $this->assertSame('Hôtel Assinie', $b->dropoff_label);
        $this->assertTrue($b->round_trip);
        $this->assertSame(TransportBooking::STATUS_PAID, $b->status);
        $this->get($b->confirmationUrl())->assertOk()->assertSee('AF702')->assertSee('Transport confirmé');
    }

    public function test_chauffeur_instant_quote_by_distance_with_minimum_and_packages(): void
    {
        $o = $this->offer(['name' => 'Chauffeur privé', 'mode' => 'chauffeur', 'base_fare_xof' => 2000, 'per_km_xof' => 400, 'per_hour_xof' => 3000,
            'minimum_xof' => 8000, 'half_day_xof' => 35000, 'full_day_xof' => 60000, 'included_km' => 150]);
        // Plateau → Riviera : environ 7,5 km à vol d'oiseau, × 1,3.
        $q = $this->getJson(route('transport-offer.quote', $o->slug).'?'.http_build_query(['package' => 'trip', 'starts_at' => $this->at(2, '09:00'),
            'pickup_lat' => 5.3200, 'pickup_lng' => -4.0170, 'dropoff_lat' => 5.3600, 'dropoff_lng' => -3.9600]))->assertOk()->json();
        $km = TransportBookingService::distanceKm(5.32, -4.017, 5.36, -3.96);
        $this->assertGreaterThan(8, $km);
        $minutes = (int) ceil($km / 35 * 60) + 15;
        $this->assertSame(max(8000, 2000 + (int) round($km * 400) + (int) round($minutes / 60 * 3000)), $q['total']);

        $short = $this->getJson(route('transport-offer.quote', $o->slug).'?'.http_build_query(['package' => 'trip', 'starts_at' => $this->at(2, '09:00'),
            'pickup_lat' => 5.3200, 'pickup_lng' => -4.0170, 'dropoff_lat' => 5.3210, 'dropoff_lng' => -4.0160]))->json();
        $this->assertSame(8000, $short['total'], 'Trajet très court : tarif minimum.');

        $this->getJson(route('transport-offer.quote', $o->slug).'?'.http_build_query(['package' => 'full_day', 'starts_at' => $this->at(2, '08:00')]))->assertJson(['total' => 60000]);
        $this->getJson(route('transport-offer.quote', $o->slug).'?'.http_build_query(['package' => 'trip', 'starts_at' => $this->at(2, '09:00')]))->assertStatus(422);
    }

    public function test_fleet_prevents_double_booking_and_respects_maintenance(): void
    {
        $o = $this->transfer();
        $car = TransportVehicle::create(['provider_id' => $this->provider->id, 'name' => 'Corolla', 'category' => 'berline', 'seats' => 4, 'luggage' => 3]);
        $in = ['direction' => 'to', 'zone' => 0, 'address' => 'Plateau', 'starts_at' => $this->at(3, '10:00')];
        $this->book($o, $in)->assertRedirect();
        $this->assertSame($car->id, TransportBooking::sole()->transport_vehicle_id);

        $other = User::factory()->create(['role' => 'visitor']);
        Wallet::forUser($other)->update(['balance_available_xof' => 100000]);
        $this->book($o, ['starts_at' => $this->at(3, '10:30')] + $in, $other)->assertSessionHasErrors('booking', null, 'transport');
        $this->book($o, ['starts_at' => $this->at(3, '14:00')] + $in, $other)->assertRedirect(); // après le premier trajet

        $car->update(['unavailable_from' => now()->addDays(5)->toDateString(), 'unavailable_to' => now()->addDays(6)->toDateString()]);
        $this->book($o, ['starts_at' => $this->at(5, '10:00')] + $in, $other)->assertSessionHasErrors('booking', null, 'transport');
        $this->assertSame(2, TransportBooking::where('status', 'paid')->count());
    }

    public function test_rental_days_weekly_rate_deposit_documents_and_inspection(): void
    {
        Storage::fake('local');
        $o = $this->offer(['name' => 'Location SUV', 'mode' => 'rental', 'vehicle_category' => 'suv', 'price_xof' => 40000, 'weekly_daily_xof' => 32000,
            'deposit_xof' => 200000, 'included_km' => 200, 'extra_km_xof' => 150,
            'extras' => [['label' => 'GPS', 'price_xof' => 2000, 'per' => 'day']]]);
        $this->book($o, ['starts_at' => $this->at(2, '10:00'), 'return_at' => $this->at(9, '10:00'), 'extras' => [0]])->assertRedirect();
        $b = TransportBooking::sole();
        $this->assertSame(7 * 32000 + 7 * 2000, $b->amount_total_xof);
        $this->assertSame(200000, $b->deposit_on_site_xof);
        $this->assertTrue($b->needsDocuments());

        $this->actingAs($this->client)->post(route('visitor.transport-bookings.documents', $b), [
            'license' => UploadedFile::fake()->image('permis.jpg'), 'id_card' => UploadedFile::fake()->image('cni.jpg'), 'license_number' => 'CI-123', 'accept_contract' => 1,
        ])->assertSessionHas('success');
        $b->refresh();
        $this->assertFalse($b->needsDocuments());
        Storage::disk('local')->assertExists($b->documents['license']);
        $this->actingAs($this->owner)->get(route('provider.transport-bookings.file', [$b, 'license']))->assertOk();
        $this->actingAs(User::factory()->provider()->create())->get(route('provider.transport-bookings.file', [$b, 'license']))->assertStatus(404);

        $this->actingAs($this->owner)->post(route('provider.transport-bookings.inspection', $b), ['stage' => 'out', 'km' => 10000, 'fuel' => 100])->assertSessionHas('success');
        $this->actingAs($this->owner)->post(route('provider.transport-bookings.inspection', $b), ['stage' => 'in', 'km' => 11600, 'fuel' => 75])->assertSessionHas('success');
        $in = $b->fresh()->inspection['in'];
        $this->assertSame(1600, $in['driven_km']);
        $this->assertSame(200, $in['extra_km']);                 // 1 600 − 7 × 200
        $this->assertSame(30000, $in['extra_km_xof']);
    }

    public function test_line_seats_are_exclusive_and_ticket_is_scanned_at_boarding(): void
    {
        $o = $this->offer(['name' => 'Abidjan → Yamoussoukro', 'mode' => 'line', 'route_from' => 'Abidjan', 'route_to' => 'Yamoussoukro', 'price_xof' => 6000, 'trip_minutes' => 180]);
        $trip = TransportTrip::create(['transport_offer_id' => $o->id, 'departs_at' => now()->addDays(2)->setTime(7, 0), 'seats_total' => 8, 'seats_per_row' => 4, 'is_active' => true]);
        $this->book($o, ['trip_id' => $trip->id, 'seats' => [3, 4]])->assertRedirect();
        $b = TransportBooking::sole();
        $this->assertSame(12000, $b->amount_total_xof);
        $this->assertSame([3, 4], $b->seats);
        $this->getJson(route('transport-offer.seats', [$o->slug, $trip->id]))->assertJson(['taken' => [3, 4]]);

        $other = User::factory()->create(['role' => 'visitor']);
        Wallet::forUser($other)->update(['balance_available_xof' => 100000]);
        $this->book($o, ['trip_id' => $trip->id, 'seats' => [4, 5]], $other)->assertSessionHasErrors('booking', null, 'transport');

        $code = $b->ticketCode();
        $this->get($b->confirmationUrl())->assertSee('Votre billet')->assertSee($code);
        $this->actingAs($this->owner)->get(route('provider.tickets.show', $code))->assertOk()->assertSee('pas encore valable');
        $trip->update(['departs_at' => now()->addHour()]);
        $b->update(['starts_at' => now()->addHour()]);
        $this->actingAs($this->owner)->post(route('provider.tickets.check-in', $code))->assertSessionHas('success');
        $this->assertNotNull($b->fresh()->checked_in_at);
    }

    public function test_driver_assignment_and_trip_status_notify_the_client(): void
    {
        $o = $this->transfer();
        $this->book($o, ['direction' => 'to', 'zone' => 0, 'address' => 'Plateau', 'starts_at' => $this->at(2, '08:00')]);
        $b = TransportBooking::sole();
        $driver = TransportDriver::create(['provider_id' => $this->provider->id, 'name' => 'Moussa', 'phone' => '0102030405']);
        $car = TransportVehicle::create(['provider_id' => $this->provider->id, 'name' => 'Corolla', 'category' => 'berline', 'plate' => 'AB-123-CI', 'seats' => 4, 'luggage' => 2]);
        $this->actingAs($this->owner)->post(route('provider.transport-bookings.assign', $b), ['transport_driver_id' => $driver->id, 'transport_vehicle_id' => $car->id])->assertSessionHas('success');
        $this->actingAs($this->owner)->post(route('provider.transport-bookings.status', $b), ['trip_status' => 'en_route'])->assertSessionHas('success');
        $b->refresh();
        $this->assertSame('en_route', $b->trip_status);
        Notification::assertSentTo($this->client, \App\Notifications\VisitorSystemNotification::class);
        $this->actingAs($this->client)->get(route('visitor.transport-bookings.index'))->assertSee('Moussa')->assertSee('AB-123-CI')->assertSee('Chauffeur en route');
        $this->actingAs($this->owner)->get(route('provider.transport-bookings.index', ['date' => now('Africa/Abidjan')->addDays(2)->toDateString()]))->assertOk()->assertSee($b->reference);
    }

    public function test_free_cancellation_refund_and_modification(): void
    {
        $o = $this->transfer();
        $this->book($o, ['direction' => 'from', 'zone' => 0, 'starts_at' => $this->at(4, '10:00'), 'flight_number' => 'HF500'])->assertRedirect();
        $b = TransportBooking::sole();
        $this->actingAs($this->client)->post(route('visitor.transport-bookings.modify', $b), ['starts_at' => $this->at(4, '12:30'), 'flight_number' => 'HF502'])->assertSessionHas('success');
        $b->refresh();
        $this->assertSame('12:30', $b->starts_at->timezone('Africa/Abidjan')->format('H:i'));
        $this->assertSame('HF502', $b->flight_number);

        $this->actingAs($this->client)->post(route('visitor.transport-bookings.cancel', $b))->assertSessionHas('success');
        $this->assertSame(TransportBooking::STATUS_REFUNDED, $b->fresh()->status);
        $this->assertSame(1000000, Wallet::forUser($this->client)->fresh()->balance_available_xof);
        $this->assertSame(0, Wallet::forProvider($this->provider)->fresh()->balance_available_xof);

        $this->book($o, ['direction' => 'from', 'zone' => 0, 'starts_at' => $this->at(4, '10:00'), 'flight_number' => 'HF500']);
        $late = TransportBooking::latest('id')->first();
        $late->update(['cancellable_until' => now()->subMinute()]);
        $this->actingAs($this->client)->post(route('visitor.transport-bookings.cancel', $late))->assertSessionHas('error');
    }

    public function test_provider_saves_market_fields_fleet_and_line_departures(): void
    {
        $o = $this->offer(['name' => 'Navette Assinie', 'mode' => null, 'price_xof' => 10000]);
        $this->actingAs($this->owner)->put(route('provider.transport.update', $o), [
            'name' => 'Navette Assinie', 'category_id' => $this->catId, 'price_xof' => 10000, 'mode' => 'line', 'route_from' => 'Abidjan', 'route_to' => 'Assinie',
            'trip_minutes' => 120, 'free_cancellation_hours' => 24, 'extras' => [['label' => 'Bagage en plus', 'price_xof' => 1000, 'per' => 'passenger']],
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame('line', $o->fresh()->mode);

        $this->actingAs($this->owner)->put(route('provider.transport.update', $o), ['name' => 'X', 'category_id' => $this->catId, 'price_xof' => 1, 'mode' => 'transfer'])->assertSessionHasErrors('zones');

        $this->actingAs($this->owner)->post(route('provider.transport-fleet.vehicle'), ['name' => 'Coaster', 'category' => 'minibus', 'seats' => 30, 'luggage' => 30])->assertSessionHas('success');
        $this->actingAs($this->owner)->post(route('provider.transport-fleet.driver'), ['name' => 'Koné', 'phone' => '0700'])->assertSessionHas('success');
        $this->actingAs($this->owner)->post(route('provider.transport-trips.store'), ['transport_offer_id' => $o->id, 'date' => now()->addDay()->toDateString(),
            'times' => '07:00, 15:00', 'repeat_days' => 6, 'seats_total' => 30, 'seats_per_row' => 4])->assertSessionHas('success');
        $this->assertSame(14, TransportTrip::count());
        foreach (['provider.transport-bookings.index', 'provider.transport-fleet.index', 'provider.transport-trips.index'] as $r) {
            $this->actingAs($this->owner)->get(route($r))->assertOk();
        }
        $this->get(route('transport-offer.show', $o->slug))->assertOk()->assertSee('Choix du siège');
        $this->get(route('transport-company.show', 'ivoire-cars'))->assertSee(route('transport-offer.show', $o->slug), false);
    }
}
