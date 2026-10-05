<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminArticleActionsTest extends TestCase
{
    use RefreshDatabase;

    private function makeArticle(string $status = 'review'): Article
    {
        $author = User::factory()->create();
        $category = ArticleCategory::create(['slug' => 'cat-'.uniqid(), 'name_fr' => 'Cat', 'name_en' => 'Cat', 'is_active' => true]);

        return Article::create([
            'title_fr' => 'Article test',
            'slug_fr' => 'article-test-'.uniqid(),
            'category_id' => $category->id,
            'status' => $status,
            'author_id' => $author->id,
        ]);
    }

    public function test_admin_can_publish_an_article(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $article = $this->makeArticle('review');

        $this->actingAs($admin)->patch(route('admin.articles.publish', $article))
            ->assertRedirect();

        $fresh = $article->fresh();
        $this->assertSame('published', $fresh->status);
        $this->assertNotNull($fresh->published_at);
    }

    public function test_admin_can_reject_an_article_back_to_draft(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $article = $this->makeArticle('review');

        $this->actingAs($admin)->patch(route('admin.articles.reject', $article), ['reason' => 'Pas assez complet'])
            ->assertRedirect();

        $this->assertSame('draft', $article->fresh()->status);
    }

    public function test_admin_can_archive_a_published_article(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $article = $this->makeArticle('published');

        $this->actingAs($admin)->patch(route('admin.articles.archive', $article))
            ->assertRedirect();

        $this->assertSame('archived', $article->fresh()->status);
    }

    public function test_admin_can_delete_an_article(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $article = $this->makeArticle('draft');

        $this->actingAs($admin)->delete(route('admin.articles.destroy', $article))
            ->assertRedirect();

        $this->assertNull(Article::find($article->id));
    }

    public function test_admin_can_create_and_update_an_article_category(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.categories.articles.store'), [
            'name_fr' => 'Nouvelle rubrique',
            'name_en' => 'New category',
            'slug' => 'nouvelle-rubrique-test',
            'is_active' => '1',
        ])->assertRedirect();

        $category = ArticleCategory::where('slug', 'nouvelle-rubrique-test')->first();
        $this->assertNotNull($category);

        $this->actingAs($admin)->patch(route('admin.categories.articles.update', $category), [
            'name_fr' => 'Rubrique renommée',
            'is_active' => '1',
        ])->assertRedirect();

        $this->assertSame('Rubrique renommée', $category->fresh()->name_fr);
    }
}
