<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

/** Photos d'un article : téléchargement une à une et toutes ensemble (ZIP), seulement pour un article publié. */
class ArticlePhotoDownloadTest extends TestCase
{
    use RefreshDatabase;

    private Article $article;

    private Media $photo;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Storage::disk('public')->put('articles/covers/une.webp', 'COUV');
        Storage::disk('public')->put('articles/gallery/a1.webp', 'IMG-A');
        Storage::disk('public')->put('articles/gallery/a2.webp', 'IMG-B');

        $author = User::factory()->create(['role' => 'admin']);
        $category = ArticleCategory::create(['slug' => 'cat-'.uniqid(), 'name_fr' => 'Cat', 'name_en' => 'Cat', 'is_active' => true]);
        $this->article = Article::create([
            'title_fr' => 'Grand-Bassam en images', 'slug_fr' => 'grand-bassam-en-images', 'category_id' => $category->id,
            'author_id' => $author->id, 'status' => 'published', 'published_at' => now()->subDay(),
            'content_fr' => '<p>Contenu.</p>', 'cover_url' => '/storage/articles/covers/une.webp',
        ]);
        $make = fn (string $file, string $name, int $order) => $this->article->media()->create([
            'type' => 'image', 'collection' => 'gallery', 'file_path' => 'articles/gallery/'.$file,
            'url' => '/storage/articles/gallery/'.$file, 'original_name' => $name, 'mime_type' => 'image/webp', 'uploaded_by' => $this->article->author_id ?? 1, 'size_bytes' => 5, 'sort_order' => $order,
        ]);
        $this->photo = $make('a1.webp', 'ABIDJAN.png', 1);
        $make('a2.webp', 'Abidjan.jpg', 2);
        // Image externe : non proposée (aucun fichier sur le site).
        $this->article->media()->create(['type' => 'image', 'collection' => 'gallery', 'file_path' => '', 'url' => 'https://exemple.com/x.jpg', 'original_name' => 'x.jpg', 'mime_type' => 'image/jpeg', 'uploaded_by' => $this->article->author_id ?? 1, 'size_bytes' => 0, 'sort_order' => 3]);
    }

    public function test_the_page_offers_each_photo_and_a_zip_of_all_of_them(): void
    {
        $this->get(route('articles.show', 'grand-bassam-en-images'))->assertOk()
            ->assertSee('Télécharger les 3 photos')
            ->assertSee(route('articles.photos.zip', 'grand-bassam-en-images'))
            ->assertSee(route('articles.photos.download', ['grand-bassam-en-images', $this->photo->id]));

        $one = $this->get(route('articles.photos.download', ['grand-bassam-en-images', $this->photo->id]));
        $one->assertOk()->assertDownload('abidjan.webp');

        $zip = $this->get(route('articles.photos.zip', 'grand-bassam-en-images'));
        $zip->assertOk()->assertDownload('grand-bassam-en-images-photos.zip');
        $z = new ZipArchive;
        $this->assertTrue($z->open($zip->baseResponse->getFile()->getPathname()));
        $names = [];
        for ($i = 0; $i < $z->numFiles; $i++) {
            $names[] = $z->getNameIndex($i);
        }
        $this->assertSame(['grand-bassam-en-images-couverture.webp', 'abidjan.webp', 'abidjan-2.webp'], $names);
        $this->assertSame('IMG-B', $z->getFromName('abidjan-2.webp'));
        $z->close();
    }

    public function test_foreign_or_unpublished_photos_are_refused(): void
    {
        $other = Article::create([
            'title_fr' => 'Brouillon', 'slug_fr' => 'brouillon', 'category_id' => $this->article->category_id,
            'author_id' => $this->article->author_id, 'status' => 'draft', 'content_fr' => '<p>x</p>',
        ]);
        $draftPhoto = $other->media()->create(['type' => 'image', 'collection' => 'gallery', 'file_path' => 'articles/gallery/a2.webp', 'url' => '/storage/articles/gallery/a2.webp', 'original_name' => 'b.webp', 'mime_type' => 'image/webp', 'uploaded_by' => $this->article->author_id ?? 1, 'size_bytes' => 5]);

        $this->get(route('articles.photos.zip', 'brouillon'))->assertNotFound();
        $this->get(route('articles.photos.download', ['brouillon', $draftPhoto->id]))->assertNotFound();
        // Photo d'un autre article demandée via cet article : refusée.
        $this->get(route('articles.photos.download', ['grand-bassam-en-images', $draftPhoto->id]))->assertNotFound();
    }
}
