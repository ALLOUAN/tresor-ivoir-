<?php

namespace App\Services\Ai;

use App\Support\HtmlSanitizer;
use Illuminate\Support\Str;

/**
 * Aide à la rédaction et à la traduction (éditeurs, prestataires).
 *
 *  - summary : résumé du texte (≤ 300 caractères) ;
 *  - seo     : titre et description pour les moteurs de recherche (≤ 70 et ≤ 165 caractères) ;
 *  - titles  : trois titres alternatifs (modèle de langage requis) ;
 *  - translate : traduction FR ⇄ EN du titre, du résumé, du contenu HTML et du SEO (modèle de langage requis).
 *
 * Résumé et SEO fonctionnent sans service externe (extraction des phrases clés). Toute production d'un
 * modèle est VÉRIFIÉE (limites de longueur, aucun chiffre inventé, structure HTML et liens inchangés)
 * avant d'être proposée ; l'utilisateur relit et applique lui-même : rien n'est enregistré automatiquement.
 */
class WritingAssistant
{
    public const LIMITS = ['excerpt' => 500, 'meta_title' => 70, 'meta_desc' => 165, 'title' => 255, 'subtitle' => 200, 'description' => 20000];

    public function __construct(private readonly LlmClient $llm) {}

    public function llmAvailable(): bool
    {
        return $this->llm->available();
    }

    /** @return array{ok:bool,engine:string,result:array,warnings:string[],message:?string} */
    public function run(string $action, array $in): array
    {
        $lang = ($in['lang'] ?? 'fr') === 'en' ? 'en' : 'fr';

        return match ($action) {
            'summary' => $this->summary((string) ($in['title'] ?? ''), (string) ($in['content'] ?? ''), $lang),
            'seo' => $this->seo((string) ($in['title'] ?? ''), (string) ($in['content'] ?? ''), $lang),
            'titles' => $this->titles((string) ($in['title'] ?? ''), (string) ($in['content'] ?? ''), $lang),
            'translate' => $this->translate((array) ($in['texts'] ?? []), (string) ($in['from'] ?? 'fr'), (string) ($in['to'] ?? 'en')),
            default => $this->fail('Action inconnue.'),
        };
    }

    // ── Résumé ──────────────────────────────────────────────────────────────

    public function summary(string $title, string $html, string $lang): array
    {
        $plain = $this->plain($html);
        if (str_word_count(Str::ascii($plain)) < 25) {
            return $this->fail('Le texte est trop court pour être résumé : rédigez d\'abord le contenu.');
        }
        if ($this->llm->available()) {
            $out = $this->llm->json($this->llm->complete(
                $this->sys("Tu rédiges le résumé d'un article de magazine de tourisme ({$lang}). 2 à 3 phrases, 300 caractères maximum, ton engageant et fidèle au texte. N'ajoute aucune information. Réponds par {\"summary\": \"...\"}."),
                "Titre : {$title}\n\nTexte :\n".Str::limit($plain, 6000, ''), 400, config('ai.writing.model'), 40
            ) ?? '');
            $text = is_array($out) ? trim(strip_tags((string) ($out['summary'] ?? ''))) : '';
            if ($text !== '' && mb_strlen($text) <= 320 && $this->numbersOk($text, $plain.' '.$title)) {
                return $this->ok('anthropic', ['text' => $text]);
            }
        }

        return $this->ok('extractive', ['text' => $this->extractiveSummary($plain, 300)], $this->llm->available() ? ['Le modèle de langage n\'a pas fourni de résumé fiable : résumé par extraction proposé à la place.'] : []);
    }

