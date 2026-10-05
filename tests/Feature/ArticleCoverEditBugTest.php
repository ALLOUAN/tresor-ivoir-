<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleCoverEditBugTest extends TestCase
{
    use RefreshDatabase;

    public function test_editing_an_article_with_an_existing_relative_cover_url_does_not_fail_validation(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = ArticleCategory::create(['slug' => 'cat-test', 'name_fr' => 'Cat', 'name_en' => 'Cat', 'is_active' => true]);

        $article = Article::create([
            'title_fr' => 'Titre test',
            'slug_fr' => 'titre-test',
            'category_id' => $category->id,
            'cover_url' => '/storage/articles/covers/exemple.webp',
            'status' => 'draft',
            'author_id' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->put(route('editor.articles.update', $article), [
            'title_fr' => 'Titre test modifié',
            'slug_fr' => 'titre-test',
            'category_id' => $category->id,
            'cover_url' => $article->cover_url, // valeur pré-remplie par le formulaire, non modifiée
            'content_fr' => '', // présent dans le vrai formulaire même vide
            'status' => 'draft',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('editor.articles.index'));
        $this->assertSame('Titre test modifié', $article->fresh()->title_fr);
        $this->assertSame($article->cover_url, $article->fresh()->cover_url);
    }

    public function test_replacing_an_existing_cover_image_with_a_new_upload_works(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);
        $category = ArticleCategory::create(['slug' => 'cat-test-2', 'name_fr' => 'Cat', 'name_en' => 'Cat', 'is_active' => true]);

        $article = Article::create([
            'title_fr' => 'Titre test 2',
            'slug_fr' => 'titre-test-2',
            'category_id' => $category->id,
            'cover_url' => '/storage/articles/covers/ancienne.webp',
            'status' => 'draft',
            'author_id' => $admin->id,
        ]);

        $newCover = \Illuminate\Http\UploadedFile::fake()->image('nouvelle-couverture.jpg', 800, 600);

        $response = $this->actingAs($admin)->put(route('editor.articles.update', $article), [
            'title_fr' => $article->title_fr,
            'slug_fr' => $article->slug_fr,
            'category_id' => $category->id,
            'cover_url' => $article->cover_url, // toujours pré-rempli, même en remplaçant par un upload
            'cover_image' => $newCover,
            'content_fr' => '',
            'status' => 'draft',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('editor.articles.index'));
        $fresh = $article->fresh();
        $this->assertNotSame('/storage/articles/covers/ancienne.webp', $fresh->cover_url);
        $this->assertStringStartsWith('/storage/articles/covers/', $fresh->cover_url);
    }
}
