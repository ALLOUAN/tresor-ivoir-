<?php

namespace Tests\Feature;

use App\Models\Artwork;
use App\Models\ArtworkCategory;
use App\Models\Provider;
use App\Models\ProviderCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomepageArtCreationsSectionTest extends TestCase
{
    use RefreshDatabase;

    private function makeArtwork(array $overrides = []): Artwork
    {
        $user = User::factory()->provider()->create();
        $providerCategory = ProviderCategory::create([
            'name_fr' => 'Art',
            'name_en' => 'Art',
            'slug' => 'art-'.uniqid(),
            'is_active' => true,
        ]);
        $provider = Provider::create([
            'user_id' => $user->id,
            'category_id' => $providerCategory->id,
            'name' => 'Atelier Kouassi',
            'slug' => 'atelier-kouassi-'.uniqid(),
            'status' => 'active',
        ]);
        $artCategory = ArtworkCategory::create([
            'name_fr' => 'Peinture',
            'name_en' => 'Painting',
            'slug' => 'peinture-'.uniqid(),
            'is_active' => true,
            'sort_order' => 1,
        ]);

        return Artwork::create(array_merge([
            'provider_id' => $provider->id,
            'category_id' => $artCategory->id,
            'title' => 'Masque Baoulé',
            'slug' => 'masque-baoule-'.uniqid(),
            'description' => 'Une oeuvre unique.',
            'images' => ['https://example.test/masque.jpg'],
            'price_xof' => 75000,
            'stock_quantity' => 1,
            'status' => Artwork::STATUS_PUBLISHED,
            'is_featured' => true,
        ], $overrides));
    }

    public function test_homepage_shows_published_artwork_with_essential_info(): void
    {
        $artwork = $this->makeArtwork();

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('id="art-creations"', false);
        $response->assertSee('Art &amp; Créations', false);
        $response->assertSee($artwork->title);
        $response->assertSee('Atelier Kouassi');
        $response->assertSee('Peinture');
        $response->assertSee('75 000 XOF');
        $response->assertSee(route('art.show', $artwork->slug), false);
        $response->assertSee(route('art.index'), false);
    }

    public function test_homepage_hides_draft_artworks(): void
    {
        $this->makeArtwork(['status' => Artwork::STATUS_DRAFT, 'title' => 'Brouillon secret']);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertDontSee('Brouillon secret');
    }

    public function test_homepage_renders_empty_state_when_no_artworks(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Aucune œuvre disponible pour le moment.');
    }
}
