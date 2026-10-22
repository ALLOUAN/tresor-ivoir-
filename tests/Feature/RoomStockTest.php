<?php

namespace Tests\Feature;

use App\Models\Accommodation;
use App\Models\Reservation;
use App\Models\TouristCity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Stock par type de chambre : un type déclarant N unités accepte N réservations
 * simultanées, refuse la suivante, et le calendrier ne grise que les nuits complètes.
 */
class RoomStockTest extends TestCase
{
    use RefreshDatabase;

    private function makeAccommodation(?int $units): Accommodation
    {
        $city = TouristCity::create(['name' => 'Abidjan Stock', 'slug' => 'abidjan-stock-'.uniqid(), 'is_active' => true]);

        return Accommodation::create([
            'city_id' => $city->id,
            'name' => 'Hôtel Stock',
            'slug' => 'hotel-stock-'.uniqid(),
            'type' => 'hotel',
            'is_active' => true,
            'room_types' => [
                ['id' => 'std', 'name' => 'Standard', 'units' => $units, 'price_xof' => 30000, 'max_adults' => 2, 'max_children' => 0],
            ],
        ]);
    }

    private function book(Accommodation $a, int $fromDays, int $toDays, int $rooms = 1)
    {
        Mail::fake();
        Notification::fake();

        return $this->actingAs(User::factory()->create())->postJson(route('reservations.store'), [
            'accommodation_id' => $a->id, 'room_id' => 'std', 'room_name' => 'Standard',
            'check_in' => now()->addDays($fromDays)->toDateString(),
            'check_out' => now()->addDays($toDays)->toDateString(),
            'rooms_count' => $rooms, 'guests_count' => 2,
            'full_name' => 'Client Stock', 'email' => 'stock@example.ci',
        ]);
    }

    public function test_two_units_accept_two_overlapping_bookings_and_refuse_a_third(): void
    {
        $a = $this->makeAccommodation(2);

        $this->book($a, 10, 13)->assertOk();
        $this->book($a, 11, 14)->assertOk();
        $this->book($a, 12, 13)->assertStatus(422);
        // Le jour 13 n'est plus occupé que par la 2e réservation : une unité reste libre.
        $this->book($a, 13, 15)->assertOk();

        $this->assertSame(3, Reservation::count());
    }

    public function test_requested_rooms_count_is_checked_against_remaining_units(): void
    {
        $a = $this->makeAccommodation(3);

        $this->book($a, 10, 12, 2)->assertOk();
        $this->book($a, 10, 12, 2)->assertStatus(422);
        $this->book($a, 10, 12, 1)->assertOk();
    }

    public function test_calendar_only_greys_out_fully_booked_nights(): void
    {
        $a = $this->makeAccommodation(2);
        $this->book($a, 10, 13)->assertOk();
        $this->book($a, 11, 14)->assertOk();

        $ranges = $this->getJson(route('reservations.availability', ['accommodation_id' => $a->id, 'room_id' => 'std']))
            ->assertOk()->json('blocked_ranges');

        $this->assertSame([[
            'check_in' => now()->addDays(11)->toDateString(),
            'check_out' => now()->addDays(13)->toDateString(),
        ]], $ranges);
    }

    public function test_closed_dates_block_booking_and_appear_in_the_calendar(): void
    {
        $a = $this->makeAccommodation(5);
        $rooms = $a->room_types;
        $rooms[0]['closed_ranges'] = [['from' => now()->addDays(20)->toDateString(), 'to' => now()->addDays(21)->toDateString()]];
        $a->update(['room_types' => $rooms]);

        $this->book($a, 18, 21)->assertStatus(422);
        $this->book($a, 22, 24)->assertOk();
        // Le départ le jour même de la fermeture reste possible.
        $this->book($a, 18, 20)->assertOk();

        $ranges = $this->getJson(route('reservations.availability', ['accommodation_id' => $a->id, 'room_id' => 'std']))->json('blocked_ranges');
        $this->assertContains(['check_in' => now()->addDays(20)->toDateString(), 'check_out' => now()->addDays(22)->toDateString()], $ranges);
    }

    public function test_provider_saves_units_and_closed_dates_from_the_room_form(): void
    {
        $user = User::factory()->provider()->create();
        $category = \App\Models\ProviderCategory::create(['slug' => 'hotels', 'name_fr' => 'Hôtels', 'name_en' => 'Hotels', 'is_active' => true]);
        $provider = \App\Models\Provider::create([
            'user_id' => $user->id, 'category_id' => $category->id,
            'name' => 'Hôtel Stock Pro', 'slug' => 'hotel-stock-pro', 'status' => 'active',
        ]);
        $city = TouristCity::create(['name' => 'Ville Stock', 'slug' => 'ville-stock', 'is_active' => true]);
        $a = Accommodation::create([
            'provider_id' => $provider->id, 'city_id' => $city->id, 'name' => 'Hôtel Stock Pro',
            'slug' => 'hotel-stock-pro', 'type' => 'hotel', 'is_active' => true, 'room_types' => [],
        ]);

        $from = now()->addDays(5)->toDateString();
        $to = now()->addDays(8)->toDateString();
        $this->actingAs($user)->post(route('provider.accommodation.rooms.store'), [
            'name' => 'Double', 'price_xof' => 25000, 'max_adults' => 2, 'units' => 12,
            'closed_from' => [$from, now()->subDays(9)->toDateString(), ''],
            'closed_to' => [$to, now()->subDays(8)->toDateString(), ''],
        ])->assertRedirect();

        $room = $a->fresh()->room_types[0];
        $this->actingAs($user)->get(route('provider.accommodation.rooms.edit', $room['id']))->assertOk()->assertSee('Dates fermées')->assertSee($from);
        $this->assertSame(12, $room['units']);
        $this->assertEquals([['from' => $from, 'to' => $to]], $room['closed_ranges'], 'Les périodes passées ou vides sont ignorées.');
    }

    public function test_a_room_without_declared_stock_keeps_the_single_unit_behaviour(): void
    {
        $a = $this->makeAccommodation(null);

        $this->book($a, 10, 13)->assertOk();
        $this->book($a, 12, 14)->assertStatus(422);
    }
}
