<?php

namespace Tests\Feature;

use App\Models\GalleryLike;
use App\Models\SiteMediaItem;
use App\Models\User;
use App\Services\GalleryLikeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Couvre le système de likes de la galerie publique : fonctionne sans compte
 * (identification par cookie), bascule aimer/ne plus aimer, compteur cohérent
 * avec les lignes réellement en base.
 */
class GalleryLikeTest extends TestCase
{
    use RefreshDatabase;

    private function makeMedia(): SiteMediaItem
    {
        $uploader = User::factory()->admin()->create();

        return SiteMediaItem::create([
            'type' => 'image',
            'mime_type' => 'image/jpeg',
            'original_name' => 'photo.jpg',
            'file_path' => 'site/media-library/photo.jpg',
            'url' => '/storage/site/media-library/photo.jpg',
            'section' => 'home_gallery',
            'is_active' => true,
            'title' => 'Photo test',
            'uploaded_by' => $uploader->id,
        ]);
    }

    public function test_guest_can_like_without_an_account(): void
    {
        $media = $this->makeMedia();

        $response = $this->postJson(route('gallery.like.toggle', $media->uuid));

        $response->assertOk();
        $response->assertJson(['liked' => true, 'likes_count' => 1]);
        $this->assertSame(1, GalleryLike::where('media_id', $media->id)->count());
        $this->assertSame(1, $media->fresh()->likes_count);
    }

    public function test_authenticated_user_liking_twice_toggles_off(): void
    {
        $media = $this->makeMedia();
        $visitor = User::factory()->create();

        // actingAs() reste stable entre les deux appels HTTP de ce test (contrairement
        // au cookie invité, qui n'est pas automatiquement reporté d'une requête de
        // test à l'autre) — vérifie ici la bascule pour un même compte.
        $this->actingAs($visitor)->postJson(route('gallery.like.toggle', $media->uuid))
            ->assertJson(['liked' => true, 'likes_count' => 1]);

        $response = $this->actingAs($visitor)->postJson(route('gallery.like.toggle', $media->uuid));

        $response->assertOk();
        $response->assertJson(['liked' => false, 'likes_count' => 0]);
        $this->assertSame(0, GalleryLike::where('media_id', $media->id)->count());
        $this->assertSame(0, $media->fresh()->likes_count);
    }

    public function test_authenticated_user_like_is_tied_to_their_account(): void
    {
        $media = $this->makeMedia();
        $visitor = User::factory()->create();

        $this->actingAs($visitor)->postJson(route('gallery.like.toggle', $media->uuid))
            ->assertJson(['liked' => true, 'likes_count' => 1]);

        $like = GalleryLike::where('media_id', $media->id)->first();
        $this->assertSame('user:'.$visitor->id, $like->liker_key);
    }

    public function test_two_different_identities_liking_results_in_count_of_two(): void
    {
        $media = $this->makeMedia();
        $service = app(GalleryLikeService::class);

        // Bascule directement au niveau service avec deux identités distinctes —
        // couvre le calcul du compteur sans dépendre de la persistance de cookies
        // entre requêtes HTTP de test (non garantie par le client de test).
        $service->toggle($media, 'guest:11111111-1111-1111-1111-111111111111');
        $service->toggle($media, 'guest:22222222-2222-2222-2222-222222222222');

        $this->assertSame(2, GalleryLike::where('media_id', $media->id)->count());
        $this->assertSame(2, $media->fresh()->likes_count);
    }

    public function test_liking_nonexistent_media_returns_404(): void
    {
        $response = $this->postJson(route('gallery.like.toggle', '00000000-0000-0000-0000-000000000000'));

        $response->assertNotFound();
    }
}
