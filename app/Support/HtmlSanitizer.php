<?php

namespace App\Support;

class HtmlSanitizer
{
    /**
     * Contenu riche éditeur : retire scripts / handlers d’événements, limite les balises.
     */
    public static function articleBody(?string $html): string
    {
        if ($html === null || $html === '') {
            return '';
        }

        $html = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $html) ?? '';
        $html = preg_replace('/<style\b[^>]*>.*?<\/style>/is', '', $html) ?? '';
        $html = preg_replace('/ on\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html) ?? '';
        $html = preg_replace('/href\s*=\s*"\s*javascript:[^"]*"/i', 'href="#"', $html) ?? '';
        $html = preg_replace("/href\s*=\s*'\s*javascript:[^']*'/i", "href='#'", $html) ?? '';

        $allowed = '<p><br><a><strong><b><em><i><u><h2><h3><blockquote><ul><ol><li><span><div><hr><sub><sup><table><thead><tbody><tr><th><td>';

        return strip_tags($html, $allowed);
    }
}
