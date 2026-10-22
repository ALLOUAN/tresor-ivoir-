<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Évaluation de l'aide à la rédaction :
 *  - résumé par extraction sur les articles publiés : limites respectées, reprise des mots clés du texte,
 *    comparée à la simple coupe des premiers caractères ;
 *  - SEO : limites de longueur ;
 *  - contrôles appliqués aux productions d'un modèle de langage (simulées) : traduction fidèle acceptée,
 *    chiffre modifié / lien changé / balise perdue / longueur absurde refusés ; résumé avec nombre inventé écarté.
 * La QUALITÉ d'une traduction réelle ne se mesure pas sans clé ni relecteur humain : elle n'est pas évaluée ici.
 */
class WritingEvaluator
{
    public function __construct(private readonly WritingAssistant $writer) {}

    /** @return array<string,mixed> */
    public function run(): array
    {
        return ['summary' => $this->summaries(), 'seo' => $this->seo(), 'guards' => $this->guards(), 'usage' => $this->usage()];
    }

    private function summaries(): array
    {
        $rows = DB::table('articles')->where('status', 'published')->whereNull('deleted_at')->get(['title_fr', 'content_fr']);
        $n = 0;
        $limitOk = 0;
        $cov = 0.0;
        $baseCov = 0.0;
        $ratio = 0.0;
        $weak = [];
        foreach ($rows as $a) {
            $plain = trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags(str_replace(['</p>', '<br>', '</li>', '</h2>', '</h3>'], ' ', (string) $a->content_fr)))));
            if (str_word_count(Str::ascii($plain)) < 40) {
                continue;
            }
            $s = $this->writer->extractiveSummary($plain, 300);
            $n++;
            $limitOk += (mb_strlen($s) <= 300 && $s !== '' && $s === strip_tags($s)) ? 1 : 0;
            $keys = $this->keyTerms($plain);
            $c = $this->coverage($keys, $s);
            $b = $this->coverage($keys, Str::limit($plain, 300, ''));
            $cov += $c;
            $baseCov += $b;
            $ratio += mb_strlen($s) / max(1, mb_strlen($plain));
            if ($c < $b) {
                $weak[] = Str::limit($a->title_fr, 45);
            }
        }

        return [
            'articles' => $n, 'within_limit' => $n ? round($limitOk / $n, 3) : null,
            'coverage' => $n ? round($cov / $n, 3) : null, 'baseline_coverage' => $n ? round($baseCov / $n, 3) : null,
            'compression' => $n ? round($ratio / $n, 3) : null, 'weaker_than_baseline' => $weak,
        ];
    }

    /** Les 8 mots les plus fréquents du texte (hors mots vides). */
    private function keyTerms(string $plain): array
    {
        $freq = array_count_values(array_filter(TextVectorizer::tokens($plain), fn ($t) => strlen($t) >= 5));
        arsort($freq);

        return array_slice(array_keys($freq), 0, 8);
    }

    private function coverage(array $keys, string $text): float
    {
        if (! $keys) {
            return 0.0;
        }
        $toks = array_flip(TextVectorizer::tokens($text));

        return count(array_filter($keys, fn ($k) => isset($toks[$k]))) / count($keys);
    }

    private function seo(): array
    {
        $rows = DB::table('articles')->where('status', 'published')->whereNull('deleted_at')->get(['title_fr', 'content_fr']);
        $ok = 0;
        $n = 0;
        foreach ($rows as $a) {
            $r = $this->writer->seo($a->title_fr, (string) $a->content_fr, 'fr');
            if (! $r['ok']) {
                continue;
            }
            $n++;
            $ok += (mb_strlen($r['result']['meta_title']) <= 70 && mb_strlen($r['result']['meta_desc']) <= 165 && $r['result']['meta_title'] !== '') ? 1 : 0;
        }

        return ['articles' => $n, 'within_limits' => $n ? round($ok / $n, 3) : null];
    }

    /** @return array{cases:int,correct:int,rows:array<int,array{label:string,pass:bool}>} */
    private function guards(): array
    {
        $html = '<p>Le <strong>Festival des Masques</strong> a lieu du 12 au 14 mars 2026 à Man. <a href="https://example.org/billets">Billets</a> à 5000 FCFA.</p>';
        $good = '<p>The <strong>Masks Festival</strong> takes place from 12 to 14 March 2026 in Man. <a href="https://example.org/billets">Tickets</a> at 5000 FCFA.</p>';
        $cases = [
            ['label' => 'traduction fidèle acceptée', 'key' => 'content', 'src' => $html, 'out' => $good, 'accept' => true],
            ['label' => 'prix modifié refusé', 'key' => 'content', 'src' => $html, 'out' => str_replace('5000', '3000', $good), 'accept' => false],
            ['label' => 'date modifiée refusée', 'key' => 'content', 'src' => $html, 'out' => str_replace('12 to 14', '12 to 15', $good), 'accept' => false],
            ['label' => 'lien changé refusé', 'key' => 'content', 'src' => $html, 'out' => str_replace('example.org', 'evil.example', $good), 'accept' => false],
            ['label' => 'balise supprimée refusée', 'key' => 'content', 'src' => $html, 'out' => str_replace(['<strong>', '</strong>'], '', $good), 'accept' => false],
            ['label' => 'texte démesuré refusé', 'key' => 'content', 'src' => $html, 'out' => '<p>'.str_repeat('Masks Festival 12 14 2026 5000. ', 40).'</p>', 'accept' => false],
            ['label' => 'balise script ajoutée refusée', 'key' => 'content', 'src' => $html, 'out' => '<script>alert(1)</script>'.$good, 'accept' => false],
            ['label' => 'titre SEO démesuré refusé', 'key' => 'meta_title', 'src' => 'Festival des Masques de Man 2026', 'out' => str_repeat('Masks Festival of Man ', 6).' 2026', 'accept' => false],
            ['label' => 'titre SEO un peu long raccourci', 'key' => 'meta_title', 'src' => 'Festival des Masques de Man 2026 : danses et traditions de l\'ouest ivoirien', 'out' => 'Masks Festival of Man 2026: dances, traditions and parades from western Ivory Coast', 'accept' => 'truncated'],
            ['label' => 'titre vide d\'origine non traduit', 'key' => 'title', 'src' => 'Festival 2026', 'out' => 'Festival 2027', 'accept' => false],
        ];
        $rows = [];
        $ok = 0;
        foreach ($cases as $c) {
            $r = $this->writer->checkTranslation($c['key'], $c['src'], $c['out']);
            $pass = match ($c['accept']) {
                true => $r['ok'],
                false => ! $r['ok'],
                'sanitized' => $r['ok'] && ! str_contains($r['text'], '<script'),
                'truncated' => $r['ok'] && mb_strlen($r['text']) <= 70 && $r['warnings'] !== [],
            };
            $ok += $pass ? 1 : 0;
            $rows[] = ['label' => $c['label'], 'pass' => $pass];
        }

        // Résumé d'un modèle de langage contenant un nombre inventé : écarté au profit de l'extraction
        $prev = config('ai.llm.api_key');
        config(['ai.llm.api_key' => 'test-key']);
        Http::fake(['api.anthropic.com/*' => Http::response(['content' => [['type' => 'text', 'text' => '{"summary": "Un festival unique qui réunit 50000 visiteurs chaque année."}']]], 200)]);
        $text = 'Le festival des masques de Man réunit chaque année les villages de la région autour de danses et de chants traditionnels. Les troupes défilent dans les rues pendant trois jours, devant un public venu de toute la Côte d\'Ivoire et d\'ailleurs. Les artisans exposent leurs masques sculptés et les visiteurs peuvent assister aux cérémonies.';
        $r = $this->writer->summary('Festival des masques', '<p>'.$text.'</p>', 'fr');
        $pass = $r['ok'] && $r['engine'] === 'extractive' && ! str_contains($r['result']['text'], '50000');
        $rows[] = ['label' => 'nombre inventé dans un résumé écarté', 'pass' => $pass];
        $ok += $pass ? 1 : 0;
        config(['ai.llm.api_key' => $prev]);

        return ['cases' => count($rows), 'correct' => $ok, 'rows' => $rows];
    }

    private function usage(): array
    {
        if (! \App\Support\SchemaCache::hasTable('ai_writing_logs')) {
            return ['total' => 0];
        }
        $q = DB::table('ai_writing_logs');
        $total = (clone $q)->count();
        $ok = (clone $q)->where('status', 'ok')->count();
        $applied = (clone $q)->where('applied', true)->count();
        $byAction = (clone $q)->select('action', DB::raw('COUNT(*) c'))->groupBy('action')->pluck('c', 'action')->all();

        return ['total' => $total, 'ok' => $ok, 'applied' => $applied, 'applied_rate' => $ok ? round($applied / $ok, 3) : null, 'by_action' => $byAction];
    }
}
