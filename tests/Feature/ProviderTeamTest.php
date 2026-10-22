<?php

namespace Tests\Feature;

use App\Models\BookingRequest;
use App\Models\Provider;
use App\Models\ProviderCategory;
use App\Models\ProviderMember;
use App\Models\Restaurant;
use App\Models\TouristCity;
use App\Models\User;
use App\Notifications\ResetPasswordNotification as ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/** Comptes équipe : invitation, accès limité aux opérations du quotidien, retrait d'accès. */
class ProviderTeamTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Provider $provider;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Notification::fake();
        $this->owner = User::factory()->provider()->create();
        $cat = ProviderCategory::create(['slug' => 'restaurants', 'name_fr' => 'Restaurants', 'name_en' => 'Restaurants', 'is_active' => true]);
        $this->provider = Provider::create(['user_id' => $this->owner->id, 'category_id' => $cat->id, 'name' => 'Chez Tantie', 'slug' => 'chez-tantie', 'status' => 'active']);
        $city = TouristCity::create(['name' => 'Abidjan', 'slug' => 'abidjan-'.uniqid(), 'is_active' => true]);
        Restaurant::create(['city_id' => $city->id, 'provider_id' => $this->provider->id, 'name' => 'Chez Tantie', 'slug' => 'chez-tantie', 'is_active' => true]);
    }

    private function invite(): User
    {
        $this->actingAs($this->owner)->post(route('provider.team.store'), [
            'first_name' => 'Koffi', 'last_name' => 'Yao', 'email' => 'koffi@chez-tantie.ci', 'job_title' => 'Serveur',
        ])->assertSessionHas('success');

        return User::where('email', 'koffi@chez-tantie.ci')->firstOrFail();
    }

    public function test_owner_invites_a_member_who_receives_a_set_password_link(): void
    {
        $member = $this->invite();

        $this->assertSame('provider', $member->role);
        $this->assertNotNull($member->email_verified_at);
        $this->assertTrue(ProviderMember::where('user_id', $member->id)->where('provider_id', $this->provider->id)->exists());
        Notification::assertSentTo($member, ResetPassword::class);
        $this->actingAs($this->owner)->get(route('provider.team.index'))->assertOk()->assertSee('Koffi Yao')->assertSee('invitation en attente');
    }

    public function test_member_handles_daily_operations_but_not_money_or_settings(): void
    {
        $member = $this->invite();
        $booking = BookingRequest::create([
            'provider_id' => $this->provider->id, 'bookable_type' => 'restaurant', 'bookable_id' => 1, 'bookable_name' => 'Chez Tantie',
            'booking_date' => now()->addDay()->toDateString(), 'booking_time' => '20:00', 'party_size' => 2,
            'full_name' => 'Awa', 'email' => 'awa@example.ci', 'status' => 'new',
        ]);

        $this->actingAs($member)->get(route('provider.booking-requests.index'))->assertOk()->assertSee('Awa');
        $this->actingAs($member)->post(route('provider.booking-requests.respond', $booking), ['decision' => 'confirm'])->assertRedirect();
        $this->assertSame('confirmed', $booking->fresh()->status);
        $this->actingAs($member)->get(route('provider.tickets.index'))->assertOk();
        $this->actingAs($member)->get(route('provider.restaurant.tables.edit'))->assertOk();

        foreach (['provider.wallet.index', 'provider.billing.plans', 'provider.verification.index', 'provider.team.index', 'provider.restaurant.profile.edit'] as $forbidden) {
            $this->actingAs($member)->get(route($forbidden))->assertRedirect(route('provider.booking-requests.index'));
        }
        $this->actingAs($member)->post(route('provider.wallet.request-payout'), ['amount_xof' => 1000])->assertRedirect(route('provider.booking-requests.index'));
    }

    public function test_removing_a_member_blocks_login_and_open_sessions(): void
    {
        $member = $this->invite();
        $member->update(['password_hash' => Hash::make('Secret#2026')]);
        $link = ProviderMember::where('user_id', $member->id)->firstOrFail();

        $this->actingAs($this->owner)->delete(route('provider.team.destroy', $link))->assertSessionHas('success');
        $this->assertFalse($member->fresh()->is_active);

        $this->actingAs($member->fresh())->get(route('provider.booking-requests.index'))->assertRedirect(route('login'));
        $this->assertGuest();

        $this->post(route('login.post'), ['email' => 'koffi@chez-tantie.ci', 'password' => 'Secret#2026'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_an_owner_cannot_remove_another_providers_member(): void
    {
        $member = $this->invite();
        $otherOwner = User::factory()->provider()->create();
        Provider::create(['user_id' => $otherOwner->id, 'category_id' => $this->provider->category_id, 'name' => 'Autre', 'slug' => 'autre', 'status' => 'active']);

        $this->actingAs($otherOwner)->delete(route('provider.team.destroy', ProviderMember::where('user_id', $member->id)->first()))->assertNotFound();
        $this->assertTrue($member->fresh()->is_active);
    }
}
