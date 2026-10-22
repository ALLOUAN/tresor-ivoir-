<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Partage d'un article : la page annonce sa photo de couverture aux réseaux sociaux (Open Graph / Twitter),
 * et l'image annoncée est bien servie en JPEG 1200 × 630.
 */
class ArticleShareImageTest extends TestCase
{
    use RefreshDatabase;

    public function test_article_page_exposes_its_cover_as_share_image(): void
    {
        Storage::fake('public');
        $img = imagecreatetruecolor(1600, 900);
        imagefill($img, 0, 0, imagecolorallocate($img, 242, 121, 15));
        ob_start();
        imagepng($img);
        Storage::disk('public')->put('articles/covers/couverture.png', ob_get_clean());

        $author = User::factory()->create(['role' => 'admin']);
        $category = ArticleCategory::create(['slug' => 'cat-'.uniqid(), 'name_fr' => 'Cat', 'name_en' => 'Cat', 'is_active' => true]);
        $article = Article::create([
            'title_fr' => 'Article partagé',
            'slug_fr' => 'article-partage',
            'category_id' => $category->id,
            'author_id' => $author->id,
            'status' => 'published',
            'published_at' => now()->subDay(),
            'content_fr' => '<p>Contenu.</p>',
            'cover_url' => '/storage/articles/covers/couverture.png',
        ]);

        $html = $this->get(route('articles.show', $article->slug_fr))->assertOk()->getContent();

        $this->assertMatchesRegularExpression('#<meta property="og:image" content="https?://[^"]+/img/og/articles/covers/couverture\.png\.jpg#', $html);
        $this->assertStringContainsString('<meta name="twitter:card" content="summary_large_image">', $html);
        $this->assertStringContainsString('<meta property="og:title" content="Article partagé">', $html);

        $response = $this->get('/img/og/articles/covers/couverture.png.jpg');
        $response->assertOk();
        $this->assertSame('image/jpeg', $response->headers->get('Content-Type'));
        $size = getimagesize($response->baseResponse->getFile()->getPathname());
        $this->assertSame([1200, 630], [$size[0], $size[1]]);
    }
}
