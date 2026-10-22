<?php

namespace Tests\Feature;

use App\Models\Provider;
use App\Models\ProviderCategory;
use App\Models\TouristCity;
use App\Models\TouristExperience;
use App\Models\TouristVisit;
use App\Models\TouristVisitType;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Types de visite personnalisés : gestion (prestataire, admin) et réservation. */
class TouristVisitTypeTest extends TestCase
{
    use RefreshDatabase;

    private User $providerUser;

    private function makeExperience(): TouristExperience
    {
        $this->providerUser = User::factory()->provider()->create();
        $category = ProviderCategory::firstOrCreate(['slug' => 'sites-touristiques'], ['name_fr' => 'Sites Touristiques', 'name_en' => 'Tourist Sites', 'is_active' => true]);
        $provider = Provider::create([
            'user_id' => $this->providerUser->id,
            'category_id' => $category->id,
            'name' => 'Site Test',
            'slug' => 'site-test-'.uniqid(),
            'status' => 'active',
        ]);
        $city = TouristCity::create(['name' => 'Ville '.uniqid(), 'slug' => 'ville-'.uniqid(), 'is_active' => true]);

        return TouristExperience::create([
            'provider_id' => $provider->id,
            'city_id' => $city->id,
            'name' => 'Site des cascades',
            'slug' => 'site-'.uniqid(),
            'is_active' => true,
        ]);
    }

    public function test_provider_creates_updates_toggles_and_deletes_a_visit_type(): void
    {
        $experience = $this->makeExperience();

        $this->actingAs($this->providerUser)
            ->post(route('provider.tourist-experience.visit-types.store'), [
                'name' => 'Visite nocturne', 'description' => 'À la lueur des lanternes', 'icon' => 'fa-moon',
                'price_xof' => 8000, 'includes_guide' => 1, 'guide_supplement_xof' => 3000,
                'duration_minutes' => 90, 'max_participants' => 6, 'is_active' => 1,
            ])
            ->assertRedirect(route('provider.tourist-experience.visit-types.index'));

        $type = TouristVisitType::sole();
        $this->assertSame($experience->id, $type->tourist_experience_id);
        $this->assertSame('Visite nocturne', $type->name);
        $this->assertTrue($type->includes_guide);
        $this->assertSame(3000, $type->appliedGuideSupplement());

        $this->actingAs($this->providerUser)->get(route('provider.tourist-experience.visit-types.index'))
            ->assertOk()->assertSee('Visite nocturne');

        // Sans guide : le supplément est effacé.
        $this->actingAs($this->providerUser)
            ->put(route('provider.tourist-experience.visit-types.update', $type), ['name' => 'Visite nocturne', 'price_xof' => 9000, 'guide_supplement_xof' => 3000, 'is_active' => 1])
            ->assertRedirect();
        $this->assertNull($type->fresh()->guide_supplement_xof);
        $this->assertSame(9000, $type->fresh()->price_xof);

        $this->actingAs($this->providerUser)->patch(route('provider.tourist-experience.visit-types.toggle', $type))->assertRedirect();
        $this->assertFalse($type->fresh()->is_active);

        $this->actingAs($this->providerUser)->delete(route('provider.tourist-experience.visit-types.destroy', $type))->assertRedirect();
        $this->assertSame(0, TouristVisitType::count());
    }

    public function test_provider_cannot_touch_another_sites_visit_type(): void
    {
        $first = $this->makeExperience();
        $this->makeExperience(); // $this->providerUser devient le propriétaire du second site
        $foreign = TouristVisitType::create(['tourist_experience_id' => $first->id, 'name' => 'Autre', 'is_active' => true]);

        $this->actingAs($this->providerUser)->delete(route('provider.tourist-experience.visit-types.destroy', $foreign))->assertForbidden();
        $this->assertSame(1, TouristVisitType::count());
    }

    public function test_admin_manages_visit_types_of_any_site(): void
    {
        $experience = $this->makeExperience();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('admin.tourist-experiences.visit-types.index', $experience))->assertOk();
        $this->actingAs($admin)
            ->post(route('admin.tourist-experiences.visit-types.store', $experience), ['name' => 'Visite VIP', 'price_xof' => 20000, 'is_active' => 1])
            ->assertRedirect(route('admin.tourist-experiences.visit-types.index', $experience));

        $this->assertSame('Visite VIP', $experience->visitTypes()->sole()->name);
    }

    public function test_site_without_visit_type_still_shows_the_booking_card_with_a_contact_hint(): void
    {
        $experience = $this->makeExperience();

        $this->get(route('tourist-experience.show', $experience->slug))
            ->assertOk()
            ->assertSee('id="tvp-booking-module"', false)
            ->assertSee('Ce site ne propose pas encore de réservation en ligne.');

        // Le prestataire du site voit un raccourci pour créer ses types.
        $this->actingAs($this->providerUser)->get(route('tourist-experience.show', $experience->slug))
            ->assertSee(route('provider.tourist-experience.visit-types.index'), false);
    }

    public function test_guest_sees_the_visit_types_before_logging_in(): void
    {
        $experience = $this->makeExperience();
        $experience->visitTypes()->create(['name' => 'Visite VIP', 'price_xof' => 20000, 'is_active' => true]);
        $experience->visitTypes()->create(['name' => 'Visite cachée', 'price_xof' => 1000, 'is_active' => false]);

        $this->get(route('tourist-experience.show', $experience->slug))
            ->assertSee('Visite VIP')->assertDontSee('Visite cachée')
            ->assertSee('Connectez-vous pour réserver une visite');
    }

    public function test_visitor_books_a_custom_type_at_its_own_price(): void
    {
        $experience = $this->makeExperience();
        $type = $experience->visitTypes()->create(['name' => 'Visite nocturne', 'price_xof' => 8000, 'includes_guide' => true, 'guide_supplement_xof' => 3000, 'max_participants' => 4, 'is_active' => true]);
        $user = User::factory()->create();
        Wallet::forUser($user)->update(['balance_available_xof' => 100000]);

        $this->actingAs($user)->get(route('tourist-experience.show', $experience->slug))->assertSee('Visite nocturne');

        $book = fn (array $o) => $this->actingAs($user)->postJson(route('tourist-visit.pay', $experience->slug), $o + [
            'visit_type' => 'custom', 'visit_type_id' => $type->id, 'desired_date' => now()->addDays(3)->toDateString(),
            'full_name' => 'Client Test', 'email' => 'client@example.ci', 'payment_method' => 'wallet',
        ]);

        $book(['participants_count' => 2])->assertOk()->assertJson(['success' => true]);
        $visit = TouristVisit::sole();
        $this->assertSame(19000, $visit->amount_total_xof); // 2 × 8 000 + guide 3 000
        $this->assertSame('Visite nocturne', $visit->labelForType());
        $this->assertSame($type->id, $visit->tourist_visit_type_id);
        $this->assertTrue($visit->with_guide);

        // Plafond de participants du type.
        $book(['participants_count' => 5])->assertStatus(422);

        // Type désactivé ou d'un autre site : refusé.
        $type->update(['is_active' => false]);
        $book(['participants_count' => 1])->assertNotFound();
        $this->assertSame(1, TouristVisit::count());
    }
}
