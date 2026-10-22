<?php

namespace Tests\Feature;

use App\Models\PaymentSetting;
use App\Models\Provider;
use App\Models\ProviderCategory;
use App\Models\User;
use App\Services\ReservationPricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Modèle économique réglable : commission sur l'acompte (défaut) ou sur le total, accès gratuit. */
class CommissionModelSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_commission_defaults_to_the_deposit_and_can_be_based_on_the_total(): void
    {
        PaymentSetting::create(['key' => 'reservation_deposit_percent', 'value' => '30']);
        PaymentSetting::create(['key' => 'reservation_commission_percent', 'value' => '12']);
        $pricing = app(ReservationPricingService::class);
        $in = now()->addDays(5)->toDateString();
        $out = now()->addDays(7)->toDateString();

        // 2 nuits × 50 000 = 100 000 ; acompte 30 000 ; 12 % de l'acompte = 3 600.
        $this->assertSame(3600, $pricing->compute($in, $out, 1, 50000)['commission_xof']);

        PaymentSetting::create(['key' => 'reservation_commission_base', 'value' => 'total']);
        $this->assertSame(12000, $pricing->compute($in, $out, 1, 50000)['commission_xof']);

        PaymentSetting::where('key', 'reservation_commission_percent')->update(['value' => '40']);
        $this->assertSame(30000, $pricing->compute($in, $out, 1, 50000)['commission_xof'], 'Plafonnée à l\'acompte encaissé.');
    }

    public function test_free_access_lets_a_provider_without_subscription_reach_the_dashboard(): void
    {
        $user = User::factory()->provider()->create();
        $category = ProviderCategory::create(['slug' => 'hotels', 'name_fr' => 'Hôtels', 'name_en' => 'Hotels', 'is_active' => true]);
        Provider::create(['user_id' => $user->id, 'category_id' => $category->id, 'name' => 'Libre', 'slug' => 'libre', 'status' => 'active']);

        $this->actingAs($user)->get(route('dashboard'))->assertRedirect(route('provider.billing.plans'));

        PaymentSetting::create(['key' => 'provider_free_access', 'value' => '1']);
        $this->actingAs($user)->get(route('dashboard'))->assertRedirect(route('provider.dashboard'));
    }

    public function test_admin_can_save_the_new_settings(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->get(route('admin.payments.settings'))->assertOk()->assertSee('Base de calcul de la commission');
    }
}
