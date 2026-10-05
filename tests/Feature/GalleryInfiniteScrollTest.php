<?php

namespace Tests\Feature;

use App\Models\SiteMediaItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Couvre le contrat AJAX du défilement infini de la galerie publique (à la
 * Pinterest) : pagination réelle, fragment HTML renvoyé pour les pages
 * suivantes, indicateur has_more correct.
 */
class GalleryInfiniteScrollTest extends TestCase
{
    use RefreshDatabase;

    private function makeGalleryItems(int $count): void
    {
        $uploader = User::factory()->admin()->create();

        for ($i = 0; $i < $count; $i++) {
            SiteMediaItem::create([
                'type' => 'image',
                'mime_type' => 'image/jpeg',
                'original_name' => "photo-$i.jpg",
                'file_path' => "site/media-library/photo-$i.jpg",
                'url' => "/storage/site/media-library/photo-$i.jpg",
                'section' => 'home_gallery',
                'is_active' => true,
                'title' => "Photo $i",
                'uploaded_by' => $uploader->id,
            ]);
        }
    }

    public function test_first_page_shows_24_items_and_reports_more_pages(): void
    {
        $this->makeGalleryItems(30);

        $response = $this->get(route('gallery.public'));

        $response->assertStatus(200);
        $response->assertViewHas('galleryImages', fn ($paginator) => $paginator->count() === 24 && $paginator->total() === 30 && $paginator->hasMorePages());
    }

    public function test_ajax_second_page_returns_remaining_cards_as_html_fragment(): void
    {
        $this->makeGalleryItems(30);

        $response = $this->get(route('gallery.public', ['page' => 2]), [
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['has_more' => false, 'next_page' => 3]);
        $json = $response->json();
        $this->assertSame(6, substr_count($json['html'], 'pin-card'));
        $this->assertStringContainsString('fa-download', $json['html']);
    }

    public function test_ajax_page_beyond_available_items_returns_empty_html_and_no_more(): void
    {
        $this->makeGalleryItems(5);

        $response = $this->get(route('gallery.public', ['page' => 2]), [
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['html' => '', 'has_more' => false]);
    }
}
