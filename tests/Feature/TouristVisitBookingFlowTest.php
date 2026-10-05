<?php

namespace Tests\Feature;

use App\Models\Provider;
use App\Models\ProviderCategory;
use App\Models\TouristCity;
use App\Models\TouristExperience;
use App\Models\TouristVisit;
use App\Models\TouristVisitSession;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Parcours de réservation de bout en bout, via le paiement wallet (évite de
 * mocker CinetPay — même logique que les tests wallet existants pour les
 * réservations hôtelières et les commandes d'œuvres).
 */
class TouristVisitBookingFlowTest extends TestCase
{
    use RefreshDatabase;

    private function makeExperience(array $overrides = []): TouristExperience
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

        return TouristExperience::create(array_merge([
            'provider_id' => $provider->id,
            'city_id' => $city->id,
            'name' => 'Expérience Test',
            'slug' => 'experience-test-'.uniqid(),
            'is_active' => true,
            'visit_individual_enabled' => true,
            'visit_individual_price_xof' => 5000,
        ], $overrides));
    }

    public function test_paid_individual_visit_via_wallet_credits_provider_exactly_once(): void
    {
        $experience = $this->makeExperience();
        $user = User::factory()->create();
        Wallet::forUser($user)->update(['balance_available_xof' => 50000]);

        $response = $this->actingAs($user)->postJson(route('tourist-visit.pay', $experience->slug), [
            'visit_type' => 'individual',
            'participants_count' => 2,
            'full_name' => 'Client Test',
            'email' => 'client@example.ci',
            'payment_method' => 'wallet',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $visit = TouristVisit::first();
        $this->assertSame(TouristVisit::STATUS_PAID, $visit->status);
        $this->assertSame(10000, $visit->amount_total_xof);
        $this->assertSame('wallet', $visit->gateway);

        $this->assertSame(40000, Wallet::forUser($user)->fresh()->balance_available_xof);
        $this->assertSame(9000, Wallet::forProvider($experience->provider)->fresh()->balance_available_xof);

        // Idempotence : un second appel direct au service ne doit rien créditer de plus.
        app(\App\Services\WalletService::class)->creditSaleForVisit($visit);
        $this->assertSame(9000, Wallet::forProvider($experience->provider)->fresh()->balance_available_xof);
        $this->assertSame(
            1,
            WalletTransaction::where('tourist_visit_id', $visit->id)->where('type', WalletTransaction::TYPE_VISIT_SALE_COLLECTED)->count()
        );
    }

    public function test_free_visit_is_confirmed_without_any_wallet_transaction(): void
    {
        $experience = $this->makeExperience(['visit_individual_price_xof' => null]);
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('tourist-visit.pay', $experience->slug), [
            'visit_type' => 'individual',
            'participants_count' => 1,
            'full_name' => 'Client Test',
            'email' => 'client@example.ci',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $visit = TouristVisit::first();
        $this->assertSame(TouristVisit::STATUS_PAID, $visit->status);
        $this->assertSame(0, $visit->amount_total_xof);
        $this->assertSame(0, WalletTransaction::where('tourist_visit_id', $visit->id)->count());
    }

    public function test_group_visit_reserves_seat_immediately_on_creation_before_any_payment_step(): void
    {
        $experience = $this->makeExperience(['visit_group_enabled' => true]);
        $session = TouristVisitSession::create([
            'tourist_experience_id' => $experience->id,
            'session_date' => now()->addDays(5)->toDateString(),
            'capacity' => 10,
            'price_per_person_xof' => 2000,
            'is_active' => true,
        ]);
        $user = User::factory()->create();
        Wallet::forUser($user)->update(['balance_available_xof' => 50000]);

        $response = $this->actingAs($user)->postJson(route('tourist-visit.pay', $experience->slug), [
            'visit_type' => 'group',
            'session_id' => $session->id,
            'participants_count' => 3,
            'full_name' => 'Client Test',
            'email' => 'client@example.ci',
            'payment_method' => 'wallet',
        ]);

        $response->assertOk();
        $this->assertSame(7, $session->remainingSeats(), 'Les 3 places doivent être décomptées dès la création de la visite.');
    }
}
