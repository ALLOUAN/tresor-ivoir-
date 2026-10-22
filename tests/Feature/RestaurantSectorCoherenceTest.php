<?php

namespace Tests\Feature;

use App\Models\BookingRequest;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Provider;
use App\Models\ProviderCategory;
use App\Models\ProviderHour;
use App\Models\Restaurant;
use App\Models\TouristCity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** Cohérence du secteur « Restaurants & Gastronomie » (réservations, carte, administration). */
class RestaurantSectorCoherenceTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Provider $provider;

    private Restaurant $restaurant;

    private TouristCity $city;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Notification::fake();
        $this->owner = User::factory()->provider()->create();
        $cat = ProviderCategory::firstOrCreate(['slug' => 'restaurants'], ['name_fr' => 'Restaurants', 'name_en' => 'Restaurants', 'is_active' => true]);
        $this->provider = Provider::create(['user_id' => $this->owner->id, 'category_id' => $cat->id, 'name' => 'Chez Tantie', 'slug' => 'chez-tantie', 'status' => 'active']);
        foreach (range(0, 6) as $d) {
            ProviderHour::create(['provider_id' => $this->provider->id, 'day_of_week' => $d, 'open_time' => '19:00', 'close_time' => '23:00', 'is_closed' => false]);
        }
        $this->city = TouristCity::create(['name' => 'Abidjan', 'slug' => 'abidjan-'.uniqid(), 'is_active' => true]);
        $this->restaurant = Restaurant::create(['city_id' => $this->city->id, 'provider_id' => $this->provider->id, 'name' => 'Chez Tantie', 'slug' => 'chez-tantie', 'is_active' => true,
            'table_capacity' => 10, 'slot_minutes' => 30, 'auto_confirm' => true, 'max_party_size' => 8]);
    }

    private function book(string $date, string $time, int $party = 2)
    {
        return $this->actingAs(User::factory()->create())->post(route('booking-requests.store'), [
            'bookable_type' => 'restaurant', 'bookable_id' => $this->restaurant->id, 'booking_date' => $date,
            'booking_time' => $time, 'party_size' => $party, 'full_name' => 'Client', 'email' => 'client@example.ci',
        ]);
    }

    public function test_a_table_cannot_be_booked_in_the_past_or_after_the_last_seating(): void
    {
        $this->travelTo(now('Africa/Abidjan')->setTime(21, 0));
        $today = now('Africa/Abidjan')->toDateString();

        $this->book($today, '20:00')->assertSessionHasErrors('booking_time', null, 'booking');
        $this->assertStringContainsString('déjà passée', session('errors')->getBag('booking')->first('booking_time'));

        // Fermeture à 23:00 : dernière table à 22:00, comme les créneaux proposés.
        $later = now()->addDays(2)->toDateString();
        $this->book($later, '22:30')->assertSessionHasErrors('booking_time', null, 'booking');
        $this->book($later, '22:00')->assertSessionHas('booking_success');
        $this->assertSame(1, BookingRequest::count());
    }

    public function test_renaming_the_restaurant_keeps_its_address_and_qr_menu(): void
    {
        $this->actingAs($this->owner)->put(route('provider.restaurant.profile.update'), [
            'city_id' => $this->city->id, 'name' => 'Chez Tantie Rose', 'is_active' => 1,
        ])->assertRedirect();

        $this->assertSame('Chez Tantie Rose', $this->restaurant->fresh()->name);
        $this->assertSame('chez-tantie', $this->restaurant->fresh()->slug);
        $this->get(route('restaurant.menu', 'chez-tantie'))->assertOk();
    }

    public function test_menu_follows_the_category_order(): void
    {
        $desserts = MenuCategory::create(['slug' => 'desserts-'.uniqid(), 'name_fr' => 'Desserts maison', 'name_en' => 'Home desserts', 'sort_order' => 121, 'is_active' => true]);
        $entrees = MenuCategory::create(['slug' => 'entrees-'.uniqid(), 'name_fr' => 'Entrées du chef', 'name_en' => 'Chef starters', 'sort_order' => 120, 'is_active' => true]);
        MenuItem::create(['provider_id' => $this->provider->id, 'category_id' => $desserts->id, 'name' => 'Dégué', 'price_xof' => 500, 'is_available' => true]);
        MenuItem::create(['provider_id' => $this->provider->id, 'category_id' => $entrees->id, 'name' => 'Alloco', 'price_xof' => 1000, 'is_available' => true]);

        $this->get(route('restaurant.menu', 'chez-tantie'))->assertOk()->assertSeeInOrder(['Entrées du chef', 'Desserts maison']);
    }

    public function test_city_filter_keeps_every_city_that_has_restaurants(): void
    {
        $bouake = TouristCity::create(['name' => 'Bouaké', 'slug' => 'bouake-'.uniqid(), 'is_active' => true]);
        TouristCity::create(['name' => 'Ville sans restaurant', 'slug' => 'vide-'.uniqid(), 'is_active' => true]);
        Restaurant::create(['city_id' => $bouake->id, 'name' => 'Maquis de Bouaké', 'slug' => 'maquis-bouake', 'is_active' => true]);

        $this->get(route('restaurant.index', ['ville' => $this->city->id]))->assertOk()
            ->assertSee('Bouaké')->assertDontSee('Ville sans restaurant');
    }

    public function test_admin_menu_rules(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);
        $cat = MenuCategory::create(['slug' => 'plats-'.uniqid(), 'name_fr' => 'Plats', 'name_en' => 'Mains', 'sort_order' => 1, 'is_active' => true]);
        $hotelCat = ProviderCategory::firstOrCreate(['slug' => 'hotels'], ['name_fr' => 'Hôtels', 'name_en' => 'Hotels', 'is_active' => true]);
        $hotel = Provider::create(['user_id' => User::factory()->provider()->create()->id, 'category_id' => $hotelCat->id, 'name' => 'Hôtel', 'slug' => 'hotel-x', 'status' => 'active']);

        // Un plat ne peut pas être rattaché à un prestataire qui n'est pas un restaurant.
        $this->actingAs($admin)->post(route('admin.menu.store'), ['provider_id' => $hotel->id, 'name' => 'Plat', 'category_id' => $cat->id, 'price_xof' => 1000])
            ->assertSessionHasErrors('provider_id');

        // Un fichier qui n'est pas une image est refusé.
        $this->actingAs($admin)->post(route('admin.menu.store'), ['provider_id' => $this->provider->id, 'name' => 'Plat', 'category_id' => $cat->id, 'price_xof' => 1000,
            'images' => [UploadedFile::fake()->create('script.php', 1, 'text/plain')]])->assertSessionHasErrors('images.0');
        $this->assertSame(0, MenuItem::count());

        // Plat supprimé : absent de la liste, et sa catégorie ne provoque plus d'erreur serveur à la suppression.
        $item = MenuItem::create(['provider_id' => $this->provider->id, 'category_id' => $cat->id, 'name' => 'Plat supprimé', 'price_xof' => 1000, 'is_available' => true]);
        $item->delete();
        $this->actingAs($admin)->get(route('admin.menu.index'))->assertOk()->assertDontSee('Plat supprimé');
        $this->actingAs($admin)->delete(route('admin.menu.categories.destroy', $cat))->assertRedirect()->assertSessionHas('error');
        $this->assertTrue(MenuCategory::whereKey($cat->id)->exists());
    }

    public function test_admin_links_a_restaurant_to_its_provider(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $cat = ProviderCategory::where('slug', 'restaurants')->first();
        $other = Provider::create(['user_id' => User::factory()->provider()->create()->id, 'category_id' => $cat->id, 'name' => 'Maquis Nouveau', 'slug' => 'maquis-nouveau', 'status' => 'active']);

        $this->actingAs($admin)->get(route('admin.restaurants.create'))->assertOk()
            ->assertSee('Maquis Nouveau')->assertDontSee('>'.$this->provider->name.'</option>', false); // déjà rattaché (le nom, l'id pouvant égaler celui d'une ville)

        $this->actingAs($admin)->post(route('admin.restaurants.store'), ['city_id' => $this->city->id, 'name' => 'Maquis Nouveau', 'provider_id' => $other->id, 'is_active' => 1])
            ->assertRedirect();
        $this->assertSame($other->id, Restaurant::where('name', 'Maquis Nouveau')->value('provider_id'));

        // Un prestataire déjà rattaché à un autre restaurant est refusé.
        $this->actingAs($admin)->post(route('admin.restaurants.store'), ['city_id' => $this->city->id, 'name' => 'Doublon', 'provider_id' => $this->provider->id])
            ->assertSessionHasErrors('provider_id');
    }
}
