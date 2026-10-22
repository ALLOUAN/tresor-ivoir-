<?php

namespace Tests\Feature;

use App\Models\Dispute;
use App\Models\Provider;
use App\Models\ProviderCategory;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Wallet;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/** Litige : ouverture par le client, gel des fonds, réponse de l'établissement, arbitrage admin. */
class DisputeFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $client;

    private User $providerUser;

    private Provider $provider;

    private Reservation $reservation;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        $this->client = User::factory()->create();
        $this->providerUser = User::factory()->provider()->create();
        $category = ProviderCategory::create(['slug' => 'hotels-'.uniqid(), 'name_fr' => 'Hôtels', 'name_en' => 'Hotels', 'is_active' => true]);
        $this->provider = Provider::create([
            'user_id' => $this->providerUser->id, 'category_id' => $category->id,
            'name' => 'Hôtel Litige', 'slug' => 'hotel-litige', 'status' => 'active', 'is_verified' => true,
        ]);
        $this->reservation = Reservation::create([
            'user_id' => $this->client->id, 'provider_id' => $this->provider->id, 'accommodation_name' => 'Hôtel Litige',
            'room_name' => 'Standard', 'check_in' => now()->subDay()->toDateString(), 'check_out' => now()->addDay()->toDateString(),
            'nights' => 2, 'rooms_count' => 1, 'guests_count' => 2, 'room_price_xof' => 30000, 'total_xof' => 60000,
            'deposit_amount_xof' => 20000, 'commission_rate_percent' => 15, 'commission_amount_xof' => 3000,
            'full_name' => 'Client', 'email' => $this->client->email,
            'status' => Reservation::STATUS_CONFIRMED, 'payment_status' => Reservation::PAYMENT_DEPOSIT_PAID,
        ]);
        app(WalletService::class)->creditDepositForReservation($this->reservation);
    }

    private function open(): void
    {
        $this->actingAs($this->client)->post(route('visitor.reservations.dispute', $this->reservation), [
            'reason' => 'closed', 'description' => 'Personne à la réception, l\'hôtel était fermé à notre arrivée.',
        ])->assertSessionHas('success');
    }

    public function test_an_open_dispute_freezes_funds_and_admin_can_refund_the_client(): void
    {
        $admin = User::factory()->admin()->create(['is_active' => true]);
        $this->open();
        $dispute = Dispute::firstOrFail();

        app(WalletService::class)->releasePendingCredits();
        $this->assertSame(0, (int) Wallet::forProvider($this->provider)->fresh()->balance_available_xof, 'Fonds gelés pendant le litige.');

        $this->actingAs($this->providerUser)->get(route('provider.disputes.index'))->assertOk()->assertSee('fermé à notre arrivée');
        $this->actingAs($this->providerUser)->post(route('provider.disputes.respond', $dispute), ['provider_response' => 'Fermeture exceptionnelle, désolés.'])->assertRedirect();

        $this->actingAs($admin)->get(route('admin.disputes.index'))->assertOk()->assertSee('Fermeture exceptionnelle');
        $this->actingAs($admin)->post(route('admin.disputes.resolve', $dispute), [
            'decision' => 'client', 'refund_xof' => 20000, 'resolution' => 'Établissement fermé, remboursement intégral.',
        ])->assertRedirect();

        $this->assertSame(Dispute::STATUS_RESOLVED_CLIENT, $dispute->fresh()->status);
        $this->assertSame(Reservation::PAYMENT_REFUNDED, $this->reservation->fresh()->payment_status);
    }

    public function test_a_decision_for_the_provider_releases_its_share(): void
    {
        $admin = User::factory()->admin()->create(['is_active' => true]);
        $this->open();

        $this->actingAs($admin)->post(route('admin.disputes.resolve', Dispute::first()), [
            'decision' => 'provider', 'resolution' => 'Séjour effectué normalement selon les preuves.',
        ])->assertRedirect();

        app(WalletService::class)->releasePendingCredits();
        $this->assertSame(17000, (int) Wallet::forProvider($this->provider)->fresh()->balance_available_xof);
    }

    public function test_only_one_dispute_and_only_by_the_owner(): void
    {
        $this->actingAs(User::factory()->create())->post(route('visitor.reservations.dispute', $this->reservation), [
            'reason' => 'other', 'description' => 'Je ne suis pas le client de cette réservation.',
        ])->assertNotFound();

        $this->open();
        $this->actingAs($this->client)->post(route('visitor.reservations.dispute', $this->reservation), [
            'reason' => 'other', 'description' => 'Deuxième signalement sur la même réservation.',
        ])->assertSessionHas('error');
        $this->assertSame(1, Dispute::count());
    }

    public function test_provider_can_download_its_wallet_statement(): void
    {
        $response = $this->actingAs($this->providerUser)->get(route('provider.wallet.export'));
        $response->assertOk();
        $this->assertStringContainsString('Montant (XOF)', $response->streamedContent());
        $this->assertStringContainsString($this->reservation->reference, $response->streamedContent());
    }
}
