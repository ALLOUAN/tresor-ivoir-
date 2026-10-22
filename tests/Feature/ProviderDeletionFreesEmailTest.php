<?php

namespace Tests\Feature;

use App\Models\Provider;
use App\Models\ProviderCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/** Supprimer un prestataire dans le back-office libère son adresse e-mail pour une nouvelle inscription. */
class ProviderDeletionFreesEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_deleted_provider_can_register_again_with_same_email(): void
    {
        Notification::fake();
        $admin = User::factory()->create(['role' => 'admin']);
        $owner = User::factory()->provider()->create(['email' => 'jean@exemple.ci']);
        $cat = ProviderCategory::firstOrCreate(['slug' => 'restaurants'], ['name_fr' => 'Restaurants', 'name_en' => 'Restaurants', 'is_active' => true]);
        $provider = Provider::create(['user_id' => $owner->id, 'category_id' => $cat->id, 'name' => 'Chez Jean', 'slug' => 'chez-jean', 'status' => 'active']);

        $this->actingAs($admin)->delete(route('admin.providers.destroy', $provider))->assertRedirect();
        $this->assertSoftDeleted('users', ['id' => $owner->id]);
        auth()->logout();

        $this->post(route('register.post'), [
            'first_name' => 'Jean', 'last_name' => 'Allou', 'email' => 'jean@exemple.ci',
            'password' => 'MotDePasse123!', 'password_confirmation' => 'MotDePasse123!',
            'role' => 'visitor', 'terms' => '1',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $this->assertDatabaseHas('users', ['email' => 'jean@exemple.ci', 'role' => 'visitor', 'deleted_at' => null]);
    }

    public function test_admin_owner_is_only_deactivated(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $cat = ProviderCategory::firstOrCreate(['slug' => 'restaurants'], ['name_fr' => 'Restaurants', 'name_en' => 'Restaurants', 'is_active' => true]);
        $provider = Provider::create(['user_id' => $admin->id, 'category_id' => $cat->id, 'name' => 'Fiche admin', 'slug' => 'fiche-admin', 'status' => 'active']);

        $this->actingAs($admin)->delete(route('admin.providers.destroy', $provider))->assertRedirect();
        $this->assertNotSoftDeleted('users', ['id' => $admin->id]);
    }
}
