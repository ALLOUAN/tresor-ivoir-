<?php

namespace Tests\Feature;

use App\Models\BookingRequest;
use App\Models\Provider;
use App\Models\ProviderCategory;
use App\Models\ProviderHour;
use App\Models\Restaurant;
use App\Models\RestaurantTable;
use App\Models\TouristCity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/** Plan de salle : le client choisit sa table ; une table réservée est grisée sur le créneau. */
class RestaurantFloorPlanTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Restaurant $restaurant;

    private RestaurantTable $t1;

    private RestaurantTable $t2;

    private string $date;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Notification::fake();
        $this->owner = User::factory()->provider()->create();
        $cat = ProviderCategory::firstOrCreate(['slug' => 'restaurants'], ['name_fr' => 'Restaurants', 'name_en' => 'Restaurants', 'is_active' => true]);
        $provider = Provider::create(['user_id' => $this->owner->id, 'category_id' => $cat->id, 'name' => 'Chez Tantie', 'slug' => 'chez-tantie', 'status' => 'active']);
        foreach (range(0, 6) as $d) {
            ProviderHour::create(['provider_id' => $provider->id, 'day_of_week' => $d, 'open_time' => '19:00', 'close_time' => '23:00', 'is_closed' => false]);
        }
        $city = TouristCity::create(['name' => 'Abidjan', 'slug' => 'abidjan-'.uniqid(), 'is_active' => true]);
        $this->restaurant = Restaurant::create(['city_id' => $city->id, 'provider_id' => $provider->id, 'name' => 'Chez Tantie', 'slug' => 'chez-tantie', 'is_active' => true, 'table_duration_minutes' => 90]);
        $this->t1 = $this->restaurant->tables()->create(['label' => 'T1', 'seats' => 4, 'zone' => 'Terrasse', 'shape' => 'round']);
        $this->t2 = $this->restaurant->tables()->create(['label' => 'T2', 'seats' => 2, 'zone' => 'Salle', 'shape' => 'square']);
        $this->date = now()->addDays(3)->toDateString();
    }

    private function book(string $time, int $party, ?int $tableId)
    {
        return $this->actingAs(User::factory()->create())->post(route('booking-requests.store'), [
            'bookable_type' => 'restaurant', 'bookable_id' => $this->restaurant->id, 'booking_date' => $this->date,
            'booking_time' => $time, 'party_size' => $party, 'full_name' => 'Client', 'email' => 'client@example.ci',
            'restaurant_table_id' => $tableId,
        ]);
    }

    private function plan(string $time, int $party = 2): \Illuminate\Support\Collection
    {
        return collect($this->getJson(route('restaurant.table-plan', [$this->restaurant->id, 'date' => $this->date, 'time' => $time, 'party' => $party]))
            ->assertOk()->json('tables'))->keyBy('label');
    }

    public function test_a_booked_table_is_greyed_for_its_duration(): void
    {
        $this->assertSame('free', $this->plan('20:00')['T1']['status']);

        $this->book('20:00', 3, $this->t1->id)->assertSessionHas('booking_success');
        $b = BookingRequest::sole();
        $this->assertSame($this->t1->id, $b->restaurant_table_id);
        $this->assertStringContainsString('Table T1', $b->item_name);

        // Occupée 1 h 30 : grisée à 19:00, 20:00 et 21:00 ; libre de nouveau à 21:30.
        $this->assertSame('booked', $this->plan('19:00')['T1']['status']);
        $this->assertSame('booked', $this->plan('21:00')['T1']['status']);
        $this->assertSame('free', $this->plan('21:30')['T1']['status']);
        $this->assertSame('free', $this->plan('20:00')['T2']['status']);

        // Personne d'autre ne peut la réserver sur ce créneau.
        $this->book('20:30', 2, $this->t1->id)->assertSessionHasErrors('restaurant_table_id', null, 'booking');
        $this->assertSame(1, BookingRequest::count());
    }

    public function test_a_cancelled_booking_frees_the_table(): void
    {
        $this->book('20:00', 2, $this->t1->id);
        BookingRequest::sole()->update(['status' => BookingRequest::STATUS_CANCELLED]);
        $this->assertSame('free', $this->plan('20:00')['T1']['status']);
    }

    public function test_table_too_small_or_foreign_is_refused(): void
    {
        $this->assertSame('small', $this->plan('20:00', 4)['T2']['status']);
        $this->book('20:00', 4, $this->t2->id)->assertSessionHasErrors('restaurant_table_id', null, 'booking');

        $other = Restaurant::create(['city_id' => $this->restaurant->city_id, 'name' => 'Autre', 'slug' => 'autre', 'is_active' => true]);
        $foreign = $other->tables()->create(['label' => 'X1', 'seats' => 8, 'zone' => 'Salle']);
        $this->book('20:00', 2, $foreign->id)->assertSessionHasErrors('restaurant_table_id', null, 'booking');

        // Sans table choisie : la demande classique reste possible.
        $this->book('20:00', 2, null)->assertSessionHas('booking_success');
        $this->assertNull(BookingRequest::sole()->restaurant_table_id);
    }

    public function test_owner_manages_the_floor_plan(): void
    {
        $this->actingAs($this->owner)->post(route('provider.restaurant.floor.store'), ['label' => 'V', 'seats' => 6, 'zone' => 'VIP', 'shape' => 'long', 'count' => 3])->assertRedirect();
        $this->assertEqualsCanonicalizing(['T1', 'T2', 'V1', 'V2', 'V3'], $this->restaurant->tables()->pluck('label')->all());

        $this->actingAs($this->owner)->post(route('provider.restaurant.floor.store'), ['label' => 'T1', 'seats' => 2, 'zone' => 'Salle', 'shape' => 'round'])
            ->assertSessionHasErrors('label');

        $this->actingAs($this->owner)->patch(route('provider.restaurant.floor.toggle', $this->t2))->assertRedirect();
        $this->assertArrayNotHasKey('T2', $this->plan('20:00')->all(), 'Une table hors service disparaît du plan.');

        $this->actingAs($this->owner)->get(route('provider.restaurant.tables.edit'))->assertOk()->assertSee('Plan de salle')->assertSee('V3');

        // Un autre restaurateur ne peut pas toucher à ces tables.
        $intruder = User::factory()->provider()->create();
        Provider::create(['user_id' => $intruder->id, 'category_id' => ProviderCategory::where('slug', 'restaurants')->value('id'), 'name' => 'X', 'slug' => 'x', 'status' => 'active']);
        Restaurant::create(['city_id' => $this->restaurant->city_id, 'provider_id' => Provider::where('slug', 'x')->value('id'), 'name' => 'X', 'slug' => 'x-resto', 'is_active' => true]);
        $this->actingAs($intruder)->delete(route('provider.restaurant.floor.destroy', $this->t1))->assertForbidden();
    }

    public function test_the_booking_card_shows_the_floor_plan(): void
    {
        $this->actingAs(User::factory()->create())->get(route('restaurant.show', 'chez-tantie'))->assertOk()
            ->assertSee('Choisissez votre table')->assertSee(route('restaurant.table-plan', $this->restaurant->id), false);
    }
}
