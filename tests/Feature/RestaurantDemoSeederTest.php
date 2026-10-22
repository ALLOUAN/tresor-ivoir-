<?php

namespace Tests\Feature;

use App\Models\BookingRequest;
use App\Models\Provider;
use App\Models\ProviderCategory;
use App\Models\Restaurant;
use App\Models\TouristCity;
use App\Models\User;
use App\Services\TableSlots;
use Database\Seeders\RestaurantDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Le seeder de démonstration habille un restaurant existant, sans doublon. */
class RestaurantDemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_data_is_built_on_an_existing_restaurant_and_is_idempotent(): void
    {
        $cat = ProviderCategory::firstOrCreate(['slug' => 'restaurants'], ['name_fr' => 'Restaurants', 'name_en' => 'Restaurants', 'is_active' => true]);
        $provider = Provider::create(['user_id' => User::factory()->provider()->create()->id, 'category_id' => $cat->id, 'name' => 'Maquis Test', 'slug' => 'maquis-test', 'status' => 'active']);
        $city = TouristCity::create(['name' => 'Abidjan', 'slug' => 'abidjan-'.uniqid(), 'is_active' => true]);
        $restaurant = Restaurant::create(['city_id' => $city->id, 'provider_id' => $provider->id, 'name' => 'Maquis Test', 'slug' => 'maquis-test', 'is_active' => true, 'accepts_orders' => false]);

        $this->seed(RestaurantDemoSeeder::class);
        $this->seed(RestaurantDemoSeeder::class);

        $restaurant->refresh();
        $this->assertTrue($restaurant->accepts_orders);
        $this->assertSame(12, $restaurant->tables()->count());
        $this->assertSame(10, BookingRequest::where('email', 'like', '%'.RestaurantDemoSeeder::DEMO_DOMAIN)->count());
        $this->assertSame(2, BookingRequest::where('bookable_type', BookingRequest::TYPE_RESTAURANT_ORDER)->count());

        $plan = collect(app(TableSlots::class)->tablePlan($restaurant, now('Africa/Abidjan')->addDay()->toDateString(), '20:00', 2))->pluck('status', 'label');
        $this->assertSame('booked', $plan['T1']);
        $this->assertSame('free', $plan['T8'], 'La réservation annulée ne bloque pas la table.');
    }
}
