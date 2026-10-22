<?php

namespace Tests\Feature;

use App\Models\Comic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** « Les Astuces de Mian » : rubrique BD, liseuse, fenêtre de première visite, gestion admin. */
class ComicsMianTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Cache::flush();
    }

    private function comic(array $attrs = []): Comic
    {
        return Comic::create($attrs + [
            'title' => 'Mian et le kédjénou', 'category' => 'educatif', 'is_published' => true,
            'published_at' => now(), 'pages' => ['comics/1/p1.jpg', 'comics/1/p2.jpg'],
        ]);
    }

    public function test_index_and_reader_show_published_comics_only(): void
    {
        $pub = $this->comic();
        $draft = $this->comic(['title' => 'Brouillon secret', 'is_published' => false]);

        $this->get(route('comics.index'))->assertOk()->assertSee('Mian et le kédjénou')->assertDontSee('Brouillon secret');
        $this->get(route('comics.show', $pub))->assertOk()->assertSee('planche-2', false);
        $this->get(route('comics.show', $draft))->assertNotFound();
        $this->assertSame(1, $pub->fresh()->views_count);
        $this->get(route('comics.show', $pub));
        $this->assertSame(1, $pub->fresh()->views_count, 'une lecture par session');
    }

    public function test_index_filters_by_category(): void
    {
        $this->comic();
        $this->comic(['title' => 'Le jeu du marché', 'category' => 'ludique']);

        $this->get(route('comics.index', ['rubrique' => 'ludique']))->assertOk()->assertSee('Le jeu du marché')->assertDontSee('Mian et le kédjénou');
    }

    public function test_welcome_popup_appears_until_seen_and_never_on_comics_pages(): void
    {
        $this->get('/')->assertOk()->assertDontSee('id="mian-welcome"', false);

        $this->comic();
        Cache::flush();
        $this->get('/')->assertOk()->assertSee('id="mian-welcome"', false)->assertSee(route('comics.index'), false);
        $this->withUnencryptedCookie('tiv_mian_seen', '1')->get('/')->assertOk()->assertDontSee('id="mian-welcome"', false);
        $this->get(route('comics.index'))->assertDontSee('id="mian-welcome"', false);
    }

    public function test_menu_links_to_the_section(): void
    {
        $this->get('/')->assertOk()->assertSee(route('comics.index'), false)->assertSee('Les Astuces de Mian');
    }

    public function test_admin_creates_reorders_and_deletes_a_comic(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.comics.store'), [
            'title' => 'Mian prend le gbaka', 'category' => 'divertissant', 'is_published' => '1',
            'cover' => UploadedFile::fake()->image('cover.jpg', 600, 800),
            'new_pages' => [UploadedFile::fake()->image('a.jpg'), UploadedFile::fake()->image('b.png')],
        ])->assertRedirect(route('admin.comics.index'));

        $comic = Comic::firstWhere('title', 'Mian prend le gbaka');
        $this->assertTrue($comic->is_published);
        $this->assertNotNull($comic->published_at);
        $this->assertCount(2, $comic->pages);
        Storage::disk('public')->assertExists([$comic->cover_path, ...$comic->pages]);

        // Ordre inversé, première planche retirée, une ajoutée.
        [$first, $second] = $comic->pages;
        $this->actingAs($admin)->put(route('admin.comics.update', $comic), [
            'title' => 'Mian prend le gbaka', 'category' => 'divertissant', 'is_published' => '1',
            'pages_keep' => [$second], 'new_pages' => [UploadedFile::fake()->image('c.jpg')],
        ])->assertRedirect();
        $comic->refresh();
        $this->assertSame($second, $comic->pages[0]);
        $this->assertCount(2, $comic->pages);
        Storage::disk('public')->assertMissing($first);

        // Un chemin étranger ne peut pas être injecté.
        $this->actingAs($admin)->put(route('admin.comics.update', $comic), [
            'title' => 'X', 'category' => 'ludique', 'pages_keep' => ['../../.env'],
        ])->assertSessionHasErrors('pages_keep.0');

        $this->actingAs($admin)->delete(route('admin.comics.destroy', $comic))->assertRedirect();
        $this->assertDatabaseMissing('comics', ['id' => $comic->id]);
        Storage::disk('public')->assertMissing($second);
    }

    public function test_non_admin_cannot_manage_comics(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'visitor']))
            ->get(route('admin.comics.index'))->assertForbidden();
    }
}
