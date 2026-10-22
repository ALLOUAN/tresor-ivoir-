<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\ActivityCategory;
use App\Models\BookingRequest;
use App\Models\LeisureVenue;
use App\Models\Provider;
use App\Models\ProviderCategory;
use App\Models\TourCategory;
use App\Models\TourDeparture;
use App\Models\TourPackage;
use App\Models\TouristCity;
use App\Models\TravelAgency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/** Agences : départs programmés (places restantes), devis sur mesure ; loisirs : choix de l'activité. */
class TourDeparturesAndQuotesTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Provider $provider;

    private TravelAgency $agency;

    private TourPackage $tour;

    private TouristCity $city;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Notification::fake();
        $this->owner = User::factory()->provider()->create();
        $cat = ProviderCategory::create(['slug' => 'agences-voyages', 'name_fr' => 'Agences', 'name_en' => 'Agencies', 'is_active' => true]);
        $this->provider = Provider::create(['user_id' => $this->owner->id, 'category_id' => $cat->id, 'name' => 'Ivoire Tours', 'slug' => 'ivoire-tours', 'status' => 'active']);
        $this->city = TouristCity::create(['name' => 'Abidjan', 'slug' => 'abidjan-'.uniqid(), 'is_active' => true]);
        $this->agency = TravelAgency::create(['city_id' => $this->city->id, 'provider_id' => $this->provider->id, 'name' => 'Ivoire Tours', 'slug' => 'ivoire-tours', 'is_active' => true]);
        $tc = TourCategory::create(['slug' => 'balneaire-'.uniqid(), 'name_fr' => 'Balnéaire', 'name_en' => 'Seaside', 'is_active' => true]);
        $this->tour = TourPackage::create(['provider_id' => $this->provider->id, 'category_id' => $tc->id, 'name' => 'Week-end Assinie', 'price_xof' => 50000, 'max_participants' => 20, 'is_available' => true]);
    }

    private function book(array $o)
    {
        return $this->actingAs(User::factory()->create())->post(route('booking-requests.store'), $o + [
            'bookable_type' => 'tour', 'bookable_id' => $this->agency->id, 'tour_package_id' => $this->tour->id,
            'party_size' => 2, 'full_name' => 'Awa', 'email' => 'awa@example.ci',
        ]);
    }

    public function test_owner_schedules_weekly_departures_and_seats_are_enforced(): void
    {
        $first = now()->addDays(7)->toDateString();
        $this->actingAs($this->owner)->post(route('provider.tours.departures.store', $this->tour), [
            'departure_date' => $first, 'seats_total' => 5, 'price_xof' => 45000, 'repeat_weeks' => 3,
        ])->assertSessionHas('success');
        $this->assertSame(4, TourDeparture::count());
        $this->actingAs($this->owner)->get(route('provider.tours.departures', $this->tour))->assertOk()->assertSee('0 / 5');

        // Date hors départs : refusée. Départ : prix du départ, places décomptées.
        $this->book(['booking_date' => now()->addDays(8)->toDateString()])->assertSessionHasErrors('booking_date', null, 'booking');
        $this->book(['booking_date' => $first, 'party_size' => 4])->assertSessionHas('booking_success');
        $b = BookingRequest::firstOrFail();
        $this->assertSame($this->tour->id, $b->item_id);
        $this->assertSame(180000, $b->amount_due_xof, '4 × 45 000');
        $this->book(['booking_date' => $first, 'party_size' => 2])->assertSessionHasErrors('party_size', null, 'booking');
        $this->assertSame(1, TourDeparture::whereDate('departure_date', $first)->first()->seatsLeft());

        $this->get(route('travel-agency.show', 'ivoire-tours'))->assertOk()->assertSee('data-departures', false);
    }

    public function test_a_departure_with_travellers_is_closed_not_deleted(): void
    {
        $d = TourDeparture::create(['tour_package_id' => $this->tour->id, 'departure_date' => now()->addDays(5)->toDateString(), 'seats_total' => 10]);
        $this->book(['booking_date' => $d->departure_date->toDateString()]);

        $this->actingAs($this->owner)->delete(route('provider.tours.departures.destroy', [$this->tour, $d]))->assertSessionHas('success');
        $this->assertFalse($d->fresh()->is_active);
    }

    public function test_custom_quote_needs_a_description_then_the_agency_prices_it(): void
    {
        $base = ['custom_quote' => 1, 'tour_package_id' => null, 'booking_date' => now()->addDays(30)->toDateString(), 'party_size' => 4];
        $this->book($base)->assertSessionHasErrors('message', null, 'booking');
        $this->book($base + ['message' => 'Une semaine en famille : Assinie, Grand-Bassam et le parc de la Comoé, budget moyen.'])->assertSessionHas('booking_success');

        $b = BookingRequest::firstOrFail();
        $this->assertTrue($b->is_custom_quote);
        $this->assertNull($b->amount_due_xof);

        $this->actingAs($this->owner)->post(route('provider.booking-requests.respond', $b), ['decision' => 'confirm', 'amount_xof' => 650000, 'provider_note' => 'Programme envoyé par e-mail.']);
        $this->assertSame(650000, $b->fresh()->amount_due_xof);
        $this->assertTrue($b->fresh()->canBePaidOnline());
    }

    public function test_leisure_booking_selects_an_activity_with_its_price_and_capacity(): void
    {
        $venue = LeisureVenue::create(['city_id' => $this->city->id, 'provider_id' => $this->provider->id, 'name' => 'Spa Riviera', 'slug' => 'spa-riviera', 'is_active' => true]);
        $ac = ActivityCategory::create(['slug' => 'detente-'.uniqid(), 'name_fr' => 'Détente', 'name_en' => 'Relax', 'is_active' => true]);
        $massage = Activity::create(['provider_id' => $this->provider->id, 'category_id' => $ac->id, 'name' => 'Massage 1 h', 'price_xof' => 25000, 'duration_minutes' => 60, 'max_participants' => 2, 'is_available' => true]);
        $payload = ['bookable_type' => 'leisure', 'bookable_id' => $venue->id, 'activity_id' => $massage->id, 'booking_date' => now()->addDays(2)->toDateString(), 'booking_time' => '15:00', 'full_name' => 'Awa', 'email' => 'awa@example.ci'];

        $this->get(route('leisure.show', 'spa-riviera'))->assertOk()->assertSee('Massage 1 h');
        $this->actingAs(User::factory()->create())->post(route('booking-requests.store'), $payload + ['party_size' => 3])->assertSessionHasErrors('party_size', null, 'booking');
        $this->actingAs(User::factory()->create())->post(route('booking-requests.store'), $payload + ['party_size' => 2])->assertSessionHas('booking_success');

        $b = BookingRequest::firstOrFail();
        $this->assertSame(['Massage 1 h', $massage->id, 50000], [$b->item_name, $b->item_id, $b->amount_due_xof]);
    }
}
