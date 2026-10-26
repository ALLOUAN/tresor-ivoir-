<?php

namespace Tests\Feature;

use App\Models\Provider;
use App\Models\User;
use App\Support\ProviderProfileBootstrap;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/** Comptes non vérifiés : supprimés après 30 jours s'ils n'ont aucune activité, jamais sinon. */
class PurgeUnverifiedAccountsTest extends TestCase
{
    use RefreshDatabase;

    private function account(string $email, string $role, int $ageDays, bool $verified = false): User
    {
        $user = User::create([
            'first_name' => 'Test', 'last_name' => 'Compte', 'email' => $email,
            'password_hash' => Hash::make('MotDePasse123!'), 'role' => $role, 'is_active' => true,
            'is_verified' => $verified, 'email_verified_at' => $verified ? now()->subDays($ageDays) : null,
        ]);
        $user->forceFill(['created_at' => now()->subDays($ageDays)])->save();

        return $user;
    }

    public function test_purges_only_old_unverified_accounts_without_activity(): void
    {
        $oldClient = $this->account('ancien@exemple.ci', 'visitor', 31);
        $oldProvider = $this->account('presta@exemple.ci', 'provider', 45);
        ProviderProfileBootstrap::ensure($oldProvider, null);
        $providerId = Provider::where('user_id', $oldProvider->id)->value('id');
        $this->assertNotNull($providerId);

        $recent = $this->account('recent@exemple.ci', 'visitor', 10);
        $verified = $this->account('verifie@exemple.ci', 'visitor', 90, true);
        $admin = $this->account('admin@exemple.ci', 'admin', 90);
        $active = $this->account('actif@exemple.ci', 'provider', 60);
        ProviderProfileBootstrap::ensure($active, null);
        DB::table('wallets')->insert(['provider_id' => Provider::where('user_id', $active->id)->value('id'), 'created_at' => now(), 'updated_at' => now()]);

        // Aperçu : rien n'est supprimé.
        $this->artisan('accounts:purge-unverified', ['--dry-run' => true])->assertSuccessful();
        $this->assertSame(6, User::withTrashed()->count());

        $this->artisan('accounts:purge-unverified')->assertSuccessful();

        $this->assertNull(User::withTrashed()->find($oldClient->id), 'Client non vérifié depuis 31 jours supprimé');
        $this->assertNull(User::withTrashed()->find($oldProvider->id), 'Prestataire non vérifié depuis 45 jours supprimé');
        $this->assertNull(Provider::withTrashed()->find($providerId), 'Son établissement vide est supprimé aussi');

        $this->assertNotNull($recent->fresh(), 'Compte de moins de 30 jours conservé');
        $this->assertNotNull($verified->fresh(), 'Compte vérifié conservé');
        $this->assertNotNull($admin->fresh(), 'Administrateur jamais concerné');
        $this->assertNotNull($active->fresh(), 'Compte avec activité (portefeuille) conservé');

        // L'adresse est de nouveau libre pour une nouvelle inscription.
        $this->assertFalse(User::withTrashed()->where('email', 'ancien@exemple.ci')->exists());
    }
}
