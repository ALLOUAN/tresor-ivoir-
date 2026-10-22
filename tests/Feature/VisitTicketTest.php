<?php

namespace Tests\Feature;

use App\Models\Provider;
use App\Models\ProviderCategory;
use App\Models\TouristCity;
use App\Models\TouristExperience;
use App\Models\TouristVisit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Billet électronique : QR sur la confirmation, contrôle à l'entrée, usage unique, cloisonnement entre sites. */
class VisitTicketTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Provider $provider;

    private TouristExperience $site;

    protected function setUp(): void
    {
        parent::setUp();
        $this->owner = User::factory()->provider()->create();
        $cat = ProviderCategory::create(['slug' => 'sites-'.uniqid(), 'name_fr' => 'Sites', 'name_en' => 'Sites', 'is_active' => true]);
        $this->provider = Provider::create(['user_id' => $this->owner->id, 'category_id' => $cat->id, 'name' => 'Parc du Banco', 'slug' => 'parc-banco', 'status' => 'active']);
        $city = TouristCity::create(['name' => 'Abidjan', 'slug' => 'abidjan-'.uniqid(), 'is_active' => true]);
        $this->site = TouristExperience::create(['city_id' => $city->id, 'provider_id' => $this->provider->id, 'name' => 'Parc du Banco', 'slug' => 'parc-banco', 'is_active' => true]);
    }

    private function visit(array $o = []): TouristVisit
    {
        return TouristVisit::create($o + [
            'tourist_experience_id' => $this->site->id, 'provider_id' => $this->provider->id, 'experience_name' => 'Parc du Banco',
            'visit_type' => 'individual', 'participants_count' => 3, 'desired_date' => now()->toDateString(),
            'amount_total_xof' => 6000, 'full_name' => 'Awa Koné', 'email' => 'awa@example.ci', 'status' => TouristVisit::STATUS_PAID,
        ]);
    }

    public function test_paid_visit_shows_a_qr_ticket_and_the_entry_can_be_validated_once(): void
    {
        $v = $this->visit();
        $this->get($v->confirmationUrl())->assertOk()->assertSee('Votre billet électronique')->assertSee($v->fresh()->ticket_code);

        $code = $v->fresh()->ticket_code;
        $this->assertMatchesRegularExpression('/^[A-Z0-9]{10}$/', $code);

        $this->actingAs($this->owner)->get(route('provider.tickets.show', $code))->assertOk()->assertSee('Billet valable')->assertSee('Awa Koné');
        $this->actingAs($this->owner)->post(route('provider.tickets.check-in', $code))->assertSessionHas('success');
        $this->assertNotNull($v->fresh()->checked_in_at);

        $this->actingAs($this->owner)->post(route('provider.tickets.check-in', $code))->assertSessionHas('error');
        $this->actingAs($this->owner)->get(route('provider.tickets.show', $code))->assertSee('déjà utilisé');
    }

    public function test_unpaid_future_or_foreign_tickets_are_refused(): void
    {
        $pending = $this->visit(['status' => TouristVisit::STATUS_PENDING_PAYMENT]);
        $this->assertNull($pending->ticketCode(), 'Pas de billet tant que ce n\'est pas payé.');

        $future = $this->visit(['desired_date' => now()->addDays(2)->toDateString()]);
        $code = $future->ticketCode();
        $this->actingAs($this->owner)->post(route('provider.tickets.check-in', $code))->assertSessionHas('error');
        $this->assertNull($future->fresh()->checked_in_at);

        $other = User::factory()->provider()->create();
        Provider::create(['user_id' => $other->id, 'category_id' => $this->provider->category_id, 'name' => 'Autre site', 'slug' => 'autre-site', 'status' => 'active']);
        $today = $this->visit();
        $this->actingAs($other)->get(route('provider.tickets.show', $today->ticketCode()))->assertOk()->assertSee('Billet inconnu')->assertDontSee('Awa Koné');
        $this->actingAs($other)->post(route('provider.tickets.check-in', $today->ticketCode()))->assertSessionHas('error');
    }

    public function test_scanner_page_accepts_the_full_qr_url_or_the_code(): void
    {
        $code = $this->visit()->ticketCode();
        $this->actingAs($this->owner)->get(route('provider.tickets.index'))->assertOk()->assertSee("Visiteurs attendus aujourd'hui", false)->assertSee('Awa Koné');
        $this->actingAs($this->owner)->get(route('provider.tickets.index', ['code' => route('provider.tickets.show', $code)]))->assertRedirect(route('provider.tickets.show', $code));
        $this->actingAs($this->owner)->get(route('provider.tickets.index', ['code' => strtolower($code)]))->assertRedirect(route('provider.tickets.show', $code));
    }
}
