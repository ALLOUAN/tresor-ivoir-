<?php

namespace Tests\Feature;

use App\Models\RecoSectionImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** Image de fond de la section « Recommandé pour vous » (Apparence → Recommandations). */
class RecoSectionImageTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_uploads_toggles_and_removes_the_background(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('admin.administration.reco-image'))->assertOk()
            ->assertSee('Recommandé pour vous');

        $this->actingAs($admin)->put(route('admin.administration.reco-image.update'), [
            'image_file' => UploadedFile::fake()->image('fond.jpg', 1920, 800),
            'is_active' => 1,
        ])->assertRedirect();

        $image = RecoSectionImage::singleton();
        $this->assertStringStartsWith('/storage/reco-section/reco_', (string) $image->image_url);
        $this->assertTrue($image->isVisible());
        Storage::disk('public')->assertExists(substr($image->image_url, strlen('/storage/')));

        $this->actingAs($admin)->patch(route('admin.administration.reco-image.toggle'))->assertRedirect();
        $this->assertFalse(RecoSectionImage::singleton()->isVisible());

        $path = substr($image->image_url, strlen('/storage/'));
        $this->actingAs($admin)->delete(route('admin.administration.reco-image.destroy'))->assertRedirect();
        $this->assertNull(RecoSectionImage::singleton()->image_url);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_visitor_cannot_open_the_admin_page(): void
    {
        $visitor = User::factory()->create(['role' => 'visitor']);

        $this->actingAs($visitor)->get(route('admin.administration.reco-image'))->assertStatus(403);
    }
}
