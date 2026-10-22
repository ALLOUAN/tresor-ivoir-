<?php

namespace Tests\Feature;

use App\Mail\BookingRequestMail;
use App\Models\BookingRequest;
use App\Models\Provider;
use App\Models\ProviderCategory;
use App\Models\Restaurant;
use App\Models\TourPackage;
use App\Models\TouristCity;
use App\Models\TravelAgency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/** Demandes de réservation restaurant / circuit : création, réponse de l'établissement, annulation, expiration. */
class BookingRequestFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $providerUser;

    private Provider $provider;

    private int $cityId;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Notification::fake();
        $this->cityId = TouristCity::create(['name' => 'Abidjan', 'slug' => 'abidjan-'.uniqid(), 'is_active' => true])->id;
        $this->providerUser = User::factory()->provider()->create();
        $category = ProviderCategory::create(['slug' => 'resto-'.uniqid(), 'name_fr' => 'Restaurants', 'name_en' => 'Restaurants', 'is_active' => true]);
        $this->provider = Provider::create([
            'user_id' => $this->providerUser->id, 'category_id' => $category->id,
            'name' => 'Chez Tantie', 'slug' => 'chez-tantie-'.uniqid(), 'status' => 'active', 'email' => 'resto@example.ci',
        ]);
    }

    private function restaurant(): Restaurant
    {
        return Restaurant::create(['city_id' => $this->cityId, 'provider_id' => $this->provider->id, 'name' => 'Chez Tantie', 'slug' => 'chez-tantie', 'is_active' => true]);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'booking_date' => now()->addDays(3)->toDateString(), 'booking_time' => '20:00', 'party_size' => 4,
            'full_name' => 'Awa Koné', 'email' => 'awa@example.ci', 'phone' => '0700000000',
        ], $overrides);
    }

    public function test_full_restaurant_flow_request_confirm_then_cancel(): void
    {
        $restaurant = $this->restaurant();
        $client = User::factory()->create();

        $this->get(route('restaurant.show', $restaurant->slug))->assertOk()->assertSee('Réserver une table');

        $this->actingAs($client)
            ->post(route('booking-requests.store'), $this->payload(['bookable_type' => 'restaurant', 'bookable_id' => $restaurant->id]))
            ->assertRedirect()->assertSessionHas('booking_success');

        $booking = BookingRequest::first();
        $this->assertSame(BookingRequest::STATUS_NEW, $booking->status);
        $this->assertSame($this->provider->id, $booking->provider_id);
        Mail::assertQueued(BookingRequestMail::class, fn ($m) => $m->hasTo('resto@example.ci') && $m->event === 'received');

        $this->actingAs($this->providerUser)->get(route('provider.booking-requests.index'))->assertOk()->assertSee('Awa Koné');
        $this->actingAs($this->providerUser)
            ->post(route('provider.booking-requests.respond', $booking), ['decision' => 'confirm', 'provider_note' => 'Table en terrasse'])
            ->assertRedirect();
        $this->assertSame(BookingRequest::STATUS_CONFIRMED, $booking->fresh()->status);
        Mail::assertQueued(BookingRequestMail::class, fn ($m) => $m->hasTo('awa@example.ci') && $m->event === 'confirmed');

        $this->actingAs($client)->get(route('visitor.booking-requests.index'))->assertOk()->assertSee('Table en terrasse');
        $this->actingAs($client)->post(route('visitor.booking-requests.cancel', $booking))->assertRedirect();
        $this->assertSame(BookingRequest::STATUS_CANCELLED, $booking->fresh()->status);

        $this->actingAs(User::factory()->admin()->create())->get(route('admin.booking-requests.index'))->assertOk()->assertSee($booking->reference);
    }

    public function test_another_provider_cannot_answer_and_past_dates_are_refused(): void
    {
        $restaurant = $this->restaurant();
        $client = User::factory()->create();

        $this->actingAs($client)
            ->post(route('booking-requests.store'), $this->payload(['bookable_type' => 'restaurant', 'bookable_id' => $restaurant->id, 'booking_date' => now()->subDay()->toDateString()]))
            ->assertSessionHasErrors('booking_date', null, 'booking');

        $booking = BookingRequest::create($this->payload([
            'provider_id' => $this->provider->id, 'user_id' => $client->id, 'bookable_type' => 'restaurant',
            'bookable_id' => $restaurant->id, 'bookable_name' => 'Chez Tantie', 'status' => 'new',
        ]));

        $other = User::factory()->provider()->create();
        Provider::create(['user_id' => $other->id, 'category_id' => $this->provider->category_id, 'name' => 'Autre', 'slug' => 'autre', 'status' => 'active']);
        $this->actingAs($other)->post(route('provider.booking-requests.respond', $booking), ['decision' => 'confirm'])->assertForbidden();
    }

    public function test_tour_booking_checks_the_package_and_its_capacity(): void
    {
        $agency = TravelAgency::create(['city_id' => $this->cityId, 'provider_id' => $this->provider->id, 'name' => 'Ivoire Tours', 'slug' => 'ivoire-tours', 'is_active' => true]);
        $tourCategory = \App\Models\TourCategory::create(['name_fr' => 'Balnéaire', 'name_en' => 'Seaside', 'slug' => 'balneaire-'.uniqid()]);
        $tour = TourPackage::create(['category_id' => $tourCategory->id, 'provider_id' => $this->provider->id, 'name' => 'Circuit Assinie', 'price_xof' => 45000, 'max_participants' => 6, 'is_available' => true]);
        $client = User::factory()->create();
        $base = ['bookable_type' => 'tour', 'bookable_id' => $agency->id, 'tour_package_id' => $tour->id];

        $this->actingAs($client)->post(route('booking-requests.store'), $this->payload($base + ['party_size' => 10]))->assertSessionHasErrors('party_size', null, 'booking');
        $this->actingAs($client)->post(route('booking-requests.store'), $this->payload($base))->assertSessionHas('booking_success');
        $this->assertSame('Circuit Assinie', BookingRequest::first()->item_name);
    }

    public function test_unanswered_requests_expire_once_the_date_has_passed(): void
    {
        $restaurant = $this->restaurant();
        $old = BookingRequest::create($this->payload([
            'provider_id' => $this->provider->id, 'bookable_type' => 'restaurant', 'bookable_id' => $restaurant->id,
            'bookable_name' => 'Chez Tantie', 'status' => 'new', 'booking_date' => now()->subDays(2)->toDateString(),
        ]));

        $this->artisan('reservations:expire-stale')->assertSuccessful();

        $this->assertSame(BookingRequest::STATUS_EXPIRED, $old->fresh()->status);
    }
}
