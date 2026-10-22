<?php

namespace Tests\Feature;

use App\Models\Accommodation;
use App\Models\BookingRequest;
use App\Models\Provider;
use App\Models\ProviderCategory;
use App\Models\ProviderPromoCode;
use App\Models\Reservation;
use App\Models\Restaurant;
use App\Models\TouristCity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/** Codes promo des établissements : création, séjours (devis + réservation), demandes de réservation. */
class ProviderPromoCodeTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Provider $provider;

    private Accommodation $hotel;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Notification::fake();
        $this->owner = User::factory()->provider()->create();
        $cat = ProviderCategory::firstOrCreate(['slug' => 'hebergements'], ['name_fr' => 'Hébergements', 'name_en' => 'Stays', 'is_active' => true]);
        $this->provider = Provider::create(['user_id' => $this->owner->id, 'category_id' => $cat->id, 'name' => 'Lagune Lodge', 'slug' => 'lagune-lodge', 'status' => 'active']);
        $city = TouristCity::create(['name' => 'Assinie', 'slug' => 'assinie-'.uniqid(), 'is_active' => true]);
        $this->hotel = Accommodation::create(['city_id' => $city->id, 'provider_id' => $this->provider->id, 'name' => 'Lagune Lodge', 'slug' => 'lagune-lodge', 'type' => 'hotel', 'is_active' => true,
            'room_types' => [['id' => 'dbl', 'name' => 'Double', 'price_xof' => 40000, 'max_adults' => 2]]]);
        Restaurant::create(['city_id' => $city->id, 'provider_id' => $this->provider->id, 'name' => 'Le Ponton', 'slug' => 'le-ponton', 'is_active' => true]);
    }

    private function stay(array $o = []): array
    {
        // Deux nuits en semaine, loin de toute remise automatique.
        $in = now()->addDays(40)->startOfWeek();

        return $o + ['accommodation_id' => $this->hotel->id, 'room_id' => 'dbl', 'room_name' => 'Double',
            'check_in' => $in->toDateString(), 'check_out' => $in->addDays(2)->toDateString(), 'rooms_count' => 1, 'guests_count' => 2,
            'full_name' => 'Awa', 'email' => 'awa@example.ci'];
    }

    public function test_owner_creates_a_code_and_it_discounts_the_stay_once(): void
    {
        $this->actingAs($this->owner)->post(route('provider.promo-codes.store'), ['code' => 'paques 26', 'kind' => 'percent', 'value' => 20, 'max_uses' => 1])->assertSessionHas('success');
        $code = ProviderPromoCode::firstOrFail();
        $this->assertSame('PAQUES26', $code->code);
        $this->actingAs($this->owner)->post(route('provider.promo-codes.store'), ['code' => 'PAQUES26', 'kind' => 'amount', 'value' => 5000])->assertSessionHasErrors('code');

        $this->get(route('accommodations.show', 'lagune-lodge'))->assertSee('Code promo (facultatif)');
        $q = $this->stay(['promo_code' => 'paques26']);
        $this->getJson(route('reservations.quote', $q))->assertOk()->assertJson(['total' => 64000, 'promo_discount_xof' => 16000, 'promo_error' => null]);
        $this->getJson(route('reservations.quote', ['promo_code' => 'NOPE'] + $q))->assertJson(['total' => 80000, 'promo_error' => 'Code promo inconnu pour cet établissement.']);

        $client = User::factory()->create();
        $this->actingAs($client)->postJson(route('reservations.store'), $q)->assertOk();
        $r = Reservation::firstOrFail();
        $this->assertSame([64000, 'PAQUES26', 16000], [(int) $r->total_xof, $r->promo_code, (int) $r->promo_discount_xof]);
        $this->assertSame(1, $code->fresh()->uses_count);

        // Nombre d'utilisations atteint : refusé, rien n'est créé.
        $this->actingAs($client)->postJson(route('reservations.store'), $this->stay(['promo_code' => 'PAQUES26', 'check_in' => now()->addDays(60)->toDateString(), 'check_out' => now()->addDays(61)->toDateString()]))->assertStatus(422);
        $this->assertSame(1, Reservation::count());
    }

    public function test_booking_request_discount_is_deducted_when_the_provider_prices_it(): void
    {
        ProviderPromoCode::create(['provider_id' => $this->provider->id, 'code' => 'BIENVENUE', 'amount_off_xof' => 5000]);
        ProviderPromoCode::create(['provider_id' => $this->provider->id, 'code' => 'FINI', 'percent_off' => 10, 'ends_on' => now()->subDay()]);
        $restaurant = Restaurant::where('slug', 'le-ponton')->firstOrFail();
        $base = ['bookable_type' => 'restaurant', 'bookable_id' => $restaurant->id, 'booking_date' => now()->addDays(3)->toDateString(), 'booking_time' => '20:00',
            'party_size' => 4, 'full_name' => 'Awa', 'email' => 'awa@example.ci'];
        $client = User::factory()->create();

        $this->actingAs($client)->post(route('booking-requests.store'), $base + ['promo_code' => 'FINI'])->assertSessionHasErrors('promo_code', null, 'booking');
        $this->actingAs($client)->post(route('booking-requests.store'), $base + ['promo_code' => 'bienvenue'])->assertSessionHas('booking_success');
        $b = BookingRequest::firstOrFail();
        $this->assertSame('BIENVENUE', $b->promo_code);

        $this->actingAs($this->owner)->get(route('provider.booking-requests.index'))->assertSee('Code promo BIENVENUE');
        $this->actingAs($this->owner)->post(route('provider.booking-requests.respond', $b), ['decision' => 'confirm', 'amount_xof' => 30000])->assertRedirect();
        $this->assertSame([25000, 5000], [(int) $b->fresh()->amount_due_xof, (int) $b->fresh()->promo_discount_xof]);
    }
}
