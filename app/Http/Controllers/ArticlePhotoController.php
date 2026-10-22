<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Media;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

/** Téléchargement des photos d'un article publié : une à une, ou toutes ensemble (ZIP). */
class ArticlePhotoController extends Controller
{
    private function article(string $slug): Article
    {
        return Article::where('slug_fr', $slug)->where('status', 'published')->with('media')->firstOrFail();
    }

    /**
     * Photos téléchargeables : la couverture puis la galerie, avec leur fichier local et un nom lisible.
     *
     * @return list<array{path: string, name: string, media_id: ?int}>
     */
    public static function photos(Article $article): array
    {
        $items = [];
        $base = Str::slug($article->title_fr) ?: 'article';
        $used = [];
        $add = function (?string $url, ?string $filePath, ?string $label, ?int $mediaId) use (&$items, &$used, $base) {
            $path = self::localPath($url, $filePath);
            if (! $path) {
                return;
            }
            $stem = Str::slug(pathinfo((string) $label, PATHINFO_FILENAME)) ?: $base.'-photo';
            $name = $stem;
            for ($i = 2; isset($used[$name]); $i++) {
                $name = $stem.'-'.$i;
            }
            $used[$name] = true;
            $items[] = ['path' => $path, 'name' => $name.'.'.strtolower(pathinfo($path, PATHINFO_EXTENSION)), 'media_id' => $mediaId];
        };

        $add($article->cover_url, null, $base.'-couverture', null);
        foreach ($article->media->where('type', 'image')->sortBy('sort_order') as $m) {
            if ($m->url && $m->url !== $article->cover_url) {
                $add($m->url, $m->file_path, $m->original_name, $m->id);
            }
        }

        return $items;
    }

    /** Fichier sur le disque public correspondant à l'image (les images externes ne sont pas proposées). */
    private static function localPath(?string $url, ?string $filePath): ?string
    {
        $relative = $filePath ?: (is_string($url) && preg_match('#^(?:https?://[^/]+)?/storage/(.+?)(?:\?.*)?$#', $url, $m) ? rawurldecode($m[1]) : null);
        if (! $relative || str_contains($relative, '..')) {
            return null;
        }
        $disk = Storage::disk('public');

        return $disk->exists($relative) ? $disk->path($relative) : null;
    }

    public function download(string $slug, Media $media): BinaryFileResponse
    {
        $article = $this->article($slug);
        $photo = collect(self::photos($article))->firstWhere('media_id', $media->id);
        abort_unless($photo, 404);

        return response()->download($photo['path'], $photo['name']);
    }

    public function cover(string $slug): BinaryFileResponse
    {
        $photo = collect(self::photos($this->article($slug)))->firstWhere('media_id', null);
        abort_unless($photo, 404);

        return response()->download($photo['path'], $photo['name']);
    }

    public function zip(string $slug): BinaryFileResponse
    {
        $article = $this->article($slug);
        $photos = self::photos($article);
        abort_if($photos === [], 404);

        $tmp = tempnam(sys_get_temp_dir(), 'photos');
        $zip = new ZipArchive;
        abort_unless($zip->open($tmp, ZipArchive::OVERWRITE) === true, 500);
        foreach ($photos as $p) {
            // Les images sont déjà compressées : on les range sans recompression.
            $zip->addFile($p['path'], $p['name']);
            $zip->setCompressionName($p['name'], ZipArchive::CM_STORE);
        }
        $zip->close();

        return response()->download($tmp, (Str::slug($article->title_fr) ?: 'article').'-photos.zip', ['Content-Type' => 'application/zip'])
            ->deleteFileAfterSend();
    }
}
