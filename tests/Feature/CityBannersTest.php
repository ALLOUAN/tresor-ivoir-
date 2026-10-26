<?php

namespace Tests\Feature;

use App\Models\TouristCity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** Bannières multiples d'une ville : ajout (fichiers + URL), retrait, et diaporama sur la page ville. */
class CityBannersTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'first_name' => 'Super', 'last_name' => 'Admin', 'email' => 'admin@exemple.ci',
            'password_hash' => Hash::make('MotDePasse123!'), 'role' => 'admin', 'is_active' => true,
            'is_verified' => true, 'email_verified_at' => now(),
        ]);
    }

    public function test_admin_adds_and_removes_banners_and_city_page_shows_a_slider(): void
    {
        Storage::fake('public');
        $city = TouristCity::create(['name' => 'Abidjan', 'slug' => 'abidjan', 'is_active' => true, 'cover_image' => 'https://exemple.ci/principale.jpg']);
        $admin = $this->admin();
        $payload = ['name' => 'Abidjan', 'is_active' => '1'];

        // Ajout : deux fichiers + une URL.
        $this->actingAs($admin)->put(route('admin.tourist.cities.update', $city), $payload + [
            'banner_files' => [UploadedFile::fake()->image('a.jpg'), UploadedFile::fake()->image('b.png')],
            'banner_urls' => "https://exemple.ci/plateau.jpg\nnot-a-url",
        ])->assertRedirect();

        $banners = $city->fresh()->banners;
        $this->assertCount(3, $banners, 'Deux fichiers et une URL valide (la ligne invalide est ignorée)');
        $this->assertSame('https://exemple.ci/plateau.jpg', $banners[2]);
        Storage::disk('public')->assertExists(ltrim(substr($banners[0], strlen('/storage/')), '/'));

        // La page ville affiche un diaporama : bannière principale + 3 bannières.
        $this->get(route('tourist.city', 'abidjan'))->assertOk()
            ->assertSee('data-tc-slider', false)
            ->assertSee('principale.jpg', false)
            ->assertSee('plateau.jpg', false);
        $this->assertCount(4, $city->fresh()->bannerList());

        // Retrait du premier fichier : il n'est plus listé et il est supprimé du disque.
        $this->actingAs($admin)->put(route('admin.tourist.cities.update', $city), $payload + [
            'banners_keep' => [$banners[1], $banners[2], '/storage/autre-ville.jpg'],
        ])->assertRedirect();

        $after = $city->fresh()->banners;
        $this->assertSame([$banners[1], $banners[2]], $after, 'Seules les bannières existantes sont conservées');
        Storage::disk('public')->assertMissing(ltrim(substr($banners[0], strlen('/storage/')), '/'));
    }

    public function test_single_banner_keeps_the_simple_header(): void
    {
        TouristCity::create(['name' => 'Bouaké', 'slug' => 'bouake', 'is_active' => true, 'cover_image' => 'https://exemple.ci/bouake.jpg']);
        $this->get(route('tourist.city', 'bouake'))->assertOk()->assertDontSee('class="tc-slides"', false)->assertSee('bouake.jpg', false);
    }
}
