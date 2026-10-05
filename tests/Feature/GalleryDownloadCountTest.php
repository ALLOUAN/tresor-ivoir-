<?php

namespace Tests\Feature;

use App\Models\SiteMediaItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Couvre le compteur de téléchargements affiché sur chaque visuel de la
 * galerie — contrairement aux likes, chaque appel doit incrémenter (pas de
 * déduplication par visiteur : un même visiteur peut télécharger plusieurs fois).
 */
class GalleryDownloadCountTest extends TestCase
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

    public function test_tracking_a_download_increments_the_counter(): void
    {
        $media = $this->makeMedia();

        $response = $this->postJson(route('gallery.download.track', $media->uuid));

        $response->assertOk();
        $response->assertJson(['downloads_count' => 1]);
        $this->assertSame(1, $media->fresh()->downloads_count);
    }

    public function test_repeated_downloads_by_the_same_visitor_all_count(): void
    {
        $media = $this->makeMedia();

        $this->postJson(route('gallery.download.track', $media->uuid));
        $this->postJson(route('gallery.download.track', $media->uuid));
        $response = $this->postJson(route('gallery.download.track', $media->uuid));

        $response->assertJson(['downloads_count' => 3]);
        $this->assertSame(3, $media->fresh()->downloads_count);
    }

    public function test_tracking_download_for_nonexistent_media_returns_404(): void
    {
        $response = $this->postJson(route('gallery.download.track', '00000000-0000-0000-0000-000000000000'));

        $response->assertNotFound();
    }
}
