<?php

namespace Tests\Feature;

use App\Models\Accommodation;
use App\Models\BookingRequest;
use App\Models\Provider;
use App\Models\ProviderCategory;
use App\Models\ProviderMember;
use App\Models\Reservation;
use App\Models\TourCategory;
use App\Models\TourDeparture;
use App\Models\TouristCity;
use App\Models\TourPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Planning du jour : séjours, tables / activités et départs de circuits réunis, accessible à l'équipe. */
class ProviderAgendaTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_day_plan_gathers_everything_for_the_owner_and_the_team(): void
    {
        $owner = User::factory()->provider()->create();
        $cat = ProviderCategory::firstOrCreate(['slug' => 'hebergements'], ['name_fr' => 'Hébergements', 'name_en' => 'Stays', 'is_active' => true]);
        $provider = Provider::create(['user_id' => $owner->id, 'category_id' => $cat->id, 'name' => 'Lagune Lodge', 'slug' => 'lagune-lodge', 'status' => 'active']);
        $other = Provider::create(['user_id' => User::factory()->provider()->create()->id, 'category_id' => $cat->id, 'name' => 'Autre', 'slug' => 'autre', 'status' => 'active']);
        $city = TouristCity::create(['name' => 'Assinie', 'slug' => 'assinie-'.uniqid(), 'is_active' => true]);
        $hotel = Accommodation::create(['city_id' => $city->id, 'provider_id' => $provider->id, 'name' => 'Lagune Lodge', 'slug' => 'lagune-lodge', 'type' => 'hotel', 'is_active' => true, 'room_types' => []]);
        $day = now()->addDays(3)->toDateString();

        $stay = fn (string $name, string $in, string $out, int $providerId) => Reservation::create([
            'accommodation_id' => $hotel->id, 'provider_id' => $providerId, 'accommodation_name' => 'Lagune Lodge', 'room_name' => 'Suite',
            'check_in' => $in, 'check_out' => $out, 'nights' => 2, 'rooms_count' => 1, 'guests_count' => 2,
            'full_name' => $name, 'email' => 'x@example.ci', 'status' => Reservation::STATUS_CONFIRMED,
        ]);
        $stay('Arrivant Kouassi', $day, now()->addDays(5)->toDateString(), $provider->id);
        $stay('Partant Yao', now()->addDays(1)->toDateString(), $day, $provider->id);
        $stay('Client Ailleurs', $day, now()->addDays(5)->toDateString(), $other->id);

        BookingRequest::create(['provider_id' => $provider->id, 'bookable_type' => 'restaurant', 'bookable_id' => 1, 'bookable_name' => 'Le Ponton',
            'booking_date' => $day, 'booking_time' => '20:00', 'party_size' => 4, 'full_name' => 'Table Awa', 'email' => 'awa@example.ci', 'status' => BookingRequest::STATUS_NEW]);

        $tourCat = TourCategory::create(['name_fr' => 'Balnéaire', 'name_en' => 'Seaside', 'slug' => 'balneaire-'.uniqid()]);
        $tour = TourPackage::create(['category_id' => $tourCat->id, 'provider_id' => $provider->id, 'name' => 'Circuit Assinie', 'price_xof' => 45000, 'max_participants' => 10, 'is_available' => true]);
        TourDeparture::create(['tour_package_id' => $tour->id, 'departure_date' => $day, 'seats_total' => 10]);

        $staff = User::factory()->provider()->create();
        ProviderMember::create(['provider_id' => $provider->id, 'user_id' => $staff->id]);

        foreach ([$owner, $staff] as $user) {
            $this->actingAs($user)->get(route('provider.agenda', ['date' => $day]))->assertOk()
                ->assertSeeInOrder(['Circuit Assinie', 'Partant Yao', 'Arrivant Kouassi', 'Table Awa'])
                ->assertSee('À confirmer')
                ->assertDontSee('Client Ailleurs');
        }
        $this->actingAs($owner)->get(route('provider.agenda', ['date' => 'n-importe-quoi']))->assertOk();
    }
}
