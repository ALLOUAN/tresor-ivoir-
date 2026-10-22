<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Le compte « Super Admin » ne doit jamais apparaître publiquement sur la page d'un article
 * (ligne d'auteur, carte auteur, contributeurs) ; les autres auteurs restent affichés.
 */
class ArticleSuperAdminHiddenTest extends TestCase
{
    use RefreshDatabase;

    private function publishedArticleBy(string $first, string $last): Article
    {
        $author = User::factory()->create(['role' => 'admin', 'first_name' => $first, 'last_name' => $last]);
        $category = ArticleCategory::create(['slug' => 'cat-'.uniqid(), 'name_fr' => 'Cat', 'name_en' => 'Cat', 'is_active' => true]);

        return Article::create([
            'title_fr' => 'Article de test',
            'slug_fr' => 'article-test-'.uniqid(),
            'category_id' => $category->id,
            'author_id' => $author->id,
            'status' => 'published',
            'published_at' => now()->subDay(),
            'content_fr' => '<p>Contenu public.</p>',
        ]);
    }

    public function test_super_admin_name_is_not_displayed(): void
    {
        $article = $this->publishedArticleBy('Super', 'Admin');

        $response = $this->get(route('articles.show', $article->slug_fr));

        $response->assertOk();
        $response->assertDontSee('Super Admin');
        $response->assertDontSee('Contributeur ·', false);
    }

    public function test_other_authors_are_still_displayed(): void
    {
        $article = $this->publishedArticleBy('Aminata', 'Koné');

        $this->get(route('articles.show', $article->slug_fr))->assertOk()->assertSee('Aminata Koné');
    }
}
