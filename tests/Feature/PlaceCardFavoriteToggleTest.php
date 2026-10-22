<?php

namespace Tests\Feature;

use App\Models\Provider;
use App\Models\ProviderCategory;
use App\Models\User;
use App\Models\VisitorFavorite;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Cœur des cartes d'établissement : bascule instantanée réservée aux visiteurs. */
class PlaceCardFavoriteToggleTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_visitor_toggles_a_favorite_and_others_cannot(): void
    {
        $cat = ProviderCategory::firstOrCreate(['slug' => 'hotels'], ['name_fr' => 'Hôtels', 'name_en' => 'Hotels', 'is_active' => true]);
        $provider = Provider::create(['user_id' => User::factory()->provider()->create()->id, 'category_id' => $cat->id, 'name' => 'Pullman', 'slug' => 'pullman', 'status' => 'active']);
        $visitor = User::factory()->create(['role' => 'visitor', 'email_verified_at' => now()]);

        $this->actingAs($visitor)->postJson(route('visitor.favorites.toggle'), ['provider_id' => $provider->id])
            ->assertOk()->assertJson(['favorited' => true]);
        $this->assertSame(1, VisitorFavorite::where('user_id', $visitor->id)->count());

        $this->actingAs($visitor)->postJson(route('visitor.favorites.toggle'), ['provider_id' => $provider->id])
            ->assertOk()->assertJson(['favorited' => false]);
        $this->assertSame(0, VisitorFavorite::count());

        $this->actingAs($visitor)->postJson(route('visitor.favorites.toggle'), ['provider_id' => 999999])->assertStatus(422);
        $this->actingAs(User::factory()->provider()->create())->postJson(route('visitor.favorites.toggle'), ['provider_id' => $provider->id])->assertForbidden();
        \Illuminate\Support\Facades\Auth::logout();
        $this->postJson(route('visitor.favorites.toggle'), ['provider_id' => $provider->id])->assertUnauthorized();
    }
}
