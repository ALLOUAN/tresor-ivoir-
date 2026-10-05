<?php

namespace Tests\Feature;

use App\Models\CulturalPeople;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Couvre la gestion des visuels par peuple/ethnie (upload, plusieurs bannières,
 * suppression individuelle, rejet des fichiers non-image) — cf. cahier des charges
 * « Gestion des visuels — Patrimoine vivant · Cultures Ivoiriennes ».
 */
class CulturalPeopleImageManagementTest extends TestCase
{
    use RefreshDatabase;

    private function makePeople(): CulturalPeople
    {
        return CulturalPeople::create([
            'name' => 'Peuple Test',
            'slug' => 'peuple-test-'.uniqid(),
            'is_active' => true,
        ]);
    }

    public function test_admin_can_upload_multiple_banners_at_once(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();
        $people = $this->makePeople();

        $response = $this->actingAs($admin)->put(route('admin.cultural.peoples.update', $people), [
            'name' => $people->name,
            'is_active' => '1',
            'cover_images_file' => [
                UploadedFile::fake()->image('banniere-1.jpg', 800, 600),
                UploadedFile::fake()->image('banniere-2.jpg', 800, 600),
            ],
        ]);

        $response->assertRedirect();
        $people->refresh();
        $this->assertCount(2, $people->cover_images);
        foreach ($people->cover_images as $url) {
            Storage::disk('public')->assertExists(ltrim(str_replace('/storage/', '', $url), '/'));
        }
        // La première bannière fait office de visuel représentatif pour les cartes/listes.
        $this->assertSame($people->cover_images[0], $people->cover_image);
    }

    public function test_uploading_more_banners_adds_to_existing_ones(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();
        $people = $this->makePeople();

        $this->actingAs($admin)->put(route('admin.cultural.peoples.update', $people), [
            'name' => $people->name,
            'is_active' => '1',
            'cover_images_file' => [UploadedFile::fake()->image('banniere-1.jpg')],
        ]);

        $this->actingAs($admin)->put(route('admin.cultural.peoples.update', $people->fresh()), [
            'name' => $people->name,
            'is_active' => '1',
            'cover_images_file' => [UploadedFile::fake()->image('banniere-2.jpg')],
        ]);

        $this->assertCount(2, $people->fresh()->cover_images);
    }

    public function test_non_image_file_disguised_as_jpg_is_rejected(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();
        $people = $this->makePeople();

        $response = $this->actingAs($admin)->put(route('admin.cultural.peoples.update', $people), [
            'name' => $people->name,
            'is_active' => '1',
            // Extension .jpg déclarée mais contenu binaire arbitraire, non une vraie image.
            'cover_images_file' => [UploadedFile::fake()->create('malicious.jpg', 10)],
        ]);

        // Rejeté par ImageUploadSecurityService::assertSafeImage() (abort 422, contenu
        // réel non conforme malgré l'extension .jpg déclarée) — pas une erreur de
        // validation Laravel classique, donc pas de redirection avec session errors.
        $response->assertStatus(422);
        $this->assertEmpty($people->fresh()->cover_images ?? []);
    }

    public function test_removing_one_banner_keeps_the_others(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();
        $people = $this->makePeople();

        $this->actingAs($admin)->put(route('admin.cultural.peoples.update', $people), [
            'name' => $people->name,
            'is_active' => '1',
            'cover_images_file' => [
                UploadedFile::fake()->image('banniere-1.jpg'),
                UploadedFile::fake()->image('banniere-2.jpg'),
            ],
        ]);
        [$toRemove, $toKeep] = $people->fresh()->cover_images;
        $removedPath = ltrim(str_replace('/storage/', '', $toRemove), '/');

        $response = $this->actingAs($admin)->put(route('admin.cultural.peoples.update', $people->fresh()), [
            'name' => $people->name,
            'is_active' => '1',
            'remove_cover_images' => [$toRemove],
        ]);

        $response->assertRedirect();
        $remaining = $people->fresh()->cover_images;
        $this->assertSame([$toKeep], $remaining);
        Storage::disk('public')->assertMissing($removedPath);
    }

    public function test_deleting_a_people_removes_all_its_banner_files(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();
        $people = $this->makePeople();

        $this->actingAs($admin)->put(route('admin.cultural.peoples.update', $people), [
            'name' => $people->name,
            'is_active' => '1',
            'cover_images_file' => [
                UploadedFile::fake()->image('banniere-1.jpg'),
                UploadedFile::fake()->image('banniere-2.jpg'),
            ],
        ]);
        $paths = array_map(fn ($url) => ltrim(str_replace('/storage/', '', $url), '/'), $people->fresh()->cover_images);

        $this->actingAs($admin)->delete(route('admin.cultural.peoples.destroy', $people->fresh()));

        foreach ($paths as $path) {
            Storage::disk('public')->assertMissing($path);
        }
    }

    public function test_homepage_shows_first_cover_image_instead_of_fallback_icon(): void
    {
        Storage::fake('public');
        $withImage = CulturalPeople::create([
            'name' => 'Avec image',
            'slug' => 'avec-image-'.uniqid(),
            'is_active' => true,
            'cover_images' => ['/storage/cultural/peoples/cover_test.jpg'],
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee($withImage->cover_images[0], false);
    }

    public function test_homepage_falls_back_to_people_group_icon_when_no_image(): void
    {
        CulturalPeople::create([
            'name' => 'Sans image',
            'slug' => 'sans-image-'.uniqid(),
            'is_active' => true,
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('fa-people-group', false);
    }
}
