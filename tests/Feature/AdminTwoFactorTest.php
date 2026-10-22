<?php

namespace Tests\Feature;

use App\Mail\AdminLoginCodeMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/** Double authentification admin : code par e-mail, appareil reconnu, visiteurs non concernés. */
class AdminTwoFactorTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        config(['auth.admin_two_factor' => true]);
        $this->admin = User::factory()->admin()->create(['email' => 'chef@example.ci', 'password_hash' => Hash::make('Secret#2026'), 'is_active' => true]);
    }

    private function sentCode(): string
    {
        $code = null;
        Mail::assertSent(AdminLoginCodeMail::class, function (AdminLoginCodeMail $m) use (&$code) {
            $code = $m->code;

            return $m->hasTo('chef@example.ci');
        });

        return $code;
    }

    public function test_admin_needs_the_emailed_code_and_the_device_is_then_trusted(): void
    {
        $this->post(route('login.post'), ['email' => 'chef@example.ci', 'password' => 'Secret#2026'])
            ->assertRedirect(route('admin.two-factor.show'));
        $this->assertGuest();

        $this->get(route('admin.two-factor.show'))->assertOk()->assertSee('chef@example.ci');
        $this->post(route('admin.two-factor.verify'), ['code' => '000000'])->assertSessionHasErrors('code');
        $this->assertGuest();

        $response = $this->post(route('admin.two-factor.verify'), ['code' => $this->sentCode()]);
        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($this->admin);
        $cookie = collect($response->headers->getCookies())->first(fn ($c) => $c->getName() === 'ti_admin_device');
        $this->assertNotNull($cookie);

        // Appareil reconnu : plus de code demandé.
        $this->post(route('logout'));
        $this->withCookie('ti_admin_device', \Illuminate\Cookie\CookieValuePrefix::remove(decrypt($cookie->getValue(), false)))
            ->post(route('login.post'), ['email' => 'chef@example.ci', 'password' => 'Secret#2026'])
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_visitors_log_in_without_a_code(): void
    {
        User::factory()->create(['email' => 'visiteur@example.ci', 'password_hash' => Hash::make('Secret#2026')]);

        $this->post(route('login.post'), ['email' => 'visiteur@example.ci', 'password' => 'Secret#2026'])
            ->assertRedirect(route('visitor.dashboard'));
        Mail::assertNothingSent();
    }

    public function test_disabled_two_factor_keeps_the_usual_login(): void
    {
        config(['auth.admin_two_factor' => false]);

        $this->post(route('login.post'), ['email' => 'chef@example.ci', 'password' => 'Secret#2026'])
            ->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($this->admin);
    }
}