    /** Les phrases les plus représentatives du texte, remises dans l'ordre de lecture. */
    public function extractiveSummary(string $plain, int $max): string
    {
        $sentences = array_values(array_filter(array_map('trim', preg_split('/(?<=[.!?…])\s+(?=\p{Lu}|\d|«)/u', $plain) ?: []), fn ($s) => str_word_count(Str::ascii($s)) >= 5));
        if (! $sentences) {
            return Str::limit($plain, $max);
        }
        $tf = [];
        foreach ($sentences as $s) {
            foreach (array_unique(TextVectorizer::tokens($s)) as $t) {
                $tf[$t] = ($tf[$t] ?? 0) + 1;
            }
        }
        $scored = [];
        foreach ($sentences as $i => $s) {
            $toks = array_unique(TextVectorizer::tokens($s));
            $sum = 0.0;
            foreach ($toks as $t) {
                $sum += $tf[$t] > 1 ? log(1 + $tf[$t]) : 0.0;   // un mot répandu dans le texte porte le sujet
            }
            $score = $toks ? $sum / sqrt(count($toks)) : 0.0;
            $score *= $i === 0 ? 1.4 : 1.0 / (1 + $i * 0.08);   // l'ouverture résume souvent l'article
            $scored[$i] = $score;
        }
        arsort($scored);
        $picked = [];
        $len = 0;
        // L'ouverture pose presque toujours le sujet : on la garde d'abord, puis on complète.
        $order = array_merge([0], array_values(array_diff(array_keys($scored), [0])));
        foreach ($order as $i) {
            $l = mb_strlen($sentences[$i]) + 1;
            if ($len + $l > $max && $picked) {
                continue;
            }
            if ($len + $l > $max && ! $picked) {
                return Str::limit($sentences[$i], $max - 1, '…');
            }
            $picked[] = $i;
            $len += $l;
            if ($len >= $max * 0.8) {
                break;
            }
        }
        sort($picked);

        return trim(implode(' ', array_map(fn ($i) => $sentences[$i], $picked)));
    }

    // ── SEO ─────────────────────────────────────────────────────────────────

    public function seo(string $title, string $html, string $lang): array
    {
        $plain = $this->plain($html);
        if ($title === '' && $plain === '') {
            return $this->fail('Saisissez au moins un titre ou un texte.');
        }
        if ($this->llm->available() && str_word_count(Str::ascii($plain)) >= 25) {
            $out = $this->llm->json($this->llm->complete(
                $this->sys("Tu optimises le référencement d'un article de tourisme ({$lang}). Réponds par {\"meta_title\": \"...\", \"meta_desc\": \"...\"} : titre de 60 caractères maximum, description de 155 caractères maximum, fidèles au texte, sans mot-clé artificiel."),
                "Titre : {$title}\n\nTexte :\n".Str::limit($plain, 5000, ''), 300, config('ai.writing.model'), 40
            ) ?? '');
            if (is_array($out)) {
                $mt = trim(strip_tags((string) ($out['meta_title'] ?? '')));
                $md = trim(strip_tags((string) ($out['meta_desc'] ?? '')));
                if ($mt !== '' && $md !== '' && mb_strlen($mt) <= self::LIMITS['meta_title'] && mb_strlen($md) <= self::LIMITS['meta_desc'] && $this->numbersOk($mt.' '.$md, $plain.' '.$title)) {
                    return $this->ok('anthropic', ['meta_title' => $mt, 'meta_desc' => $md]);
                }
            }
        }
        $base = $plain !== '' ? $this->extractiveSummary($plain, 150) : $title;

        return $this->ok('extractive', [
            'meta_title' => $this->cut($title !== '' ? $title : $base, self::LIMITS['meta_title']),
            'meta_desc' => $this->cut($base, self::LIMITS['meta_desc']),
        ]);
    }

    // ── Titres ──────────────────────────────────────────────────────────────

