<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use App\Notifications\EmailVerificationCodeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/** API mobile — compte : connexion par jeton, inscription, code e-mail, déconnexion. */
class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    private function user(array $attrs = []): User
    {
        return User::create($attrs + [
            'first_name' => 'Awa', 'last_name' => 'Koné', 'email' => 'awa@exemple.ci',
            'password_hash' => Hash::make('MotDePasse123!'), 'role' => 'visitor',
            'is_active' => true, 'is_verified' => true, 'email_verified_at' => now(),
        ]);
    }

    public function test_login_returns_a_token_usable_on_protected_routes(): void
    {
        $this->user();

        $res = $this->postJson('/api/v1/auth/login', ['email' => 'awa@exemple.ci', 'password' => 'MotDePasse123!', 'device_name' => 'Pixel 8'])
            ->assertOk()
            ->assertJsonStructure(['token', 'token_type', 'expires_at', 'user' => ['id', 'email', 'full_name', 'role', 'email_verified']])
            ->assertJsonPath('user.email', 'awa@exemple.ci')
            ->assertJsonPath('token_type', 'Bearer');

        $this->withToken($res->json('token'))->getJson('/api/v1/auth/me')
            ->assertOk()->assertJsonPath('data.first_name', 'Awa');
    }

    public function test_wrong_password_inactive_account_and_admin_are_refused(): void
    {
        $this->user();
        $this->postJson('/api/v1/auth/login', ['email' => 'awa@exemple.ci', 'password' => 'mauvais', 'device_name' => 'x'])
            ->assertStatus(422)->assertJsonValidationErrors('email');

        $this->user(['email' => 'off@exemple.ci', 'is_active' => false]);
        $this->postJson('/api/v1/auth/login', ['email' => 'off@exemple.ci', 'password' => 'MotDePasse123!', 'device_name' => 'x'])
            ->assertStatus(403);

        $this->user(['email' => 'admin@exemple.ci', 'role' => 'admin']);
        $this->postJson('/api/v1/auth/login', ['email' => 'admin@exemple.ci', 'password' => 'MotDePasse123!', 'device_name' => 'x'])
            ->assertStatus(403);
    }

    public function test_protected_route_without_token_returns_json_401(): void
    {
        $this->get('/api/v1/auth/me')->assertStatus(401)->assertJsonPath('message', 'Connexion requise.');
    }

    public function test_register_creates_visitor_sends_code_and_code_verifies_email(): void
    {
        Notification::fake();

        $res = $this->postJson('/api/v1/auth/register', [
            'first_name' => 'Yao', 'last_name' => 'Kouassi', 'email' => 'yao@exemple.ci',
            'password' => 'Un-Mot2Passe-Solide!', 'password_confirmation' => 'Un-Mot2Passe-Solide!',
            'terms' => true, 'device_name' => 'Galaxy A15',
        ])->assertCreated()->assertJsonPath('user.role', 'visitor')->assertJsonPath('user.email_verified', false);

        $user = User::where('email', 'yao@exemple.ci')->firstOrFail();
        $code = null;
        Notification::assertSentTo($user, EmailVerificationCodeNotification::class, function ($n) use (&$code) {
            $code = (fn () => $this->code)->call($n);

            return true;
        });

        $token = $res->json('token');
        $this->withToken($token)->postJson('/api/v1/auth/email/verify', ['code' => '000000'])
            ->assertStatus(422)->assertJsonValidationErrors('code');
        $this->withToken($token)->postJson('/api/v1/auth/email/verify', ['code' => $code])
            ->assertOk()->assertJsonPath('user.email_verified', true);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_logout_revokes_only_the_current_token(): void
    {
        $user = $this->user();
        $phone = $user->createToken('Téléphone')->plainTextToken;
        $tablet = $user->createToken('Tablette')->plainTextToken;

        $this->withToken($phone)->postJson('/api/v1/auth/logout')->assertOk();
        $this->assertSame(1, $user->tokens()->count());

        app('auth')->forgetGuards();
        $this->withToken($tablet)->getJson('/api/v1/auth/me')->assertOk();
    }

    public function test_responses_follow_the_phone_language(): void
    {
        $this->withHeader('Accept-Language', 'en-US,en;q=0.9')->getJson('/api/v1/meta')
            ->assertOk()->assertHeader('Content-Language', 'en')
            ->assertJsonStructure(['data' => ['api_version', 'app' => ['android' => ['min_version', 'store_url']], 'site', 'pages']]);
    }
}
