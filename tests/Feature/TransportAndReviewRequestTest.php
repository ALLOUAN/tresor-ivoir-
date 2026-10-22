<?php

namespace Tests\Feature;

use App\Mail\ReviewRequestMail;
use App\Models\BookingRequest;
use App\Models\Provider;
use App\Models\ProviderCategory;
use App\Models\Reservation;
use App\Models\Review;
use App\Models\TouristCity;
use App\Models\TransportCompany;
use App\Models\TransportOffer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/** Transport réservable (transfert, trajet) et demande d'avis automatique après la prestation. */
class TransportAndReviewRequestTest extends TestCase
{
    use RefreshDatabase;

    private User $providerUser;

    private Provider $provider;

    private TransportCompany $company;

    private TransportOffer $offer;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Notification::fake();
        $this->providerUser = User::factory()->provider()->create();
        $category = ProviderCategory::create(['slug' => 'transports-'.uniqid(), 'name_fr' => 'Transports', 'name_en' => 'Transport', 'is_active' => true]);
        $this->provider = Provider::create(['user_id' => $this->providerUser->id, 'category_id' => $category->id, 'name' => 'Ivoire Transferts', 'slug' => 'ivoire-transferts', 'status' => 'active']);
        $city = TouristCity::create(['name' => 'Abidjan', 'slug' => 'abidjan-'.uniqid(), 'is_active' => true]);
        $this->company = TransportCompany::create(['city_id' => $city->id, 'provider_id' => $this->provider->id, 'name' => 'Ivoire Transferts', 'slug' => 'ivoire-transferts', 'is_active' => true]);
        $offerCat = \App\Models\TransportCategory::create(['slug' => 'transferts-'.uniqid(), 'name_fr' => 'Transferts', 'name_en' => 'Transfers', 'is_active' => true]);
        $this->offer = TransportOffer::create(['provider_id' => $this->provider->id, 'category_id' => $offerCat->id, 'name' => 'Transfert aéroport → Plateau', 'price_xof' => 15000, 'max_passengers' => 4, 'is_available' => true]);
    }

    private function bookRide(array $overrides = [])
    {
        return $this->actingAs(User::factory()->create())->post(route('booking-requests.store'), $overrides + [
            'bookable_type' => 'transport', 'bookable_id' => $this->company->id, 'transport_offer_id' => $this->offer->id,
            'pickup_location' => 'Aéroport FHB', 'dropoff_location' => 'Hôtel Pullman, Plateau',
            'booking_date' => now()->addDays(3)->toDateString(), 'booking_time' => '06:30', 'party_size' => 2,
            'full_name' => 'Awa', 'email' => 'awa@example.ci',
        ]);
    }

    public function test_an_airport_transfer_can_be_booked_with_pickup_dropoff_and_price(): void
    {
        $this->get(route('transport-company.show', $this->company->slug))->assertOk()->assertSee('Réserver un transport')->assertSee('Transfert aéroport');

        $this->bookRide()->assertSessionHas('booking_success');

        $b = BookingRequest::firstOrFail();
        $this->assertSame('transport', $b->bookable_type);
        $this->assertSame('Transfert aéroport → Plateau', $b->item_name);
        $this->assertSame('Aéroport FHB', $b->pickup_location);
        $this->assertSame(15000, $b->amount_due_xof, 'Prix de la course proposé, que l\'entreprise confirme.');
        $this->actingAs($this->providerUser)->get(route('provider.booking-requests.index'))->assertSee('Aéroport FHB → Hôtel Pullman, Plateau');
    }

    public function test_transport_requires_a_route_and_respects_vehicle_capacity(): void
    {
        $this->bookRide(['pickup_location' => ''])->assertSessionHasErrors('pickup_location', null, 'booking');
        $this->bookRide(['party_size' => 6])->assertSessionHasErrors('party_size', null, 'booking');
        $this->assertSame(0, BookingRequest::count());
    }

    public function test_review_requests_are_sent_once_after_the_service_and_never_to_existing_reviewers(): void
    {
        $client = User::factory()->create(['email' => 'client@example.ci']);
        $done = BookingRequest::create([
            'provider_id' => $this->provider->id, 'user_id' => $client->id, 'bookable_type' => 'transport', 'bookable_id' => $this->company->id,
            'bookable_name' => 'Ivoire Transferts', 'booking_date' => now()->subDays(2)->toDateString(), 'party_size' => 2,
            'full_name' => 'Client', 'email' => 'client@example.ci', 'status' => 'confirmed',
        ]);
        $future = BookingRequest::create([
            'provider_id' => $this->provider->id, 'user_id' => $client->id, 'bookable_type' => 'transport', 'bookable_id' => $this->company->id,
            'bookable_name' => 'Ivoire Transferts', 'booking_date' => now()->addDays(2)->toDateString(), 'party_size' => 2,
            'full_name' => 'Client', 'email' => 'futur@example.ci', 'status' => 'confirmed',
        ]);
        $stay = Reservation::create([
            'provider_id' => $this->provider->id, 'accommodation_name' => 'Hôtel', 'room_name' => 'Std',
            'check_in' => now()->subDays(4)->toDateString(), 'check_out' => now()->subDay()->toDateString(), 'nights' => 3,
            'rooms_count' => 1, 'guests_count' => 2, 'full_name' => 'Séjour', 'email' => 'sejour@example.ci',
            'status' => Reservation::STATUS_CONFIRMED, 'payment_status' => Reservation::PAYMENT_DEPOSIT_PAID,
        ]);

        $this->artisan('reviews:request')->assertSuccessful();
        $this->artisan('reviews:request')->assertSuccessful();

        Mail::assertQueued(ReviewRequestMail::class, 2);
        Mail::assertQueued(ReviewRequestMail::class, fn ($m) => $m->hasTo('client@example.ci') && str_contains($m->reviewUrl, '/annuaire/ivoire-transferts?avis=1'));
        Mail::assertQueued(ReviewRequestMail::class, fn ($m) => $m->hasTo('sejour@example.ci'));
        Mail::assertNotQueued(ReviewRequestMail::class, fn ($m) => $m->hasTo('futur@example.ci'));
        $this->assertNotNull($done->fresh()->review_requested_at);
        $this->assertNull($future->fresh()->review_requested_at);

        // Client ayant déjà laissé un avis : pas de relance.
        Review::create(['provider_id' => $this->provider->id, 'user_id' => $client->id, 'rating' => 5, 'comment' => 'Parfait, chauffeur ponctuel et aimable.', 'status' => 'approved']);
        BookingRequest::create([
            'provider_id' => $this->provider->id, 'user_id' => $client->id, 'bookable_type' => 'transport', 'bookable_id' => $this->company->id,
            'bookable_name' => 'Ivoire Transferts', 'booking_date' => now()->subDay()->toDateString(), 'party_size' => 1,
            'full_name' => 'Client', 'email' => 'client2@example.ci', 'status' => 'confirmed',
        ]);
        $this->artisan('reviews:request');
        Mail::assertNotQueued(ReviewRequestMail::class, fn ($m) => $m->hasTo('client2@example.ci'));
    }

    public function test_the_review_link_preselects_the_rating_and_marks_the_review_verified(): void
    {
        $client = User::factory()->create();
        BookingRequest::create([
            'provider_id' => $this->provider->id, 'user_id' => $client->id, 'bookable_type' => 'transport', 'bookable_id' => $this->company->id,
            'bookable_name' => 'Ivoire Transferts', 'booking_date' => now()->subDay()->toDateString(), 'party_size' => 1,
            'full_name' => 'Client', 'email' => $client->email, 'status' => 'confirmed',
        ]);

        $this->get(route('providers.show', 'ivoire-transferts').'?avis=1')->assertOk()->assertSee('id="laisser-un-avis"', false)->assertSee('Se connecter');
        $this->actingAs($client)->get(route('providers.show', 'ivoire-transferts').'?avis=1&note=4')->assertOk()->assertSee('<option value="4" selected', false);

        $this->actingAs($client)->post(route('reviews.store', $this->provider), ['rating' => 4, 'comment' => 'Chauffeur à l\'heure, voiture propre et climatisée.']);
        $this->assertTrue(Review::firstOrFail()->is_verified);
    }
}
