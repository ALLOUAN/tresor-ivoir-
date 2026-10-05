<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * Nettoyage du contenu riche saisi dans l'éditeur (articles, événements).
 *
 * Liste blanche appliquée sur le DOM : balises, attributs, URL et propriétés CSS
 * autorisés ; tout le reste est retiré (scripts, gestionnaires on*, javascript:,
 * iframes hors YouTube/Vimeo, formulaires, SVG, commentaires…).
 */
class HtmlSanitizer
{
    /** Balises conservées. Les autres sont « dépliées » (leur texte est gardé). */
    private const ALLOWED_TAGS = [
        'p', 'br', 'a', 'strong', 'b', 'em', 'i', 'u', 's', 'strike', 'del', 'ins', 'mark', 'small', 'sub', 'sup',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'pre', 'code', 'ul', 'ol', 'li', 'span', 'div', 'hr',
        'table', 'thead', 'tbody', 'tfoot', 'tr', 'th', 'td', 'caption', 'colgroup', 'col',
        'img', 'figure', 'figcaption',
    ];

    /** Balises supprimées avec leur contenu. */
    private const DROP_WITH_CONTENT = [
        'script', 'style', 'object', 'embed', 'form', 'input', 'button', 'textarea', 'select', 'option',
        'link', 'meta', 'base', 'svg', 'math', 'template', 'noscript', 'title', 'head', 'frame', 'frameset',
        'applet', 'audio', 'video', 'source', 'canvas',
    ];

    /** Attributs autorisés par balise, en plus de class/style/title. */
    private const TAG_ATTRIBUTES = [
        'a' => ['href', 'target'],
        'img' => ['src', 'alt', 'width', 'height'],
        'td' => ['colspan', 'rowspan'],
        'th' => ['colspan', 'rowspan'],
        'ol' => ['start'],
        'col' => ['span'],
    ];

    private const STYLE_PROPERTIES = [
        'color', 'background-color', 'background', 'font-family', 'font-size', 'font-weight', 'font-style',
        'text-decoration', 'text-align', 'text-indent', 'line-height', 'letter-spacing', 'vertical-align',
        'margin', 'margin-top', 'margin-right', 'margin-bottom', 'margin-left',
        'padding', 'padding-top', 'padding-right', 'padding-bottom', 'padding-left',
        'width', 'max-width', 'height', 'float', 'list-style-type',
        'border', 'border-top', 'border-right', 'border-bottom', 'border-left', 'border-collapse', 'border-color',
    ];

    /** Vidéos intégrables (YouTube, Vimeo) — l'iframe est reconstruite, jamais copiée. */
    private const VIDEO_SRC = '#^https://(?:www\.youtube(?:-nocookie)?\.com/embed/[A-Za-z0-9_-]{6,20}|player\.vimeo\.com/video/\d{4,12})(?:\?[A-Za-z0-9=&_.%-]{0,200})?$#';

    private const MAX_DEPTH = 60;

