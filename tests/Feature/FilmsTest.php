<?php

namespace Tests\Feature;

use App\Models\Film;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** « Cinéma d'Ivoire » : vitrine par catégorie, fiche film, clic « Regarder », proposition et validation. */
class FilmsTest extends TestCase
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

    private function film(array $attrs = []): Film
    {
        return Film::create($attrs + [
            'title' => 'Les Gardiens du Sacré', 'kind' => 'documentaire', 'genre' => 'Patrimoine & traditions',
            'director' => 'Awa Koné', 'year' => 2024, 'duration_minutes' => 92, 'country' => "Côte d'Ivoire",
            'synopsis' => 'Un voyage au cœur des forêts sacrées et de ceux qui les protègent.',
            'trailer_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'watch_url' => 'https://exemple.ci/regarder',
            'rights_confirmed' => true, 'status' => 'published', 'published_at' => now(),
        ]);
    }

    public function test_index_and_kind_pages_list_only_published_films_of_the_category(): void
    {
        $doc = $this->film();
        $this->film(['title' => 'Bois de Rose', 'kind' => 'fiction', 'genre' => 'Drame']);
        $this->film(['title' => 'Brouillon caché', 'status' => 'draft']);

        $this->get(route('films.index'))->assertOk()
            ->assertSee('Cinéma')->assertSee('Les Gardiens du Sacré')->assertSee('Bois de Rose')
            ->assertSee('Film documentaire')->assertSee('Film fiction')->assertDontSee('Brouillon caché');

        $this->get(route('films.kind', 'documentaires'))->assertOk()
            ->assertSee($doc->title)->assertDontSee('Bois de Rose');
        $this->get(route('films.kind', 'fictions'))->assertOk()->assertSee('Bois de Rose');
        $this->get('/cinema-d-ivoire/autres')->assertNotFound();

        $this->get(route('films.index', ['q' => 'Rose']))->assertOk()->assertSee('Bois de Rose')->assertDontSee('Les Gardiens du Sacré');
    }

    public function test_show_page_has_movie_structured_data_trailer_facade_and_counts_a_view_once(): void
    {
        $film = $this->film();

        $this->get(route('films.show', $film))->assertOk()
            ->assertSee('"@type":"Movie"', false)
            ->assertSee('youtube-nocookie.com/embed/dQw4w9WgXcQ', false)
            ->assertSee('PT92M', false)
            ->assertSee('1 h 32');
        $this->get(route('films.show', $film))->assertOk();
        $this->assertSame(1, $film->fresh()->views_count);

        $this->get(route('films.watch', $film))->assertRedirect('https://exemple.ci/regarder');
        $this->assertSame(1, $film->fresh()->clicks_count);

        $this->get(route('films.show', $this->film(['title' => 'En attente', 'status' => 'pending'])))->assertNotFound();
    }

    public function test_public_submission_is_stored_pending_and_requires_rights(): void
    {
        Storage::fake('public');
        $payload = [
            'title' => 'Abidjan la nuit', 'kind' => 'fiction', 'director' => 'Yao Kouassi',
            'synopsis' => str_repeat('Une histoire nocturne à Abidjan. ', 3),
            'submitter_name' => 'Studio Lagune', 'submitter_email' => 'contact@studio.ci',
        ];

        $this->post(route('films.submit.store'), $payload)->assertSessionHasErrors('rights_confirmed');
        $this->assertSame(0, Film::count());

        $this->post(route('films.submit.store'), $payload + ['rights_confirmed' => '1', 'poster' => UploadedFile::fake()->image('affiche.jpg', 600, 900), 'director_photo' => UploadedFile::fake()->image('yao.jpg', 400, 400)])
            ->assertRedirect(route('films.submit'));
        $film = Film::sole();
        $this->assertSame('pending', $film->status);
        $this->assertNotNull($film->poster_path);
        Storage::disk('public')->assertExists($film->director_photo_path);
        Storage::disk('public')->assertExists($film->poster_path);
        $this->get(route('films.index'))->assertDontSee('Abidjan la nuit');

        // Pot de miel rempli : refus.
        $this->post(route('films.submit.store'), $payload + ['rights_confirmed' => '1', 'website' => 'spam'])->assertSessionHasErrors('website');
    }

    public function test_admin_manages_films_and_cannot_publish_without_rights(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('admin.films.index'))->assertOk()->assertSee("Cinéma d'Ivoire", false);
        $this->actingAs($admin)->get(route('admin.films.create'))->assertOk();

        $base = ['title' => 'Akwaba', 'kind' => 'documentaire', 'status' => 'published'];
        $this->actingAs($admin)->post(route('admin.films.store'), $base)->assertSessionHasErrors('rights_confirmed');

        $this->actingAs($admin)->post(route('admin.films.store'), $base + [
            'rights_confirmed' => '1', 'is_featured' => '1',
            'screenings_text' => "2099-12-01 19:00 | Institut, Abidjan | https://exemple.ci/billets\nligne invalide",
            'poster' => UploadedFile::fake()->image('p.jpg', 600, 900),
            'director' => 'Awa Koné',
            'director_photo' => UploadedFile::fake()->image('awa.jpg', 600, 600),
        ])->assertRedirect(route('admin.films.index'));
        $film = Film::sole();
        Storage::disk('public')->assertExists($film->director_photo_path);
        $this->get(route('films.show', $film))->assertOk()->assertSee('Portrait de Awa Koné')->assertSee($film->directorPhotoUrl(), false);

        // Retrait de la photo : retour aux initiales.
        $photo = $film->director_photo_path;
        $this->actingAs($admin)->put(route('admin.films.update', $film), ['title' => 'Akwaba', 'kind' => 'documentaire', 'status' => 'published', 'rights_confirmed' => '1', 'director' => 'Awa Koné', 'is_featured' => '1', 'screenings_text' => '2099-12-01 19:00 | Institut, Abidjan | https://exemple.ci/billets', 'remove_director_photo' => '1'])->assertRedirect();
        $this->assertNull($film->fresh()->director_photo_path);
        Storage::disk('public')->assertMissing($photo);
        $film->refresh();
        $this->assertTrue($film->is_featured);
        $this->assertNotNull($film->published_at);
        $this->assertCount(1, $film->screenings);
        $this->assertSame('Institut, Abidjan', $film->screenings[0]['place']);

        $this->get(route('films.show', $film))->assertOk()->assertSee('Institut, Abidjan');

        // Retrait rapide depuis la liste, puis suppression.
        $this->actingAs($admin)->patch(route('admin.films.status', $film), ['status' => 'withdrawn'])->assertRedirect();
        $this->assertSame('withdrawn', $film->fresh()->status);
        $this->actingAs($admin)->delete(route('admin.films.destroy', $film))->assertRedirect();
        $this->assertSame(0, Film::count());
    }
}