    public function titles(string $title, string $html, string $lang): array
    {
        if (! $this->llm->available()) {
            return $this->fail('Les titres alternatifs nécessitent un modèle de langage (clé ANTHROPIC_API_KEY non configurée).', 'unavailable');
        }
        $plain = $this->plain($html);
        if ($title === '' && $plain === '') {
            return $this->fail('Saisissez au moins un titre ou un texte.');
        }
        $out = $this->llm->json($this->llm->complete(
            $this->sys("Tu proposes des titres pour un article de magazine de tourisme en Côte d'Ivoire ({$lang}). Réponds par {\"titles\": [\"...\", \"...\", \"...\"]} : trois titres différents (90 caractères maximum chacun), accrocheurs mais exacts, sans point d'exclamation ni majuscules abusives, sans fait absent du texte."),
            "Titre actuel : {$title}\n\nTexte :\n".Str::limit($plain, 5000, ''), 300, config('ai.writing.model'), 40
        ) ?? '');
        $titles = [];
        foreach ((array) ($out['titles'] ?? []) as $t) {
            $t = trim(strip_tags((string) $t));
            if ($t !== '' && mb_strlen($t) <= 110 && Str::lower($t) !== Str::lower($title) && $this->numbersOk($t, $plain.' '.$title) && ! in_array($t, $titles, true)) {
                $titles[] = $t;
            }
        }
        if (! $titles) {
            return $this->fail('Aucune proposition fiable : réessayez.', 'rejected');
        }

        return $this->ok('anthropic', ['titles' => array_slice($titles, 0, 3)]);
    }

    // ── Traduction ──────────────────────────────────────────────────────────

    /** @param array<string,string> $texts clés : title, subtitle, excerpt, content, meta_title, meta_desc */
    public function translate(array $texts, string $from, string $to): array
    {
        $from = $from === 'en' ? 'en' : 'fr';
        $to = $to === 'fr' ? 'fr' : 'en';
        $texts = array_filter(array_map(fn ($t) => trim((string) $t), $texts), fn ($t) => $t !== '');
        if ($from === $to || ! $texts) {
            return $this->fail('Rien à traduire.');
        }
        if (! $this->llm->available()) {
            return $this->fail('La traduction nécessite un modèle de langage (clé ANTHROPIC_API_KEY non configurée) : sans lui, aucune traduction fiable n\'est possible.', 'unavailable');
        }
        $names = ['fr' => 'français', 'en' => 'anglais'];
        $out = $this->llm->json($this->llm->complete(
            $this->sys("Tu traduis du {$names[$from]} vers le {$names[$to]} pour un magazine de tourisme en Côte d'Ivoire. Conserve le sens exact, le ton, les noms propres, les chiffres et dates ; pour les champs HTML, garde STRICTEMENT les mêmes balises, attributs et liens, traduis seulement le texte. Respecte les longueurs : meta_title ≤ 70 caractères, meta_desc ≤ 165, excerpt ≤ 500. Réponds uniquement par un objet JSON avec exactement les mêmes clés que celui reçu."),
            json_encode($texts, JSON_UNESCAPED_UNICODE), 4000, config('ai.writing.model'), 90
        ) ?? '');
        if (! is_array($out)) {
            return $this->fail('Le service de traduction n\'a pas répondu correctement : réessayez.', 'rejected');
        }

        $result = [];
        $warnings = [];
        foreach ($texts as $key => $src) {
            $t = isset($out[$key]) && is_string($out[$key]) ? trim($out[$key]) : '';
            if ($t === '') {
                $warnings[] = "« {$key} » : traduction absente.";

                continue;
            }
            $verdict = $this->checkTranslation($key, $src, $t);
            if ($verdict['ok']) {
                $result[$key] = $verdict['text'];
                $warnings = array_merge($warnings, $verdict['warnings']);
            } else {
                $warnings[] = "« {$key} » : traduction écartée ({$verdict['why']}).";
            }
        }
        if (! $result) {
            return $this->fail('Aucune traduction n\'a passé les contrôles : '.implode(' ', $warnings), 'rejected');
        }

        return $this->ok('anthropic', $result, $warnings);
    }

