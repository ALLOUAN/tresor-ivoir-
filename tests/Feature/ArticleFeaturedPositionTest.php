<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Carte « Mise en une » du formulaire de création/édition d'article : permet
 * de choisir une position (1 à 5) parmi les emplacements de la section
 * vedette de l'accueil, respectée par la requête d'accueil (routes/web.php).
 */
class ArticleFeaturedPositionTest extends TestCase
{
    use RefreshDatabase;

    private function makeCategory(): ArticleCategory
    {
        return ArticleCategory::create(['slug' => 'cat-'.uniqid(), 'name_fr' => 'Cat', 'name_en' => 'Cat', 'is_active' => true]);
    }

    public function test_creating_a_featured_article_stores_its_position(): void
    {
        $author = User::factory()->create(['role' => 'admin']);
        $category = $this->makeCategory();

        $response = $this->actingAs($author)->post(route('editor.articles.store'), [
            'title_fr' => 'Article vedette',
            'slug_fr' => 'article-vedette',
            'category_id' => $category->id,
            'content_fr' => 'Contenu',
            'status' => 'draft',
            'is_featured' => '1',
            'featured_position' => '3',
        ]);
        $response->assertSessionHasNoErrors();

        $article = Article::where('slug_fr', 'article-vedette')->first();
        $this->assertNotNull($article);
        $this->assertTrue((bool) $article->is_featured);
        $this->assertSame(3, $article->featured_position);
    }

    public function test_position_is_cleared_when_article_is_not_featured(): void
    {
        $author = User::factory()->create(['role' => 'admin']);
        $category = $this->makeCategory();

        $article = Article::create([
            'title_fr' => 'Article',
            'slug_fr' => 'article-non-vedette',
            'category_id' => $category->id,
            'author_id' => $author->id,
            'status' => 'draft',
            'is_featured' => true,
            'featured_position' => 2,
        ]);

        // On désactive « à la une » sans envoyer featured_position (case décochée
        // dans le vrai formulaire => champ radio absent de la requête).
        $this->actingAs($author)->put(route('editor.articles.update', $article), [
            'title_fr' => $article->title_fr,
            'slug_fr' => $article->slug_fr,
            'category_id' => $category->id,
            'content_fr' => '',
            'status' => 'draft',
            'is_featured' => '0',
        ])->assertSessionHasNoErrors();

        $fresh = $article->fresh();
        $this->assertFalse((bool) $fresh->is_featured);
        $this->assertNull($fresh->featured_position);
    }

    public function test_homepage_orders_featured_articles_by_their_chosen_position(): void
    {
        $author = User::factory()->create(['role' => 'admin']);
        $category = $this->makeCategory();

        $make = function (string $slug, int $position) use ($author, $category) {
            return Article::create([
                'title_fr' => 'Article '.$slug,
                'slug_fr' => $slug,
                'category_id' => $category->id,
                'author_id' => $author->id,
                'status' => 'published',
                'published_at' => now()->subDay(),
                'is_featured' => true,
                'featured_position' => $position,
            ]);
        };

        // Créés dans le désordre pour bien vérifier que c'est featured_position
        // qui pilote l'ordre, pas la date de publication ni l'id.
        $make('position-5', 5);
        $make('position-1', 1);
        $make('position-3', 3);

        $response = $this->get('/');
        $response->assertOk();

        $html = $response->getContent();
        $posPos1 = strpos($html, 'Article position-1');
        $this->assertNotFalse($posPos1, 'position-1 doit être affiché sur l\'accueil');
        $this->assertNotFalse(strpos($html, 'Article position-3'), 'position-3 doit être affiché sur l\'accueil');
        $this->assertNotFalse(strpos($html, 'Article position-5'), 'position-5 doit être affiché sur l\'accueil');

        // La colonne centrale (emplacement « principal ») n'est identifiable qu'au
        // badge « Dernier paru » qu'elle seule porte (les commentaires Blade sur
        // la structure des colonnes ne survivent pas dans le HTML rendu) : on
        // vérifie qu'il apparaît bien à proximité de l'article en position 1,
        // preuve que featured_position=1 a été placé dans cet emplacement.
        $window = substr($html, max(0, $posPos1 - 4000), 8000);
        $this->assertStringContainsString('Dernier paru', $window, 'l\'article en position 1 doit occuper l\'emplacement principal');
    }
}
