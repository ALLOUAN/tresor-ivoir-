<?php

namespace Tests\Feature;

use App\Models\Provider;
use App\Models\ProviderCategory;
use App\Models\TouristCity;
use App\Models\TouristExperience;
use App\Models\TouristVisit;
use App\Models\TouristVisitSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Couvre la règle métier centrale du cahier des charges : la disponibilité d'une
 * session de visite groupée est gérée indépendamment de toute autre session,
 * même sur le même site (cf. exemple 15/16 octobre, 20 places chacune).
 */
class TouristVisitSessionCapacityTest extends TestCase
{
    use RefreshDatabase;

    private function makeExperience(): TouristExperience
    {
        $providerUser = User::factory()->provider()->create();
        $category = ProviderCategory::create(['slug' => 'sites-touristiques-test-'.uniqid(), 'name_fr' => 'Sites Touristiques', 'name_en' => 'Tourist Sites', 'is_active' => true]);
        $provider = Provider::create([
            'user_id' => $providerUser->id,
            'category_id' => $category->id,
            'name' => 'Site Test',
            'slug' => 'site-test-'.uniqid(),
            'status' => 'active',
        ]);
        $city = TouristCity::create(['name' => 'Ville Test '.uniqid(), 'slug' => 'ville-test-'.uniqid(), 'is_active' => true]);

        return TouristExperience::create([
            'provider_id' => $provider->id,
            'city_id' => $city->id,
            'name' => 'Expérience Test',
            'slug' => 'experience-test-'.uniqid(),
            'is_active' => true,
            'visit_group_enabled' => true,
        ]);
    }

    private function makeVisit(TouristVisitSession $session, int $participants, string $status): TouristVisit
    {
        return TouristVisit::create([
            'tourist_experience_id' => $session->tourist_experience_id,
            'tourist_visit_session_id' => $session->id,
            'experience_name' => 'Expérience Test',
            'visit_type' => TouristVisit::TYPE_GROUP,
            'participants_count' => $participants,
            'full_name' => 'Client Test',
            'email' => 'client-test@example.ci',
            'status' => $status,
        ]);
    }

    public function test_two_sessions_with_equal_capacity_are_fully_independent(): void
    {
        $experience = $this->makeExperience();

        $sessionA = TouristVisitSession::create([
            'tourist_experience_id' => $experience->id,
            'session_date' => now()->addDays(15)->toDateString(),
            'period_label' => '10h00',
            'capacity' => 20,
            'is_active' => true,
        ]);
        $sessionB = TouristVisitSession::create([
            'tourist_experience_id' => $experience->id,
            'session_date' => now()->addDays(16)->toDateString(),
            'period_label' => '10h00',
            'capacity' => 20,
            'is_active' => true,
        ]);

        $this->makeVisit($sessionA, 4, TouristVisit::STATUS_PAID);

        $this->assertSame(16, $sessionA->remainingSeats(), 'Session A doit refléter la réservation de 4 places.');
        $this->assertSame(20, $sessionB->remainingSeats(), 'Session B ne doit jamais être affectée par une réservation sur la session A.');
    }

    public function test_booking_exactly_remaining_seats_fills_the_session(): void
    {
        $experience = $this->makeExperience();
        $session = TouristVisitSession::create([
            'tourist_experience_id' => $experience->id,
            'session_date' => now()->addDays(5)->toDateString(),
            'capacity' => 5,
            'is_active' => true,
        ]);

        $this->makeVisit($session, 5, TouristVisit::STATUS_PAID);

        $this->assertSame(0, $session->remainingSeats());
        $this->assertTrue($session->isFull());
    }

    public function test_booking_more_than_remaining_seats_is_rejected(): void
    {
        $experience = $this->makeExperience();
        $session = TouristVisitSession::create([
            'tourist_experience_id' => $experience->id,
            'session_date' => now()->addDays(5)->toDateString(),
            'capacity' => 5,
            'is_active' => true,
        ]);
        $this->makeVisit($session, 3, TouristVisit::STATUS_PAID);

        $user = User::factory()->create();
        $response = $this->actingAs($user)->postJson(route('tourist-visit.pay', $experience->slug), [
            'visit_type' => 'group',
            'session_id' => $session->id,
            'participants_count' => 3, // seulement 2 places restantes
            'full_name' => 'Client Test',
            'email' => 'client2@example.ci',
        ]);

        $response->assertStatus(422);
        $this->assertSame(1, TouristVisit::where('tourist_visit_session_id', $session->id)->count(), 'Aucune nouvelle visite ne doit avoir été créée.');
    }

    public function test_cancelled_visits_do_not_count_against_capacity(): void
    {
        $experience = $this->makeExperience();
        $session = TouristVisitSession::create([
            'tourist_experience_id' => $experience->id,
            'session_date' => now()->addDays(5)->toDateString(),
            'capacity' => 5,
            'is_active' => true,
        ]);
        $this->makeVisit($session, 5, TouristVisit::STATUS_CANCELLED);

        $this->assertSame(5, $session->remainingSeats(), 'Une visite annulée doit libérer sa place.');
    }
}
