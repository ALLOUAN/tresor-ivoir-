<?php

namespace Tests\Feature;

use App\Models\Accommodation;
use App\Models\BookingRequest;
use App\Models\Bundle;
use App\Models\Provider;
use App\Models\ProviderCategory;
use App\Models\TouristCity;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/** Offres groupées : composition admin, page publique, demande au prix par personne, paiement. */
class BundleTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $providerUser;

    private Provider $provider;

    private TouristCity $city;

    private Accommodation $hotel;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Notification::fake();
        $this->admin = User::factory()->admin()->create(['is_active' => true]);
        $this->providerUser = User::factory()->provider()->create();
        $category = ProviderCategory::create(['slug' => 'agences-'.uniqid(), 'name_fr' => 'Agences', 'name_en' => 'Agencies', 'is_active' => true]);
        $this->provider = Provider::create(['user_id' => $this->providerUser->id, 'category_id' => $category->id, 'name' => 'Ivoire Tours', 'slug' => 'ivoire-tours', 'status' => 'active']);
        $this->city = TouristCity::create(['name' => 'Grand-Bassam', 'slug' => 'grand-bassam', 'is_active' => true]);
        $this->hotel = Accommodation::create(['city_id' => $this->city->id, 'name' => 'Villa Lagune', 'slug' => 'villa-lagune', 'type' => 'villa', 'is_active' => true, 'room_types' => []]);
    }

    private function createBundle(): Bundle
    {
        $this->actingAs($this->admin)->post(route('admin.bundles.store'), [
            'title' => 'Week-end à Grand-Bassam', 'summary' => 'Plage, patrimoine et maquis.',
            'provider_id' => $this->provider->id, 'city_id' => $this->city->id,
            'duration_days' => 2, 'price_xof' => 85000, 'min_people' => 2, 'max_people' => 6,
            'includes' => ['accommodation' => [$this->hotel->id]],
            'highlights' => "2 nuits en villa\nVisite guidée du quartier France",
            'is_active' => '1',
        ])->assertRedirect(route('admin.bundles.index'));

        return Bundle::firstOrFail();
    }

    public function test_admin_composes_a_bundle_shown_publicly_with_its_included_places(): void
    {
        $this->actingAs($this->admin)->get(route('admin.bundles.create'))->assertOk()->assertSee('Villa Lagune');
        $bundle = $this->createBundle();

        $this->assertSame('week-end-a-grand-bassam', $bundle->slug);
        $this->assertEquals([['type' => 'accommodation', 'id' => $this->hotel->id]], $bundle->includes);
        $this->assertSame(['2 nuits en villa', 'Visite guidée du quartier France'], $bundle->highlights);

        $this->get(route('bundles.index'))->assertOk()->assertSee('Week-end à Grand-Bassam')->assertSee('85 000');
        $this->get(route('bundles.show', $bundle))->assertOk()
            ->assertSee('Villa Lagune')
            ->assertSee('Visite guidée du quartier France')
            ->assertSee(route('accommodations.show', 'villa-lagune'), false);
        $this->get(route('home'))->assertOk()->assertSee(route('bundles.index'), false);
    }

    public function test_booking_a_bundle_prices_it_per_person_then_it_can_be_paid(): void
    {
        $bundle = $this->createBundle();
        $client = User::factory()->create();
        $payload = ['bookable_type' => 'bundle', 'bookable_id' => $bundle->id, 'booking_date' => now()->addDays(10)->toDateString(),
            'full_name' => 'Awa', 'email' => $client->email];

        $this->actingAs($client)->post(route('booking-requests.store'), $payload + ['party_size' => 1])->assertSessionHasErrors('party_size', null, 'booking');
        $this->actingAs($client)->post(route('booking-requests.store'), $payload + ['party_size' => 3])->assertSessionHas('booking_success');

        $booking = BookingRequest::firstOrFail();
        $this->assertSame($bundle->id, $booking->bundle_id);
        $this->assertSame(255000, $booking->amount_due_xof, '3 × 85 000');
        $this->assertSame($this->provider->id, $booking->provider_id);

        // Le porteur confirme le prix proposé, le client paie avec son solde.
        $this->actingAs($this->providerUser)->post(route('provider.booking-requests.respond', $booking), ['decision' => 'confirm', 'amount_xof' => 255000]);
        Wallet::forUser($client)->forceFill(['balance_available_xof' => 300000])->save();
        $this->actingAs($client)->post(route('visitor.booking-requests.pay', $booking), ['payment_method' => 'wallet'])->assertSessionHas('success');

        $this->assertSame(BookingRequest::PAYMENT_PAID, $booking->fresh()->payment_status);
        $this->assertSame(229500, (int) Wallet::forProvider($this->provider)->fresh()->balance_pending_xof, '255 000 − 10 % de commission');
    }

    public function test_hidden_bundles_are_not_reachable_and_english_titles_are_used(): void
    {
        $bundle = $this->createBundle();
        $bundle->update(['title_en' => 'Weekend in Grand-Bassam']);

        $this->get(route('lang.switch', 'en'));
        $this->get(route('bundles.show', $bundle))->assertOk()->assertSee('Weekend in Grand-Bassam')->assertSee('Book this deal');

        $this->actingAs($this->admin)->patch(route('admin.bundles.toggle', $bundle));
        $this->get(route('bundles.show', $bundle))->assertNotFound();
    }
}