    /** @return array{ok:bool,text:string,why:string,warnings:string[]} */
    public function checkTranslation(string $key, string $src, string $out): array
    {
        $warnings = [];
        $isHtml = $key === 'content';
        if (! $isHtml) {
            $out = trim(strip_tags($out));
            $max = self::LIMITS[$key] ?? self::LIMITS['title'];
            if (mb_strlen($out) > $max) {
                if (! in_array($key, ['meta_title', 'meta_desc', 'excerpt'], true)) {
                    return ['ok' => false, 'text' => '', 'why' => 'trop long', 'warnings' => []];
                }
                $out = $this->cut($out, $max);
                $warnings[] = "« {$key} » raccourci à {$max} caractères.";
            }
        } else {
            if ($this->tagSignature($src) !== $this->tagSignature($out)) {
                return ['ok' => false, 'text' => '', 'why' => 'la structure HTML ou les liens ont changé', 'warnings' => []];
            }
            $out = HtmlSanitizer::articleBody($out);
        }
        $ratio = mb_strlen(strip_tags($out)) / max(1, mb_strlen(strip_tags($src)));
        if ($ratio < 0.4 || $ratio > 2.6) {
            return ['ok' => false, 'text' => '', 'why' => 'longueur incohérente avec l\'original', 'warnings' => []];
        }
        if ($this->digits($src) !== $this->digits($out)) {
            return ['ok' => false, 'text' => '', 'why' => 'les chiffres ou dates ne correspondent pas à l\'original', 'warnings' => []];
        }

        return ['ok' => true, 'text' => $out, 'why' => '', 'warnings' => $warnings];
    }

    // ── Vérifications et utilitaires ────────────────────────────────────────

    /** Suite des balises ouvrantes avec leurs liens (href, src) : doit être identique avant/après traduction. */
    private function tagSignature(string $html): string
    {
        preg_match_all('/<\s*([a-z][a-z0-9]*)\b([^>]*)>/i', $html, $m, PREG_SET_ORDER);
        $sig = [];
        foreach ($m as $t) {
            $attrs = [];
            if (preg_match('/\b(?:href|src)\s*=\s*"([^"]*)"/i', $t[2], $a)) {
                $attrs[] = $a[1];
            }
            $sig[] = strtolower($t[1]).($attrs ? '='.implode(',', $attrs) : '');
        }

        return implode('|', $sig);
    }

    /** Suites de chiffres du texte (prix, dates, quantités), triées. */
    private function digits(string $s): string
    {
        preg_match_all('/\d+/', strip_tags($s), $m);
        $d = $m[0];
        sort($d);

        return implode(',', $d);
    }

    /** Un résumé, un titre ou un SEO ne doit citer que des nombres déjà présents dans le texte. */
    private function numbersOk(string $generated, string $source): bool
    {
        preg_match_all('/\d+/', $generated, $g);
        preg_match_all('/\d+/', $source, $s);

        return ! array_diff($g[0], $s[0]);
    }

    private function plain(string $html): string
    {
        $t = preg_replace('~</(p|li|h[1-6]|div|tr)>|<br\s*/?>~i', ' ', $html);

        return trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags((string) $t), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }

    private function cut(string $text, int $max): string
    {
        $text = trim(preg_replace('/\s+/', ' ', $text));
        if (mb_strlen($text) <= $max) {
            return $text;
        }
        $cut = mb_substr($text, 0, $max - 1);
        $space = mb_strrpos($cut, ' ');

        return rtrim($space !== false && $space > $max * 0.6 ? mb_substr($cut, 0, $space) : $cut, " ,;:.-—").'…';
    }

    private function sys(string $s): string
    {
        return $s.' Réponds uniquement par un objet JSON valide, sans texte autour.';
    }

    private function ok(string $engine, array $result, array $warnings = []): array
    {
        return ['ok' => true, 'engine' => $engine, 'result' => $result, 'warnings' => $warnings, 'message' => null];
    }

    private function fail(string $message, string $engine = 'none'): array
    {
        return ['ok' => false, 'engine' => $engine, 'result' => [], 'warnings' => [], 'message' => $message];
    }
}
