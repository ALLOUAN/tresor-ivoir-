<?php

namespace Tests\Feature;

use App\Models\Provider;
use App\Models\ProviderCategory;
use App\Models\User;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** Médiathèque — vidéos : liens YouTube/Vimeo, envoi direct, validation, j'aime, vues, clics. */
class VideoMediathequeTest extends TestCase
{
    use RefreshDatabase;

    private function provider(): array
    {
        $user = User::factory()->provider()->create();
        $cat = ProviderCategory::firstOrCreate(['slug' => 'restaurants'], ['name_fr' => 'Restaurants', 'name_en' => 'Restaurants', 'is_active' => true]);
        $provider = Provider::create(['user_id' => $user->id, 'category_id' => $cat->id, 'name' => 'Chez Tantie', 'slug' => 'chez-tantie-'.uniqid(), 'status' => 'active']);

        return [$user, $provider];
    }

    public function test_youtube_and_vimeo_links_are_recognised(): void
    {
        $this->assertSame(['youtube', 'dQw4w9WgXcQ'], Video::parseExternal('https://www.youtube.com/watch?v=dQw4w9WgXcQ&t=10'));
        $this->assertSame(['youtube', 'dQw4w9WgXcQ'], Video::parseExternal('https://youtu.be/dQw4w9WgXcQ'));
        $this->assertSame(['youtube', 'abcdefghijk'], Video::parseExternal('https://www.youtube.com/shorts/abcdefghijk'));
        $this->assertSame(['vimeo', '76979871'], Video::parseExternal('https://vimeo.com/76979871'));
        $this->assertNull(Video::parseExternal('https://example.com/video.mp4'));
    }

    public function test_admin_publishes_a_youtube_video_that_appears_in_the_mediatheque(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->post(route('admin.videos.store'), [
            'title' => 'Coucher de soleil à Assinie', 'category' => 'destinations', 'source_type' => 'link',
            'external_url' => 'https://youtu.be/dQw4w9WgXcQ', 'orientation' => 'landscape', 'publish' => 1, 'is_featured' => 1,
        ])->assertRedirect(route('admin.videos.index'));

        $video = Video::firstOrFail();
        $this->assertSame(Video::STATUS_PUBLISHED, $video->status);
        $this->assertSame('youtube', $video->source);
        $this->assertStringContainsString('i.ytimg.com/vi/dQw4w9WgXcQ', (string) $video->poster());

        auth()->logout();
        $this->get(route('videos.index'))->assertOk()->assertSee('Coucher de soleil à Assinie')->assertSee('Médiathèque');
        $this->get(route('videos.show', $video))->assertOk()->assertSee('youtube-nocookie.com', false);
    }

    public function test_provider_video_stays_hidden_until_the_admin_approves_it(): void
    {
        Storage::fake('public');
        [$user, $provider] = $this->provider();

        $this->actingAs($user)->post(route('provider.videos.store'), [
            'title' => 'Notre kedjenou en cuisine', 'category' => 'gastronomie', 'source_type' => 'upload',
            'video_file' => UploadedFile::fake()->create('clip.mp4', 800, 'video/mp4'),
            'orientation' => 'portrait', 'duration_sec' => 42,
        ])->assertRedirect(route('provider.videos.index'));

        $video = Video::firstOrFail();
        $this->assertSame(Video::STATUS_PENDING, $video->status);
        $this->assertSame($provider->id, $video->provider_id);
        Storage::disk('public')->assertExists($video->file_path);
        $this->get(route('videos.index'))->assertDontSee('Notre kedjenou en cuisine');
        $this->get(route('videos.show', $video))->assertNotFound();

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->patch(route('admin.videos.approve', $video))->assertRedirect();
        $this->assertSame(Video::STATUS_PUBLISHED, $video->fresh()->status);
        auth()->logout();
        $this->get(route('videos.index'))->assertSee('Notre kedjenou en cuisine')->assertSee('0:42');
    }

    public function test_uploads_reject_non_video_files_and_oversized_videos(): void
    {
        Storage::fake('public');
        [$user] = $this->provider();
        $base = ['title' => 'Test', 'category' => 'culture', 'source_type' => 'upload', 'orientation' => 'portrait'];

        $this->actingAs($user)->post(route('provider.videos.store'), $base + ['video_file' => UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf')])
            ->assertSessionHasErrors('video_file');
        $this->actingAs($user)->post(route('provider.videos.store'), $base + ['video_file' => UploadedFile::fake()->create('big.mp4', 60000, 'video/mp4')])
            ->assertSessionHasErrors('video_file');
        $this->actingAs($user)->post(route('provider.videos.store'), ['source_type' => 'link', 'external_url' => 'https://exemple.ci/x'] + $base)
            ->assertSessionHasErrors('external_url');
        $this->assertSame(0, Video::count());
    }

    public function test_likes_views_and_booking_clicks_are_counted(): void
    {
        $video = Video::create(['title' => 'Plage de Grand-Bassam', 'category' => 'destinations', 'source' => 'youtube', 'external_id' => 'dQw4w9WgXcQ', 'status' => Video::STATUS_PUBLISHED, 'published_at' => now()]);

        // Visiteur connecté (clé « user:id ») : un anonyme est reconnu par un cookie que le client de test ne renvoie pas.
        $this->actingAs(User::factory()->create(['role' => 'visitor']));
        $this->postJson(route('videos.like', $video))->assertOk()->assertJson(['liked' => true, 'likes' => 1]);
        $this->postJson(route('videos.like', $video))->assertOk()->assertJson(['liked' => false, 'likes' => 0]);

        $this->postJson(route('videos.view', $video))->assertOk()->assertJson(['views' => 1]);
        $this->postJson(route('videos.view', $video))->assertOk()->assertJson(['views' => 1]); // même session : une seule vue

        $this->postJson(route('videos.click', $video))->assertOk();
        $this->postJson(route('videos.share', $video))->assertOk();
        $this->assertSame(1, $video->fresh()->booking_clicks_count);
        $this->assertSame(1, $video->fresh()->shares_count);
    }

    public function test_access_rules(): void
    {
        [$user] = $this->provider();
        [, $other] = $this->provider();
        $foreign = Video::create(['title' => 'Autre', 'category' => 'culture', 'source' => 'youtube', 'external_id' => 'dQw4w9WgXcQ', 'provider_id' => $other->id]);

        $this->actingAs($user)->delete(route('provider.videos.destroy', $foreign))->assertForbidden();
        $this->actingAs($user)->get(route('admin.videos.index'))->assertForbidden();
        $this->assertNotNull($foreign->fresh());
    }
}
