<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\Provider;
use App\Models\ProviderCategory;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\SubscriptionService;
use App\Support\PlanBundle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Prestataire multi-secteurs : plusieurs secteurs choisis à l'inscription ou au paiement, montant = somme
 * des forfaits, un abonnement par secteur, accès à l'espace de chaque secteur et menu regroupé par secteur.
 */
class MultiSectorProviderTest extends TestCase
{
    use RefreshDatabase;

    private ProviderCategory $hotels;

    private ProviderCategory $restaurants;

    private ProviderCategory $art;

    private SubscriptionPlan $silver;

    private SubscriptionPlan $gold;

    private SubscriptionPlan $artPlan;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Notification::fake();
        config(['services.cynetpay.api_key' => '', 'services.cynetpay.api_password' => '']); // paiement hors passerelle

        $this->hotels = ProviderCategory::firstOrCreate(['slug' => 'hotels'], ['name_fr' => 'Hôtels', 'name_en' => 'Hotels', 'is_active' => true, 'sort_order' => 1]);
        $this->restaurants = ProviderCategory::firstOrCreate(['slug' => 'restaurants'], ['name_fr' => 'Restaurants', 'name_en' => 'Restaurants', 'is_active' => true, 'sort_order' => 2]);
        $this->art = ProviderCategory::firstOrCreate(['slug' => 'art-creations'], ['name_fr' => 'Art', 'name_en' => 'Art', 'is_active' => true, 'sort_order' => 3]);
        $this->silver = $this->plan('silver', 24900, 249000);
        $this->gold = $this->plan('gold', 49900, 499000);
        $this->artPlan = $this->plan('bronze', 15000, 150000, $this->art->id);
    }

    private function plan(string $code, int $monthly, int $yearly, ?int $categoryId = null): SubscriptionPlan
    {
        return SubscriptionPlan::updateOrCreate(['code' => $code, 'provider_category_id' => $categoryId], [
            'name_fr' => ucfirst($code), 'name_en' => ucfirst($code),
            'price_monthly' => $monthly, 'price_yearly' => $yearly, 'photos_limit' => 10, 'description_chars' => 500,
            'stats_level' => 'basic', 'support_level' => 'email', 'min_duration_months' => 1, 'is_active' => true,
        ]);
    }

    private function hotelOwner(): array
    {
        $user = User::factory()->provider()->create();
        $provider = Provider::create(['user_id' => $user->id, 'category_id' => $this->hotels->id, 'name' => 'Hôtel Lagune', 'slug' => 'hotel-lagune', 'status' => 'active']);

        return [$user, $provider];
    }

    public function test_bundle_keeps_one_plan_per_sector_and_sums_prices(): void
    {
        // Le même forfait générique pour deux secteurs ; un secteur en double et un forfait ciblé mal rattaché sont écartés.
        $items = PlanBundle::items($this->silver, 'hotels', 'restaurants:'.$this->gold->id.',hotels:'.$this->gold->id.',restaurants:'.$this->silver->id.',hotels:'.$this->artPlan->id);

        $this->assertSame(['hotels', 'restaurants'], $items->map(fn ($i) => $i['sector']->slug)->all());
        $this->assertSame(24900.0 + 49900.0, PlanBundle::total($items, 'monthly'));
        $this->assertSame(249000.0 + 499000.0, PlanBundle::total($items, 'yearly'));

        $withArt = PlanBundle::items($this->silver, 'hotels', 'art-creations:'.$this->artPlan->id);
        $this->assertSame('art-creations', $withArt[1]['sector']->slug);
    }

    public function test_offline_checkout_creates_one_subscription_per_sector_and_opens_each_space(): void
    {
        [$user, $provider] = $this->hotelOwner();
        $this->actingAs($user)->get(route('provider.menu.dashboard'))->assertForbidden();

        $this->actingAs($user)->get(route('subscriptions.checkout', ['plan' => $this->silver, 'categorie' => 'hotels', 'avec' => 'restaurants:'.$this->gold->id]))
            ->assertOk()->assertSee('Abonnement multi-secteurs')->assertSee('74 800', false);

        $this->actingAs($user)->post(route('subscriptions.process-offline', $this->silver), [
            'sector' => 'hotels', 'avec' => 'restaurants:'.$this->gold->id,
            'billing_cycle' => 'monthly', 'method' => 'wave', 'first_name' => 'Awa', 'last_name' => 'Koné',
            'payment_reference' => 'REF-123', 'accept_cgu' => '1',
        ])->assertRedirect();

        $subs = Subscription::where('provider_id', $provider->id)->where('status', 'active')->get();
        $this->assertCount(2, $subs);
        $this->assertEqualsCanonicalizing([$this->hotels->id, $this->restaurants->id], $subs->pluck('sector_category_id')->all());
        $this->assertNotNull($subs->firstWhere('sector_category_id', $this->restaurants->id)->bundle_parent_id);
        $this->assertSame(74800.0, (float) Payment::where('provider_id', $provider->id)->value('amount'));

        $provider->refresh();
        $this->assertSame(['hotels', 'restaurants'], $provider->sectorSlugs());
        // L'espace restaurant s'ouvre (plus de 403) ; le menu affiche une rubrique par secteur.
        $this->actingAs($user)->get(route('provider.menu.dashboard'))->assertOk();
        $this->actingAs($user)->get(route('provider.restaurant.profile.edit'))->assertOk();
        $html = $this->actingAs($user)->get(route('provider.dashboard'))->assertOk()->assertSee('Mes secteurs')->getContent();
        $this->assertStringContainsString('>Mon hébergement</p>', $html);
        $this->assertStringContainsString('>Mon restaurant</p>', $html);
    }

    public function test_a_new_plan_only_replaces_the_subscription_of_the_same_sector(): void
    {
        [, $provider] = $this->hotelOwner();
        $service = app(SubscriptionService::class);
        $hotelSub = $service->createSubscription($provider, $this->silver, 'monthly', 'wave', 'T1', null, $this->hotels);
        $restoSub = $service->createSubscription($provider, $this->silver, 'monthly', 'wave', 'T2', null, $this->restaurants);

        $service->createSubscription($provider, $this->gold, 'monthly', 'wave', 'T3', null, $this->restaurants);

        $this->assertSame('active', $hotelSub->fresh()->status);
        $this->assertSame('cancelled', $restoSub->fresh()->status);
        $this->assertSame(2, Subscription::where('provider_id', $provider->id)->where('status', 'active')->count());
    }

    public function test_paid_bundle_activates_every_sector_subscription(): void
    {
        [, $provider] = $this->hotelOwner();
        $main = Subscription::create(['provider_id' => $provider->id, 'plan_id' => $this->silver->id, 'sector_category_id' => $this->hotels->id, 'status' => 'pending', 'billing_cycle' => 'monthly', 'payment_method' => 'cinetpay', 'starts_at' => now(), 'ends_at' => now()->addMonth()]);
        $child = Subscription::create(['provider_id' => $provider->id, 'plan_id' => $this->artPlan->id, 'sector_category_id' => $this->art->id, 'status' => 'pending', 'billing_cycle' => 'monthly', 'payment_method' => 'cinetpay', 'starts_at' => now(), 'ends_at' => now()->addMonth(), 'bundle_parent_id' => $main->id]);

        app(SubscriptionService::class)->activatePendingSubscription($main);

        $this->assertSame('active', $main->fresh()->status);
        $this->assertSame('active', $child->fresh()->status);
        $this->assertTrue($provider->fresh()->hasSector('art-creations'));
        $this->assertTrue($provider->fresh()->isArtist());
    }

    public function test_registration_with_several_sectors_opens_them_and_goes_to_a_single_checkout(): void
    {
        $avec = 'restaurants:'.$this->gold->id;
        $this->get(route('register', ['role' => 'provider', 'plan' => $this->silver->id, 'categorie' => 'hotels', 'avec' => $avec]))
            ->assertOk()->assertSee('2 secteurs sélectionnés')->assertSee('74 800', false);

        $this->post(route('register'), [
            'first_name' => 'Yao', 'last_name' => 'Kouassi', 'email' => 'yao@exemple.ci', 'phone' => '0700000000',
            'password' => 'Un-Mot-De-Passe-Solide-2026!', 'password_confirmation' => 'Un-Mot-De-Passe-Solide-2026!',
            'role' => 'provider', 'terms' => '1', 'plan_id' => $this->silver->id, 'category_slug' => 'hotels', 'avec' => $avec,
        ])->assertRedirect(route('subscriptions.checkout', ['plan' => $this->silver->id, 'categorie' => 'hotels', 'avec' => $avec]));

        $provider = Provider::where('user_id', User::where('email', 'yao@exemple.ci')->value('id'))->firstOrFail();
        $this->assertSame(['hotels', 'restaurants'], $provider->sectorSlugs());
    }

    public function test_plans_page_allows_several_sectors_and_flags_the_providers_own(): void
    {
        [$user] = $this->hotelOwner();

        $html = $this->actingAs($user)->get(route('plans.public', ['secteurs' => 'hotels,restaurants']))->assertOk()
            ->assertSee('Choisissez un ou plusieurs secteurs')->assertSee('Votre secteur')->getContent();
        $this->assertSame(2, substr_count($html, 'class="category-card lc c') - substr_count($html, 'category-card lc c3 '));
        $this->assertStringContainsString('data-pick-plan="'.$this->silver->id.'" data-sector="restaurants"', $html);
    }
}