    /**
     * Contenu riche éditeur prêt à être affiché (`{!! !!}`).
     */
    public static function articleBody(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        // Sans l'extension DOM on ne peut pas filtrer proprement : on échoue « fermé » (texte brut).
        if (! class_exists(DOMDocument::class)) {
            return htmlspecialchars(strip_tags($html), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }

        $doc = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $doc->loadHTML(
            '<!DOCTYPE html><html><head><meta charset="utf-8"></head><body><div id="__san">'.$html.'</div></body></html>',
            LIBXML_NONET | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $doc->getElementById('__san');
        if (! $root) {
            return htmlspecialchars(strip_tags($html), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }

        self::cleanChildren($root, 0);

        $out = '';
        foreach ($root->childNodes as $child) {
            $out .= $doc->saveHTML($child);
        }

        return trim($out);
    }

    /**
     * Version destinée à la base : nettoyée, et `null` quand il n'y a aucun contenu
     * visible (l'éditeur renvoie `<p><br></p>` pour un champ vide).
     */
    public static function forStorage(?string $html): ?string
    {
        $clean = self::articleBody($html);
        if ($clean === '') {
            return null;
        }

        $text = preg_replace('/[\s\x{00A0}]+/u', '', html_entity_decode(strip_tags($clean), ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?? '';
        if ($text === '' && ! preg_match('/<(?:img|iframe|hr|table)\b/i', $clean)) {
            return null;
        }

        return $clean;
    }

    private static function cleanChildren(DOMNode $parent, int $depth): void
    {
        foreach (iterator_to_array($parent->childNodes) as $node) {
            if ($node instanceof DOMElement) {
                if ($depth >= self::MAX_DEPTH) {
                    $parent->removeChild($node);

                    continue;
                }

                $tag = strtolower($node->tagName);

                if ($tag === 'iframe') {
                    self::rebuildVideoIframe($parent, $node);

                    continue;
                }

                if (in_array($tag, self::DROP_WITH_CONTENT, true)) {
                    $parent->removeChild($node);

                    continue;
                }

                self::cleanChildren($node, $depth + 1);

                if (! in_array($tag, self::ALLOWED_TAGS, true)) {
                    while ($node->firstChild) {
                        $parent->insertBefore($node->firstChild, $node);
                    }
                    $parent->removeChild($node);

                    continue;
                }

                if (! self::cleanAttributes($node, $tag)) {
                    $parent->removeChild($node);
                }
            } elseif ($node->nodeType !== XML_TEXT_NODE && $node->nodeType !== XML_CDATA_SECTION_NODE) {
                $parent->removeChild($node); // commentaires, instructions de traitement…
            }
        }
    }

    /** @return bool false si l'élément doit être supprimé (ex. <img> sans source valide). */
    private static function cleanAttributes(DOMElement $el, string $tag): bool
    {
        $extra = self::TAG_ATTRIBUTES[$tag] ?? [];

        foreach (iterator_to_array($el->attributes) as $attr) {
            $name = strtolower($attr->name);
            $value = (string) $attr->value;
            $keep = null;

            if ($name === 'class') {
                $keep = self::cleanClass($value);
            } elseif ($name === 'style') {
                $keep = self::cleanStyle($value);
            } elseif ($name === 'title') {
                $keep = mb_substr($value, 0, 300);
            } elseif (in_array($name, $extra, true)) {
                $keep = match ($name) {
                    'href' => self::safeUrl($value, false),
                    'src' => self::safeUrl($value, true),
                    'alt' => mb_substr($value, 0, 300),
                    'width', 'height' => preg_match('/^\d{1,4}(?:px|%)?$/', trim($value)) ? trim($value) : null,
                    'colspan', 'rowspan', 'span', 'start' => preg_match('/^\d{1,3}$/', trim($value)) ? trim($value) : null,
                    'target' => in_array($value, ['_blank', '_self'], true) ? $value : null,
                    default => null,
                };
            }

            if ($keep === null || $keep === '') {
                $el->removeAttribute($attr->name);
            } else {
                $el->setAttribute($attr->name, $keep);
            }
        }

        if ($tag === 'img') {
            if (! $el->hasAttribute('src')) {
                return false;
            }
            $el->setAttribute('loading', 'lazy');
        }

        if ($tag === 'a' && $el->getAttribute('target') === '_blank') {
            $el->setAttribute('rel', 'noopener noreferrer');
        }

        return true;
    }

    private static function cleanClass(string $value): ?string
    {
        $tokens = array_filter(
            preg_split('/\s+/', trim($value)) ?: [],
            fn ($t) => $t !== '' && preg_match('/^[A-Za-z0-9_\-:\/.\[\]%#]{1,60}$/', $t)
        );

        return $tokens ? implode(' ', array_slice($tokens, 0, 20)) : null;
    }

    private static function cleanStyle(string $value): ?string
    {
        $kept = [];
        foreach (explode(';', $value) as $declaration) {
            if (! str_contains($declaration, ':')) {
                continue;
            }
            [$prop, $val] = array_map('trim', explode(':', $declaration, 2));
            $prop = strtolower($prop);
            if (! in_array($prop, self::STYLE_PROPERTIES, true) || $val === '' || strlen($val) > 200) {
                continue;
            }
            if (! preg_match('/^[A-Za-z0-9\s#%.,()\-"\'\/]+$/', $val) || preg_match('/url|expression|javascript|behavior|binding|import/i', $val)) {
                continue;
            }
            $kept[] = $prop.': '.$val;
        }

        return $kept ? implode('; ', $kept).';' : null;
    }

    /** URL http(s)/mailto/tel ou relative ; `data:image/...` accepté pour les images uniquement. */
    private static function safeUrl(string $url, bool $forImage): ?string
    {
        $url = trim($url);
        if ($url === '') {
            return null;
        }

        // On retire espaces et caractères de contrôle avant de lire le schéma (« java\tscript: »).
        $probe = preg_replace('/[\x00-\x20\x7F]+/', '', $url) ?? '';

        if (preg_match('/^data:image\/(?:png|jpe?g|gif|webp);base64,[A-Za-z0-9+\/=]+$/i', $probe)) {
            return $forImage ? $probe : null;
        }

        if (preg_match('/^([a-z][a-z0-9+.\-]*):/i', $probe, $m)) {
            $scheme = strtolower($m[1]);
            $allowed = $forImage ? ['http', 'https'] : ['http', 'https', 'mailto', 'tel'];

            return in_array($scheme, $allowed, true) ? $url : null;
        }

        if (str_starts_with($probe, '//')) {
            return null;
        }

        return $url; // relative (/storage/..., page, #ancre, ?q=)
    }

    private static function rebuildVideoIframe(DOMNode $parent, DOMElement $iframe): void
    {
        $src = trim($iframe->getAttribute('src'));
        if (str_starts_with($src, '//')) {
            $src = 'https:'.$src;
        }
        $src = preg_replace('#^http://#i', 'https://', $src) ?? '';

        if (! preg_match(self::VIDEO_SRC, $src)) {
            $parent->removeChild($iframe);

            return;
        }

        $clean = $iframe->ownerDocument->createElement('iframe');
        $clean->setAttribute('src', $src);
        $clean->setAttribute('width', '640');
        $clean->setAttribute('height', '360');
        $clean->setAttribute('frameborder', '0');
        $clean->setAttribute('allowfullscreen', 'allowfullscreen');
        $clean->setAttribute('loading', 'lazy');
        $clean->setAttribute('referrerpolicy', 'strict-origin-when-cross-origin');
        $parent->replaceChild($clean, $iframe);
    }
}
