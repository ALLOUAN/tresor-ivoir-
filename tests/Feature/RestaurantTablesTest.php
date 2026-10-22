<?php

namespace Tests\Feature;

use App\Mail\BookingRequestMail;
use App\Models\BookingRequest;
use App\Models\Provider;
use App\Models\ProviderCategory;
use App\Models\ProviderHour;
use App\Models\Restaurant;
use App\Models\TouristCity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/** Tables : couverts par créneau, confirmation instantanée, créneaux de repli, plat du jour, menu QR. */
class RestaurantTablesTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Restaurant $restaurant;

    private string $date;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Notification::fake();
        $this->owner = User::factory()->provider()->create();
        $cat = ProviderCategory::create(['slug' => 'restaurants', 'name_fr' => 'Restaurants', 'name_en' => 'Restaurants', 'is_active' => true]);
        $provider = Provider::create(['user_id' => $this->owner->id, 'category_id' => $cat->id, 'name' => 'Chez Tantie', 'slug' => 'chez-tantie', 'status' => 'active']);
        foreach (range(0, 6) as $d) {
            ProviderHour::create(['provider_id' => $provider->id, 'day_of_week' => $d, 'open_time' => '19:00', 'close_time' => '23:00', 'is_closed' => false]);
        }
        $city = TouristCity::create(['name' => 'Abidjan', 'slug' => 'abidjan-'.uniqid(), 'is_active' => true]);
        $this->restaurant = Restaurant::create(['city_id' => $city->id, 'provider_id' => $provider->id, 'name' => 'Chez Tantie', 'slug' => 'chez-tantie', 'is_active' => true,
            'table_capacity' => 10, 'slot_minutes' => 30, 'auto_confirm' => true, 'max_party_size' => 8]);
        $this->date = now()->addDays(3)->toDateString();
    }

    private function book(string $time, int $party)
    {
        return $this->actingAs(User::factory()->create())->post(route('booking-requests.store'), [
            'bookable_type' => 'restaurant', 'bookable_id' => $this->restaurant->id, 'booking_date' => $this->date,
            'booking_time' => $time, 'party_size' => $party, 'full_name' => 'Client', 'email' => 'client@example.ci',
        ]);
    }

    public function test_tables_are_confirmed_instantly_until_the_slot_is_full(): void
    {
        $this->book('20:00', 6)->assertSessionHas('booking_success');
        $this->assertSame(BookingRequest::STATUS_CONFIRMED, BookingRequest::firstOrFail()->status);
        Mail::assertQueued(BookingRequestMail::class, fn ($m) => $m->event === 'confirmed' && $m->hasTo('client@example.ci'));

        // Même créneau (20:00–20:29) : 6 + 4 = 10, encore possible ; la suivante dépasse.
        $this->book('20:15', 4)->assertSessionHas('booking_success');
        $this->book('20:00', 2)->assertSessionHasErrors('booking_time', null, 'booking');
        $error = session('errors')->getBag('booking')->first('booking_time');
        $this->assertStringContainsString('19:30', $error, 'Propose les créneaux libres les plus proches : '.$error);
        $this->assertSame(2, BookingRequest::count());
    }

    public function test_closed_hours_and_large_groups_are_refused(): void
    {
        $this->book('12:00', 2)->assertSessionHasErrors('booking_time', null, 'booking');
        $this->book('20:00', 9)->assertSessionHasErrors('party_size', null, 'booking');
    }

    public function test_slots_endpoint_lists_remaining_seats(): void
    {
        $this->book('19:30', 8);
        $slots = collect($this->getJson(route('restaurant.slots', [$this->restaurant->id, 'date' => $this->date, 'party' => 4]))->assertOk()->json('slots'))->keyBy('time');

        $this->assertSame('19:00', $slots->keys()->first());
        $this->assertSame('22:00', $slots->keys()->last(), 'Dernière table une heure avant la fermeture.');
        $this->assertSame(2, $slots['19:30']['remaining']);
        $this->assertFalse($slots['19:30']['available']);
        $this->assertTrue($slots['20:00']['available']);
    }

    public function test_without_capacity_the_request_flow_is_unchanged(): void
    {
        $this->restaurant->update(['table_capacity' => null, 'auto_confirm' => false]);
        $this->book('12:00', 20)->assertSessionHas('booking_success');
        $this->assertSame(BookingRequest::STATUS_NEW, BookingRequest::firstOrFail()->status);
    }

    public function test_owner_sets_tables_and_daily_special_and_prints_the_qr_menu(): void
    {
        $this->actingAs($this->owner)->put(route('provider.restaurant.tables.update'), [
            'table_capacity' => 30, 'slot_minutes' => 60, 'auto_confirm' => '1',
            'daily_special' => 'Kedjenou de poulet', 'daily_special_price_xof' => 4500,
        ])->assertRedirect();
        $r = $this->restaurant->fresh();
        $this->assertSame([30, 60, true], [(int) $r->table_capacity, (int) $r->slot_minutes, $r->auto_confirm]);
        $this->assertTrue($r->daily_special_date->isToday());

        $this->get(route('restaurant.show', 'chez-tantie'))->assertOk()->assertSee('Kedjenou de poulet')->assertSee('4 500');
        $this->travel(1)->days();
        $this->get(route('restaurant.show', 'chez-tantie'))->assertOk()->assertDontSee('Kedjenou de poulet');

        $this->actingAs($this->owner)->get(route('provider.restaurant.tables.edit'))->assertOk()->assertSee('Couverts par créneau');
        $this->actingAs($this->owner)->get(route('provider.restaurant.menu-qr'))->assertOk()
            ->assertSee('data:image/png;base64', false)->assertSee('Scannez pour voir le menu');
    }
}
