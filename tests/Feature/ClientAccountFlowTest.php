<?php

namespace Tests\Feature;

use App\Models\NewsletterSubscriber;
use App\Models\User;
use App\Notifications\EmailVerificationCodeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/** Création de compte client : règles communes, anti-robots, newsletter, compte express par code. */
class ClientAccountFlowTest extends TestCase
{
    use RefreshDatabase;

    private function registerPayload(array $over = []): array
    {
        return $over + [
            'first_name' => 'Awa', 'last_name' => 'Koné', 'email' => 'awa@exemple.ci',
            'password' => 'MotDePasse123!', 'password_confirmation' => 'MotDePasse123!',
            'role' => 'visitor', 'terms' => '1',
        ];
    }

    /** Remplace le code envoyé par un code connu (le vrai n'est conservé que sous forme hachée). */
    private function knownCode(string $email, string $code = '123456'): string
    {
        $key = 'express_code:'.sha1($email);
        Cache::put($key, ['hash' => Hash::make($code)] + Cache::get($key), now()->addMinutes(10));

        return $code;
    }

    public function test_honeypot_blocks_bots(): void
    {
        Notification::fake();
        $this->post(route('register.post'), $this->registerPayload(['website' => 'http://spam.example']))
            ->assertSessionHasErrors('website');
        $this->assertDatabaseMissing('users', ['email' => 'awa@exemple.ci']);
    }

    public function test_new_client_must_confirm_email_before_reaching_the_dashboard(): void
    {
        Notification::fake();
        $this->post(route('register.post'), $this->registerPayload())
            ->assertRedirect(route('verification.notice'));

        $user = User::firstWhere('email', 'awa@exemple.ci');
        $this->assertFalse($user->hasVerifiedEmail());
        Notification::assertSentTo($user, EmailVerificationCodeNotification::class);

        // Espace client fermé tant que l'adresse n'est pas confirmée.
        $this->get(route('visitor.dashboard'))->assertRedirect(route('verification.notice'));
        $this->get(route('verification.notice'))->assertOk();

        // Le code (conservé haché) est remplacé par un code connu, puis saisi.
        $key = 'email_verification_code:'.$user->getKey();
        Cache::put($key, ['hash' => Hash::make('123456')] + Cache::get($key), now()->addMinutes(10));
        $this->post(route('verification.code'), ['verification_code' => '123 456'])
            ->assertRedirect(route('visitor.dashboard'));

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $this->get(route('visitor.dashboard'))->assertOk();
    }

    public function test_taken_email_offers_login_and_password_reset(): void
    {
        Notification::fake();
        $this->post(route('register.post'), $this->registerPayload())->assertRedirect();
        auth()->logout();

        $this->from(route('register', ['role' => 'visitor']))
            ->followingRedirects()
            ->post(route('register.post'), $this->registerPayload())
            ->assertSee('Cette adresse e-mail est déjà utilisée.')
            ->assertSee('Vous avez déjà un compte avec cette adresse.')
            ->assertSee(route('password.request'), false);
    }

    public function test_newsletter_is_opt_in_only(): void
    {
        Notification::fake();
        $this->post(route('register.post'), $this->registerPayload())->assertRedirect();
        $this->assertSame(0, NewsletterSubscriber::count());

        auth()->logout();
        $this->post(route('register.post'), $this->registerPayload(['email' => 'bintou@exemple.ci', 'newsletter_opt_in' => '1']))->assertRedirect();
        $this->assertDatabaseHas('newsletter_subscribers', ['email' => 'bintou@exemple.ci', 'status' => 'active', 'source' => 'register']);
    }

