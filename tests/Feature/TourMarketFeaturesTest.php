<?php

namespace Tests\Feature;

use App\Models\Provider;
use App\Models\ProviderCategory;
use App\Models\TourBooking;
use App\Models\TourCategory;
use App\Models\TourDeparture;
use App\Models\TourPackage;
use App\Models\TouristCity;
use App\Models\TravelAgency;
use App\Models\User;
use App\Models\Wallet;
use App\Services\TourBookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Circuits « marché » : page détaillée, réservation immédiate (acompte puis solde), départ garanti,
 * tarifs (enfant, chambre individuelle, dégressif, options), prise en charge, annulation, voyageurs.
 */
class TourMarketFeaturesTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Provider $provider;

    private TravelAgency $agency;

    private TourPackage $tour;

    private User $client;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        $this->owner = User::factory()->provider()->create();
        $cat = ProviderCategory::firstOrCreate(['slug' => 'agences-voyages'], ['name_fr' => 'Agences de Voyages', 'name_en' => 'Travel agencies', 'is_active' => true]);
        $this->provider = Provider::create(['user_id' => $this->owner->id, 'category_id' => $cat->id, 'name' => 'Ivoire Tours', 'slug' => 'ivoire-tours', 'status' => 'active']);
        $city = TouristCity::create(['name' => 'Abidjan', 'slug' => 'abj-'.uniqid(), 'is_active' => true]);
        $this->agency = TravelAgency::create(['provider_id' => $this->provider->id, 'city_id' => $city->id, 'name' => 'Ivoire Tours', 'slug' => 'ivoire-tours', 'is_active' => true]);
        $tc = TourCategory::firstOrCreate(['slug' => 'nature'], ['name_fr' => 'Nature', 'name_en' => 'Nature', 'is_active' => true]);
        $this->tour = TourPackage::create([
            'provider_id' => $this->provider->id, 'category_id' => $tc->id, 'name' => 'Ouest montagneux', 'price_xof' => 100000,
            'duration_days' => 3, 'max_participants' => 10, 'is_available' => true,
            'itinerary' => [['title' => 'Abidjan → Man', 'description' => 'Route', 'meals' => 'Dîner', 'accommodation' => 'Hôtel à Man'], ['title' => 'La Dent de Man']],
            'included' => "Transport\nHébergement", 'not_included' => 'Boissons',
            'pickup_points' => [['label' => 'Plateau', 'time' => '07:00', 'note' => ''], ['label' => 'Cocody', 'time' => '07:20', 'note' => '']],
            'child_price_xof' => 60000, 'single_supplement_xof' => 20000,
            'group_prices' => [['min' => 4, 'price_xof' => 90000]],
            'extras' => [['label' => 'Nuit en plus', 'price_xof' => 30000, 'per' => 'person'], ['label' => 'Transfert', 'price_xof' => 10000, 'per' => 'booking']],
            'min_participants' => 6, 'guarantee_days' => 7, 'deposit_percent' => 30, 'balance_due_days' => 15,
            'cancellation_policy' => [['days' => 30, 'percent' => 90], ['days' => 14, 'percent' => 50], ['days' => 0, 'percent' => 0]],
            'instant_booking' => true,
        ]);
        $this->client = User::factory()->create(['role' => 'visitor']);
        Wallet::forUser($this->client)->update(['balance_available_xof' => 2000000]);
    }

    private function departure(int $inDays, int $seats = 12, ?int $min = null): TourDeparture
    {
        return $this->tour->departures()->create(['departure_date' => now('Africa/Abidjan')->addDays($inDays)->toDateString(), 'seats_total' => $seats, 'min_participants' => $min, 'is_active' => true]);
    }

    private function book(TourDeparture $d, array $o = [], ?User $as = null)
    {
        return $this->actingAs($as ?? $this->client)->post(route('travel-agency.tour.book', [$this->agency->slug, $this->tour->slug]), $o + [
            'departure_id' => $d->id, 'adults' => 2, 'children' => 0, 'single_rooms' => 0, 'pickup_point' => 'Plateau',
            'payment_mode' => 'deposit', 'payment_method' => 'wallet',
        ]);
    }

    private function balance(): int
    {
        return Wallet::forUser($this->client)->fresh()->balance_available_xof;
    }

    public function test_the_tour_page_shows_itinerary_inclusions_pickups_departures_and_conditions(): void
    {
        $this->assertSame('ouest-montagneux-'.$this->tour->id, $this->tour->slug);
        $this->departure(40);
        $this->actingAs($this->client)->get(route('travel-agency.tour', [$this->agency->slug, $this->tour->slug]))->assertOk()
            ->assertSee('Abidjan → Man')->assertSee('Hôtel à Man')->assertSee('Transport')->assertSee('Boissons')
            ->assertSee('Plateau')->assertSee('Prochains départs')->assertSee('Encore 6 voyageurs pour garantir le départ')
            ->assertSee('Acompte de 30 % à la réservation')->assertSee('90 % remboursé')
            ->assertSee('id="tp-form"', false);
        $this->get(route('travel-agency.tours', $this->agency->slug))->assertSee(route('travel-agency.tour', [$this->agency->slug, $this->tour->slug]), false);
    }

    public function test_quote_applies_child_single_supplement_group_price_and_extras(): void
    {
        $d = $this->departure(40);
        $json = $this->getJson(route('travel-agency.tour.quote', [$this->agency->slug, $this->tour->slug]).'?'.http_build_query([
            'departure_id' => $d->id, 'adults' => 3, 'children' => 1, 'single_rooms' => 1, 'extras' => [0, 1],
        ]))->assertOk()->json();
        // 4 voyageurs → palier 90 000 ; 3 × 90 000 + 60 000 + 20 000 + 4 × 30 000 + 10 000
        $this->assertSame(270000 + 60000 + 20000 + 120000 + 10000, $json['total']);
        $this->assertSame(144000, $json['deposit']); // 30 % arrondi à la centaine supérieure
    }

    public function test_deposit_then_balance_with_the_wallet(): void
    {
        $d = $this->departure(40);
        $this->book($d)->assertRedirect();
        $b = TourBooking::sole();
        $this->assertSame(TourBooking::STATUS_DEPOSIT_PAID, $b->status);
        $this->assertSame(60000, $b->deposit_xof);                 // 30 % de 200 000
        $this->assertSame(60000, $b->amount_paid_xof);
        $this->assertSame(2000000 - 60000, $this->balance());
        $this->assertSame($d->departure_date->copy()->subDays(15)->toDateString(), $b->balance_due_on->toDateString());
        $this->assertSame(54000, Wallet::forProvider($this->provider)->fresh()->balance_pending_xof); // 60 000 − 10 % commission

        $this->actingAs($this->client)->get(route('visitor.tour-bookings.index'))->assertOk()->assertSee('Solde de 140 000 FCFA');
        $this->actingAs($this->client)->post(route('visitor.tour-bookings.pay', $b), ['payment_method' => 'wallet'])->assertRedirect();
        $this->assertSame(TourBooking::STATUS_PAID, $b->fresh()->status);
        $this->assertSame(2000000 - 200000, $this->balance());
        $this->assertSame(180000, Wallet::forProvider($this->provider)->fresh()->balance_pending_xof);
        $this->get($b->confirmationUrl())->assertOk()->assertSee('Votre programme')->assertSee('Plateau');
    }

    public function test_late_booking_pays_in_full_and_seats_are_limited(): void
    {
        $d = $this->departure(10, seats: 3); // moins de 15 jours : pas d'acompte
        $this->book($d)->assertRedirect();
        $this->assertSame(TourBooking::STATUS_PAID, TourBooking::sole()->status);
        $this->assertSame(200000, TourBooking::sole()->amount_paid_xof);

        $this->book($d)->assertSessionHasErrors('tour', null, 'tour'); // 1 place restante pour 2
        $this->book($d, ['pickup_point' => 'Ailleurs', 'adults' => 1])->assertSessionHasErrors('tour', null, 'tour');
        $this->assertSame(1, TourBooking::count());
    }

    public function test_reaching_the_minimum_guarantees_the_departure(): void
    {
        $d = $this->departure(40, min: 4);
        $this->book($d);
        $this->assertFalse($d->fresh()->isGuaranteed());
        $this->book($d, [], User::factory()->create(['role' => 'visitor']))->assertRedirect();
        // Second client sans solde : paiement refusé, la place n'est pas comptée.
        $this->assertFalse($d->fresh()->isGuaranteed());

        $other = User::factory()->create(['role' => 'visitor']);
        Wallet::forUser($other)->update(['balance_available_xof' => 500000]);
        $this->book($d, [], $other);
        $this->assertNotNull($d->fresh()->guaranteed_at);
    }

    public function test_client_cancellation_follows_the_policy(): void
    {
        $d = $this->departure(20); // entre 14 et 30 jours : 50 %
        $this->book($d, ['payment_mode' => 'full']);
        $b = TourBooking::sole();
        $this->assertSame(50, $b->refundPercentNow());
        $this->actingAs($this->client)->post(route('visitor.tour-bookings.cancel', $b))->assertSessionHas('success');
        $b->refresh();
        $this->assertSame(TourBooking::STATUS_CANCELLED, $b->status);
        $this->assertSame('client', $b->cancelled_by);
        $this->assertSame(100000, $b->refund_xof);
        $this->assertSame(2000000 - 200000 + 100000, $this->balance());
        // L'agence garde la moitié de sa part nette (180 000 → 90 000).
        $this->assertSame(90000, Wallet::forProvider($this->provider)->fresh()->balance_pending_xof);
    }

    public function test_agency_cancels_a_departure_and_everyone_is_refunded_in_full(): void
    {
        $d = $this->departure(40);
        $this->book($d);
        $this->actingAs($this->owner)->post(route('provider.tours.departures.cancel', [$this->tour, $d]), ['reason' => 'Route coupée'])->assertSessionHas('success');
        $b = TourBooking::sole();
        $this->assertSame('agency', $b->cancelled_by);
        $this->assertSame(2000000, $this->balance());
        $this->assertSame(0, Wallet::forProvider($this->provider)->fresh()->balance_pending_xof);
        $this->assertNotNull($d->fresh()->cancelled_at);
        $this->book($d)->assertSessionHasErrors('departure_id', null, 'tour');
    }

    public function test_daily_processing_cancels_departures_below_minimum_and_unpaid_balances(): void
    {
        $low = $this->departure(5, min: 6);   // date de décision dépassée, 2 inscrits
        $this->tour->update(['deposit_percent' => null]);
        $this->book($low, ['payment_mode' => 'full']);

        $this->tour->update(['deposit_percent' => 30]);
        $later = $this->departure(40);
        $this->book($later);
        $unpaid = TourBooking::where('tour_departure_id', $later->id)->sole();
        $unpaid->update(['balance_due_on' => now()->subDays(5)]);

        $stats = app(TourBookingService::class)->processDaily();
        $this->assertSame(1, $stats['cancelled_departures']);
        $this->assertSame(1, $stats['cancelled_unpaid']);
        $this->assertSame('departure', TourBooking::where('tour_departure_id', $low->id)->sole()->cancelled_by);
        $this->assertSame('unpaid', $unpaid->fresh()->cancelled_by);
        // Départ annulé : 200 000 rendus ; impayé à 40 jours : 90 % de l'acompte (54 000).
        $this->assertSame(2000000 - 60000 + 54000, $this->balance());
    }

    public function test_provider_credits_are_released_after_departure(): void
    {
        $d = $this->departure(10);
        $this->book($d);
        $this->travelTo(now()->addDays(12));
        $this->assertSame(1, app(\App\Services\TourBookingPaymentService::class)->releaseDueCredits());
        $w = Wallet::forProvider($this->provider)->fresh();
        $this->assertSame(0, $w->balance_pending_xof);
        $this->assertSame(180000, $w->balance_available_xof);
    }

    public function test_travelers_list_is_saved_and_shown_to_the_agency(): void
    {
        $d = $this->departure(40);
        $this->book($d);
        $b = TourBooking::sole();
        $this->assertFalse($b->travelersComplete());
        $this->actingAs($this->client)->put(route('visitor.tour-bookings.travelers', $b), [
            'travelers' => [['name' => 'Aya Kouassi', 'id_number' => 'CI123'], ['name' => 'Koffi Yao', 'id_number' => 'CI456']],
            'emergency_contact' => 'Maman 0707070707',
        ])->assertSessionHas('success');
        $this->assertTrue($b->fresh()->travelersComplete());
        $this->actingAs($this->owner)->get(route('provider.tours.departures', $this->tour))->assertOk()
            ->assertSee('Koffi Yao')->assertSee('CI456')->assertSee('Liste des voyageurs');
        $this->actingAs(User::factory()->create(['role' => 'visitor']))->put(route('visitor.tour-bookings.travelers', $b), ['travelers' => [['name' => 'X']]])->assertNotFound();
    }

    public function test_provider_saves_market_fields_from_the_form(): void
    {
        $this->actingAs($this->owner)->put(route('provider.tours.update', $this->tour), [
            'name' => 'Ouest montagneux', 'category_id' => $this->tour->category_id, 'price_xof' => 100000, 'duration_days' => 2,
            'itinerary' => [['title' => 'Jour A', 'description' => 'x'], ['title' => '', 'description' => ''], ['title' => 'Jour B']],
            'pickup_points' => [['label' => 'Gare', 'time' => '06:30']],
            'group_prices' => [['min' => 6, 'price_xof' => 80000], ['min' => 3, 'price_xof' => 95000]],
            'cancellation_policy' => [['days' => 10, 'percent' => 100], ['days' => 0, 'percent' => 0]],
            'deposit_percent' => 25, 'balance_due_days' => 10, 'min_participants' => 5, 'instant_booking' => 1,
            'languages_text' => 'Français, English',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $t = $this->tour->fresh();
        $this->assertSame(['Jour A', 'Jour B'], collect($t->itinerary)->pluck('title')->all());
        $this->assertSame([3, 6], collect($t->groupPrices())->pluck('min')->all());
        $this->assertSame(25, $t->deposit_percent);
        $this->assertSame(['Français', 'English'], $t->languages);
        $this->assertSame('ouest-montagneux-'.$t->id, $t->slug, 'Le slug ne change pas.');

        $this->actingAs($this->owner)->get(route('provider.tours.edit', $this->tour))->assertOk()->assertSee('Programme jour par jour')->assertSee('Jour B');
        $this->actingAs($this->owner)->put(route('provider.tours.update', $this->tour), ['name' => 'X', 'category_id' => $t->category_id, 'price_xof' => 1, 'extras' => [['label' => 'Option sans prix']]])
            ->assertSessionHasErrors('extras');
    }

    public function test_request_mode_tour_cannot_be_booked_instantly(): void
    {
        $this->tour->update(['instant_booking' => false]);
        $d = $this->departure(40);
        $this->actingAs($this->client)->get(route('travel-agency.tour', [$this->agency->slug, $this->tour->slug]))->assertSee('Demander à l\'agence');
        $this->book($d)->assertNotFound();
    }
}
