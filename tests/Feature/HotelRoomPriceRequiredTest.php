<?php

namespace Tests\Feature;

use App\Models\Accommodation;
use App\Models\Provider;
use App\Models\ProviderCategory;
use App\Models\Reservation;
use App\Models\TouristCity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Une chambre sans tarif ne doit ni être réservable ni pouvoir être créée par un
 * prestataire : sans prix, la carte n'affiche ni tarif ni bouton « Réserver », et la
 * réservation ne doit jamais se rabattre sur un prix envoyé par le navigateur.
 */
class HotelRoomPriceRequiredTest extends TestCase
{
    use RefreshDatabase;

    private function makeAccommodation(): Accommodation
    {
        $city = TouristCity::create(['name' => 'Abidjan Test', 'slug' => 'abidjan-test-'.uniqid(), 'is_active' => true]);

        return Accommodation::create([
            'city_id' => $city->id,
            'name' => 'Hôtel Test Prix',
            'slug' => 'hotel-test-prix-'.uniqid(),
            'type' => 'hotel',
            'is_active' => true,
            'room_types' => [
                ['id' => 'r1', 'name' => 'Chambre avec prix', 'price_xof' => 50000, 'max_adults' => 2, 'max_children' => 0],
                ['id' => 'r2', 'name' => 'Chambre sans prix', 'price_xof' => null, 'max_adults' => 2, 'max_children' => 0],
            ],
        ]);
    }

    private function payload(Accommodation $a, string $room, int $clientPrice): array
    {
        return [
            'accommodation_id' => $a->id,
            'room_name' => $room,
            'room_price_xof' => $clientPrice,
            'check_in' => now()->addDays(10)->toDateString(),
            'check_out' => now()->addDays(12)->toDateString(),
            'rooms_count' => 1,
            'guests_count' => 2,
            'full_name' => 'Client Test',
            'email' => 'client-test@example.ci',
            'phone' => '0700000000',
        ];
    }

    public function test_reservation_of_a_room_without_price_is_refused_whatever_the_client_sends(): void
    {
        Mail::fake();
        Notification::fake();
        $a = $this->makeAccommodation();

        $this->actingAs(User::factory()->create())->postJson(route('reservations.store'), $this->payload($a, 'Chambre sans prix', 1))
            ->assertStatus(422);

        $this->actingAs(User::factory()->create())->postJson(route('reservations.payment.initiate'), $this->payload($a, 'Chambre sans prix', 1))
            ->assertStatus(422);

        $this->assertSame(0, Reservation::count());
    }

    public function test_reservation_uses_the_server_price_not_the_one_submitted(): void
    {
        Mail::fake();
        Notification::fake();
        $a = $this->makeAccommodation();

        $this->actingAs(User::factory()->create())->postJson(route('reservations.store'), $this->payload($a, 'Chambre avec prix', 1))
            ->assertStatus(200);

        $this->assertSame(1, Reservation::count());
        $this->assertSame(50000, (int) Reservation::first()->room_price_xof);
    }

    public function test_provider_cannot_create_a_room_without_price(): void
    {
        $user = User::factory()->provider()->create();
        $category = ProviderCategory::create(['slug' => 'hotels', 'name_fr' => 'Hôtels', 'name_en' => 'Hotels', 'is_active' => true]);
        $provider = Provider::create([
            'user_id' => $user->id, 'category_id' => $category->id,
            'name' => 'Hôtel Prestataire', 'slug' => 'hotel-prestataire', 'status' => 'active',
        ]);
        $city = TouristCity::create(['name' => 'Ville Test', 'slug' => 'ville-test', 'is_active' => true]);
        Accommodation::create([
            'provider_id' => $provider->id, 'city_id' => $city->id, 'name' => 'Hôtel Prestataire',
            'slug' => 'hotel-prestataire', 'type' => 'hotel', 'is_active' => true, 'room_types' => [],
        ]);

        $this->actingAs($user)
            ->post(route('provider.accommodation.rooms.store'), ['name' => 'Chambre sans tarif', 'max_adults' => 2])
            ->assertSessionHasErrors('price_xof');

        $this->assertSame([], Accommodation::where('slug', 'hotel-prestataire')->first()->room_types ?? []);
    }
}
