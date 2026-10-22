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
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/** Restaurant : le client réserve une table OU commande des plats (à emporter / en livraison). */
class RestaurantOrderTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Restaurant $restaurant;

    private MenuItem $alloco;

    private MenuItem $bissap;

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
            ProviderHour::create(['provider_id' => $provider->id, 'day_of_week' => $d, 'open_time' => '11:00', 'close_time' => '23:00', 'is_closed' => false]);
        }
        $city = TouristCity::create(['name' => 'Abidjan', 'slug' => 'abidjan-'.uniqid(), 'is_active' => true]);
        $this->restaurant = Restaurant::create(['city_id' => $city->id, 'provider_id' => $provider->id, 'name' => 'Chez Tantie', 'slug' => 'chez-tantie', 'is_active' => true, 'accepts_orders' => true]);
        $menuCat = MenuCategory::create(['slug' => 'plats-'.uniqid(), 'name_fr' => 'Plats', 'name_en' => 'Mains', 'sort_order' => 1, 'is_active' => true]);
        $this->alloco = MenuItem::create(['provider_id' => $provider->id, 'category_id' => $menuCat->id, 'name' => 'Alloco poisson', 'price_xof' => 2500, 'is_available' => true]);
        $this->bissap = MenuItem::create(['provider_id' => $provider->id, 'category_id' => $menuCat->id, 'name' => 'Jus de bissap', 'price_xof' => 1000, 'is_available' => true]);
        $this->date = now()->addDays(2)->toDateString();
    }

    private function order(array $lines, array $extra = [])
    {
        return $this->actingAs(User::factory()->create())->post(route('booking-requests.store'), $extra + [
            'bookable_type' => 'restaurant_order', 'bookable_id' => $this->restaurant->id, 'booking_date' => $this->date,
            'booking_time' => '12:30', 'order' => $lines, 'full_name' => 'Client', 'email' => 'client@example.ci',
        ]);
    }

    public function test_the_card_offers_a_table_or_an_order(): void
    {
        $this->actingAs(User::factory()->create())->get(route('restaurant.show', 'chez-tantie').'?item=Jus+de+bissap&commander=1')
            ->assertOk()->assertSee('Réserver une table')->assertSee('Commander')->assertSee('Voir mon panier')
            // Panier : plats proposés, plat demandé pré-ajouté (data-preset), formulaire de commande.
            ->assertSee('data-cart-step="'.$this->bissap->id.'"', false)
            ->assertSee('data-preset="Jus de bissap"', false)
            ->assertSee('value="restaurant_order"', false);

        // Page « Notre carte » : bouton « Ajouter » sur chaque plat, relié au même panier.
        $this->get(route('restaurant.menu', 'chez-tantie'))->assertOk()
            ->assertSee('data-cart-add="'.$this->alloco->id.'"', false)->assertSee('Votre panier');
    }

    public function test_a_takeaway_order_is_priced_from_the_menu(): void
    {
        $this->order([$this->alloco->id => 2, $this->bissap->id => 1])->assertSessionHas('booking_success')->assertSessionHas('order_placed', $this->restaurant->id);

        $b = BookingRequest::sole();
        $this->assertSame('restaurant_order', $b->bookable_type);
        $this->assertSame('pickup', $b->fulfillment);
        $this->assertSame(6000, $b->amount_due_xof);              // 2 × 2 500 + 1 × 1 000
        $this->assertSame(3, $b->party_size);                     // 3 plats
        $this->assertStringContainsString('2 × Alloco poisson', $b->item_name);
        $this->assertSame(BookingRequest::STATUS_NEW, $b->status);
        $this->assertCount(2, $b->order_items);
    }

    public function test_delivery_needs_an_address_and_a_restaurant_that_delivers(): void
    {
        $this->order([$this->alloco->id => 1], ['fulfillment' => 'delivery', 'delivery_address' => 'Cocody, rue des jardins'])
            ->assertSessionHasErrors('fulfillment', null, 'booking');

        $this->restaurant->update(['offers_delivery' => true]);
        $this->order([$this->alloco->id => 1], ['fulfillment' => 'delivery'])->assertSessionHasErrors('delivery_address', null, 'booking');
        $this->order([$this->alloco->id => 1], ['fulfillment' => 'delivery', 'delivery_address' => 'Cocody, rue des jardins'])->assertSessionHas('booking_success');

        $b = BookingRequest::sole();
        $this->assertSame('delivery', $b->fulfillment);
        $this->assertSame('Cocody, rue des jardins', $b->dropoff_location);
    }

    public function test_invalid_orders_are_refused(): void
    {
        // Aucun plat, plat indisponible ou d'un autre restaurant, heure de fermeture, commandes désactivées.
        $this->order([$this->alloco->id => 0])->assertSessionHasErrors('order', null, 'booking');
        $this->bissap->update(['is_available' => false]);
        $this->order([$this->bissap->id => 2])->assertSessionHasErrors('order', null, 'booking');
        $this->order([$this->alloco->id => 1], ['booking_time' => '23:30'])->assertSessionHasErrors('booking_time', null, 'booking');
        $this->restaurant->update(['accepts_orders' => false]);
        $this->order([$this->alloco->id => 1])->assertSessionHasErrors('order', null, 'booking');
        $this->assertSame(0, BookingRequest::count());
    }

    public function test_the_owner_can_switch_orders_and_delivery(): void
    {
        $this->actingAs($this->owner)->put(route('provider.restaurant.tables.update'), [
            'slot_minutes' => 30, 'accepts_orders' => '1', 'offers_delivery' => '1',
        ])->assertRedirect();
        $this->assertTrue($this->restaurant->fresh()->offers_delivery);

        $this->actingAs($this->owner)->put(route('provider.restaurant.tables.update'), ['slot_minutes' => 30, 'accepts_orders' => '0', 'offers_delivery' => '1'])->assertRedirect();
        $r = $this->restaurant->fresh();
        $this->assertFalse($r->accepts_orders);
        $this->assertFalse($r->offers_delivery, 'Pas de livraison sans commandes.');
        $this->actingAs(User::factory()->create())->get(route('restaurant.show', 'chez-tantie'))->assertOk()->assertDontSee('Voir mon panier');
    }
}
