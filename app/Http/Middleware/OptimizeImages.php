<?php

namespace App\Http\Middleware;

use App\Services\ImageResizer;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pages publiques : remplace les images /storage/… lourdes par des variantes WebP
 * redimensionnées (srcset), et charge en différé celles qui sont sous la ligne de flottaison.
 * Ne touche ni aux données ni aux vues ; sans effet sur le back-office.
 */
class OptimizeImages
{
    /** Ne pas toucher aux images de moins de ce poids / de cette largeur (logos, icônes). */
    private const MIN_BYTES = 60 * 1024;

    private const MIN_WIDTH = 500;

    private bool $firstLargeSeen = false;

    /** @var array<string, array{w:int, v:int}|null> */
    private array $info = [];

    public function __construct(private readonly ImageResizer $resizer) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $this->firstLargeSeen = false;

        if (! config('services.images.optimize', true)
            || ! $request->isMethod('GET')
            || $request->expectsJson()
            || $request->is('admin*', 'editor*', 'provider*', 'img/*')
            || $response->getStatusCode() !== 200
            || ! str_contains((string) $response->headers->get('Content-Type'), 'text/html')
            || $response instanceof \Symfony\Component\HttpFoundation\BinaryFileResponse
            || $response instanceof \Symfony\Component\HttpFoundation\StreamedResponse) {
            return $response;
        }

        $html = $response->getContent();
        if (! is_string($html) || ! str_contains($html, '/storage/')) {
            return $response;
        }

        $host = preg_quote($request->getHost(), '#');
        $storage = '(?:https?://'.$host.')?/storage/';

        // 1. Balises <img>
        $html = preg_replace_callback('#<img\b[^>]*>#i', function (array $m) use ($storage) {
            $tag = $m[0];
            if (stripos($tag, 'srcset=') !== false
                || ! preg_match('#\bsrc=(["\'])'.$storage.'([^"\'?\#]+)(?:\?[^"\']*)?\1#i', $tag, $s)) {
                return $tag;
            }
            $meta = $this->meta(rawurldecode($s[2]));
            if ($meta === null) {
                return $tag;
            }

            $candidates = array_values(array_filter(ImageResizer::WIDTHS, fn ($w) => $w <= $meta['w']));
            if ($candidates === []) {
                return $tag;
            }
            $default = collect($candidates)->filter(fn ($w) => $w <= 1200)->last() ?? $candidates[0];
            $url = fn (int $w) => '/img/'.$w.'/'.$s[2].'?v='.$meta['v'];
            $srcset = collect($candidates)->map(fn ($w) => $url($w).' '.$w.'w')->implode(', ');

            $hasLoading = (bool) preg_match('#\bloading=#i', $tag);
            $eager = ! $hasLoading && ! $this->firstLargeSeen;
            $this->firstLargeSeen = true;

            $tag = str_replace($s[0], 'src="'.$url($default).'" srcset="'.$srcset.'"', $tag);
            $extra = '';
            if (! $hasLoading) {
                $extra .= $eager ? ' loading="eager"' : ' loading="lazy"';
            }
            if (! preg_match('#\bdecoding=#i', $tag)) {
                $extra .= ' decoding="async"';
            }
            if (! preg_match('#\bsizes=#i', $tag)) {
                $extra .= ($eager || $hasLoading) ? ' sizes="100vw"' : ' sizes="auto, 100vw"';
            }

            return preg_replace('#\s*(/?)>$#', $extra.'$1>', $tag);
        }, $html);

        // 1b. <picture><source srcset="/storage/…"> : une seule URL, sans descripteur
        $html = preg_replace_callback('#<source\b[^>]*>#i', function (array $m) use ($storage) {
            $tag = $m[0];
            if (! preg_match('#\bsrcset=(["\'])'.$storage.'([^"\'?\#\s,]+)(?:\?[^"\']*)?\1#i', $tag, $s)) {
                return $tag;
            }
            $meta = $this->meta(rawurldecode($s[2]));
            if ($meta === null) {
                return $tag;
            }
            $max = preg_match('#min-width:\s*(\d+)#i', $tag, $mw) && (int) $mw[1] >= 1024 ? 1920 : 1200;
            $w = collect(ImageResizer::WIDTHS)->filter(fn ($x) => $x <= min($meta['w'], $max))->last();
            if ($w === null) {
                return $tag;
            }

            return str_replace($s[0], 'srcset="/img/'.$w.'/'.$s[2].'?v='.$meta['v'].'"', $tag);
        }, $html);

        // 2. Images de fond CSS : url('/storage/…')
        $html = preg_replace_callback('#url\((["\']?)'.$storage.'([^)"\'?\#]+)(?:\?[^)"\']*)?\1\)#i', function (array $m) {
            $meta = $this->meta(rawurldecode($m[2]));
            if ($meta === null) {
                return $m[0];
            }
            $w = collect([800, 1200, 1600])->filter(fn ($x) => $x <= $meta['w'])->last();
            if ($w === null) {
                return $m[0];
            }

            return 'url('.$m[1].'/img/'.$w.'/'.$m[2].'?v='.$meta['v'].$m[1].')';
        }, $html);

        if ($html !== null) {
            $original = $response->original ?? null;
            $response->setContent($html);
            if ($original !== null) {
                $response->original = $original;   // setContent() l'écrase : les tests (assertViewHas) et les middlewares en aval en ont besoin
            }
        }

        return $response;
    }

    /** Largeur et version du fichier, ou null s'il ne vaut pas la peine d'être converti. */
    private function meta(string $relative): ?array
    {
        if (array_key_exists($relative, $this->info)) {
            return $this->info[$relative];
        }

        $abs = $this->resizer->sourcePath($relative);
        $size = $abs ? @getimagesize($abs) : false;
        if (! $abs || ! $size
            || ($size[0] < self::MIN_WIDTH && filesize($abs) < self::MIN_BYTES)
            || ($size[0] <= 200)) {
            return $this->info[$relative] = null;
        }

        return $this->info[$relative] = ['w' => (int) $size[0], 'v' => (int) filemtime($abs)];
    }
}