    public function test_register_is_rate_limited(): void
    {
        Notification::fake();
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('register.post'), $this->registerPayload(['email' => "c$i@exemple.ci"]));
            auth()->logout();
        }
        $this->post(route('register.post'), $this->registerPayload(['email' => 'c9@exemple.ci']))->assertStatus(429);
    }

    public function test_express_creates_a_verified_account_and_logs_in(): void
    {
        Notification::fake();
        $this->postJson(route('account.express.start'), ['full_name' => 'Yao Kouassi', 'email' => 'Yao@Exemple.ci', 'phone' => '+2250700000000'])
            ->assertOk()->assertJson(['status' => 'code_sent']);
        Notification::assertSentOnDemand(EmailVerificationCodeNotification::class);

        $code = $this->knownCode('yao@exemple.ci');
        $this->postJson(route('account.express.verify'), ['code' => '000000'])->assertStatus(422);
        $this->postJson(route('account.express.verify'), ['code' => $code])->assertOk()->assertJson(['status' => 'ok']);

        $user = User::firstWhere('email', 'yao@exemple.ci');
        $this->assertNotNull($user->email_verified_at);
        $this->assertSame(['Yao', 'Kouassi', 'visitor', '+2250700000000'], [$user->first_name, $user->last_name, $user->role, $user->phone]);
        $this->assertAuthenticatedAs($user);
    }

    public function test_code_pasted_with_a_space_is_accepted(): void
    {
        Notification::fake();
        $this->postJson(route('account.express.start'), ['full_name' => 'Jean Allou', 'email' => 'jean@exemple.ci'])->assertOk();
        $this->knownCode('jean@exemple.ci', '860431');
        $this->postJson(route('account.express.verify'), ['code' => '860 431'])->assertOk()->assertJson(['status' => 'ok']);

        $client = User::factory()->unverified()->create(['email' => 'ama@exemple.ci', 'role' => 'visitor']);
        \Illuminate\Support\Facades\Cache::put('email_verification_code:'.$client->id, ['hash' => Hash::make('607129'), 'expires_at' => now()->addMinutes(10)->timestamp], now()->addMinutes(10));
        $this->actingAs($client)->post(route('verification.code'), ['verification_code' => '607 129'])->assertSessionHasNoErrors();
        $this->assertNotNull($client->fresh()->email_verified_at);
    }

    public function test_express_logs_in_an_existing_client_without_password(): void
    {
        Notification::fake();
        $client = User::factory()->create(['email' => 'mariam@exemple.ci', 'role' => 'visitor']);
        $this->postJson(route('account.express.start'), ['full_name' => 'Mariam', 'email' => 'mariam@exemple.ci'])->assertOk();
        $this->postJson(route('account.express.verify'), ['code' => $this->knownCode('mariam@exemple.ci')])->assertOk();

        $this->assertAuthenticatedAs($client);
        $this->assertSame(1, User::where('email', 'mariam@exemple.ci')->count());
    }

    public function test_express_refuses_professional_accounts_and_limits_attempts(): void
    {
        Notification::fake();
        User::factory()->create(['email' => 'pro@exemple.ci', 'role' => 'provider']);
        $this->postJson(route('account.express.start'), ['full_name' => 'Pro', 'email' => 'pro@exemple.ci'])->assertStatus(422);

        $this->postJson(route('account.express.start'), ['full_name' => 'Adjoua', 'email' => 'adjoua@exemple.ci'])->assertOk();
        for ($i = 0; $i < 5; $i++) {
            $this->postJson(route('account.express.verify'), ['code' => '000000'])->assertStatus(422);
        }
        // Après 5 erreurs le code est détruit : même le bon code ne passe plus.
        $this->assertNull(Cache::get('express_code:'.sha1('adjoua@exemple.ci')));
        $this->assertGuest();
    }

    public function test_unverified_logged_in_client_confirms_inline(): void
    {
        Notification::fake();
        $client = User::factory()->unverified()->create(['email' => 'koffi@exemple.ci', 'role' => 'visitor']);
        $this->actingAs($client)->postJson(route('account.express.start'), [])->assertOk()->assertJson(['status' => 'code_sent']);
        $this->postJson(route('account.express.verify'), ['code' => $this->knownCode('koffi@exemple.ci')])->assertOk();
        $this->assertNotNull($client->fresh()->email_verified_at);
    }

    public function test_deleted_account_email_is_free_again_in_pay_flows(): void
    {
        $old = User::factory()->create(['email' => 'reuse@exemple.ci', 'role' => 'visitor']);
        $old->delete();
        $rule = \Illuminate\Support\Facades\Validator::make(['email' => 'reuse@exemple.ci'], ['email' => \App\Services\ClientAccountService::uniqueEmailRule()]);
        $this->assertTrue($rule->passes());
    }
}
