<?php

namespace Tests\Feature;

use App\Models\HomePromoBanner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** Bannières publicitaires de l'accueil : gestion admin, plusieurs visuels qui défilent, affichage. */
class HomePromoBannerTest extends TestCase
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

    public function test_default_banners_are_shown_on_the_homepage(): void
    {
        $this->assertSame(2, HomePromoBanner::count(), 'Deux bannières par défaut (migration)');
        $this->get(route('home'))->assertOk()
            ->assertSee('class="tpb"', false)
            ->assertSee('Référencez')
            ->assertSee("Cinéma d'Ivoire");
    }

    public function test_admin_uploads_several_images_that_slide_and_can_remove_one(): void
    {
        Storage::fake('public');
        $admin = $this->admin();
        $banner = HomePromoBanner::where('placement', 'after_annuaire')->firstOrFail();
        $payload = [
            'placement' => 'after_annuaire', 'title_strong' => 'Séjour', 'title_rest' => 'à Assinie',
            'accent' => 'Des prix imbattables', 'cta_label' => 'Réserver', 'link_url' => '/residences-hotels', 'is_active' => '1',
        ];

        $this->actingAs($admin)->put(route('admin.home-banners.update', $banner), $payload + [
            'images_new' => [UploadedFile::fake()->image('a.png', 800, 500), UploadedFile::fake()->image('b.png', 800, 500), UploadedFile::fake()->image('c.jpg', 800, 500)],
        ])->assertRedirect(route('admin.home-banners.index'));

        $images = $banner->fresh()->imageList();
        $this->assertCount(3, $images);
        $this->assertSame($images[0], $banner->fresh()->image_url);
        Storage::disk('public')->assertExists(ltrim(substr($images[1], strlen('/storage/')), '/'));

        Cache::flush();
        $html = $this->get(route('home'))->assertOk()->getContent();
        $this->assertStringContainsString('data-tpb-slides', $html);
        preg_match('#<span class="tpb__dots">(.*?)</span>#s', $html, $m);
        $this->assertSame(3, substr_count($m[1] ?? '', '<i'), 'Un point par visuel');

        // Retrait du 2e visuel : fichier supprimé, l'ordre des autres est conservé.
        $this->actingAs($admin)->put(route('admin.home-banners.update', $banner), $payload + ['images_remove' => [$images[1]]])->assertRedirect();
        $this->assertSame([$images[0], $images[2]], $banner->fresh()->imageList());
        Storage::disk('public')->assertMissing(ltrim(substr($images[1], strlen('/storage/')), '/'));
    }

    public function test_admin_pages_render_and_inactive_banner_is_hidden(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->get(route('admin.home-banners.index'))->assertOk()->assertSee('Bannières publicitaires');
        $this->actingAs($admin)->get(route('admin.home-banners.create'))->assertOk();

        HomePromoBanner::query()->update(['is_active' => false]);
        Cache::flush();
        $this->get(route('home'))->assertOk()->assertDontSee('class="tpb"', false);
    }
}
