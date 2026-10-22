<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Filet de sécurité de la connexion : le bon couple identifiant / mot de passe ouvre une session,
 * un mauvais mot de passe est refusé avec le message habituel.
 */
class AuthLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_correct_credentials_open_a_session(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email' => 'admin@exemple.test', 'password_hash' => 'Admin@2025!']);

        $this->post(route('login'), ['email' => 'admin@exemple.test', 'password' => 'Admin@2025!'])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);
    }

    public function test_wrong_password_is_refused(): void
    {
        User::factory()->create(['role' => 'admin', 'email' => 'admin@exemple.test', 'password_hash' => 'Admin@2025!']);

        $this->from(route('login'))
            ->post(route('login'), ['email' => 'admin@exemple.test', 'password' => 'mauvais'])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['email' => 'Email ou mot de passe incorrect.']);

        $this->assertGuest();
    }
}
