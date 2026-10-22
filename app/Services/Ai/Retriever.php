<?php

namespace App\Services\Ai;

use Illuminate\Support\Str;

/** Recherche des passages du site les plus proches d'une question (similarité TF-IDF + compréhension de la demande). */
class Retriever
{
    private QueryAnalyzer $analyzer;

    private array $snap;

    private TextVectorizer $vectorizer;

    /** @var array<string,CatalogItem> */
    private array $items;

    private function located(CatalogItem $i, array $city): bool
    {
        if ($i->cityId === $city['id'] || ($i->city && Str::lower(Str::ascii($i->city)) === Str::lower(Str::ascii($city['name'])))) {
            return true;
        }

        return $i->hasGeo() && $city['lat'] !== null && (Geo::km($city['lat'], $city['lng'], $i->lat, $i->lng) ?? INF) <= 18;
    }

    public function __construct(private readonly PassageIndex $index, Catalog $catalog)
    {
        $this->items = $catalog->items();
        $this->snap = $index->snapshot();
        $this->vectorizer = $index->vectorizer();
        $this->analyzer = new QueryAnalyzer(array_keys($this->snap['idf']), $catalog->cities());
    }

    /**
     * @return array{passages:array<int,array{passage:array,score:float}>, analysis:array, tokens:string[]}
     *         passages : un maximum de deux par source, triés par score décroissant
     */
    public function retrieve(string $question, int $sources = 3): array
    {
        $a = $this->analyzer->analyze($question);
        $interp = $a['interp'];
        $q = $a['tf'] ? $this->vectorizer->vector($a['tf']) : [];
        $cityNorm = $interp['city'] ? Str::lower(Str::ascii($interp['city']['name'])) : null;

        $scored = [];
        $cov = [];
        $loc = [];
        foreach ($this->snap['passages'] as $i => $p) {
            $cos = $q ? TextVectorizer::cosine($q, $this->snap['vectors'][$i]) : 0.0;
            if ($cos <= 0) {
                continue;
            }
            $titleHits = count(array_intersect($a['tokens'], TextVectorizer::tokens($p['title'])));
            $score = $cos + ($a['tokens'] ? 0.15 * $titleHits / count($a['tokens']) : 0);
            // Couverture : part des mots de la question retrouvés dans le passage (ou par un synonyme).
            $passTokens = $this->snap['vectors'][$i];
            $covered = 0;
            $visitable = in_array($p['type'], ['site', 'experience', 'leisure'], true);
            foreach ($a['tokens'] as $t) {
                $ok = isset($passTokens[$t]) || ($visitable && in_array($t, ['visiter', 'visit', 'voir', 'see', 'decouvrir', 'discover', 'activite', 'excursion', 'balade'], true));
                if (! $ok) {
                    foreach (SmartSearch::SYNONYMS as $g) {
                        if (in_array($t, $g, true) && array_intersect_key(array_flip($g), $passTokens)) {
                            $ok = true;
                            break;
                        }
                    }
                }
                $covered += $ok ? 1 : 0;
            }
            $coverage = $a['tokens'] ? $covered / count($a['tokens']) : 1.0;
            if ($cityNorm) {
                $hay = Str::lower(Str::ascii($p['title'].' '.$p['text']));
                $mentions = (bool) preg_match('/\b'.preg_quote($cityNorm, '/').'\b/', $hay);
                // Pour une fiche du catalogue, ce qui compte est son emplacement, pas la simple mention de la ville.
                if (isset($this->items[$p['src']])) {
                    $mentions = $this->located($this->items[$p['src']], $interp['city']);
                }
                $loc[$i] = $mentions;
                $score *= $mentions ? 1.3 : 0.75;
                // « Où dormir / que visiter à X » : la ville et le type suffisent à désigner la réponse.
                $wantsVisit = (bool) array_intersect($a['tokens'], ['visiter', 'visit', 'voir', 'see', 'decouvrir', 'discover', 'activite']);
                if ($mentions && (($interp['type'] && $p['type'] === $interp['type']) || ($wantsVisit && $visitable))) {
                    $score = max($score, 0.3 + 0.5 * $cos);
                }
            }
            if ($interp['type'] && $p['type'] === $interp['type']) {
                $score = $score * 1.4 + 0.08;
            }
            // « Où visiter… » : sites, expériences et loisirs passent devant le reste.
            if ($visitable && array_intersect($a['tokens'], ['visiter', 'visit', 'voir', 'see', 'decouvrir', 'discover', 'activite'])) {
                $score = $score * 1.4 + 0.08;
            }
            if ($p['type'] === 'faq' && preg_match('/\b(comment|how|puis-je|peut-on|can i|inscri|compte|account|reserv|annul|payer|paiement|prestataire)\b/', Str::lower(Str::ascii($question)))) {
                $score *= 1.25;
            }
            $scored[$i] = $score;
            $cov[$i] = $coverage;
        }
        arsort($scored);

        $perSource = [];
        $out = [];
        foreach ($scored as $i => $score) {
            $p = $this->snap['passages'][$i];
            $perSource[$p['src']] = ($perSource[$p['src']] ?? 0) + 1;
            if ($perSource[$p['src']] > 3) {
                continue;
            }
            $out[] = ['passage' => $p, 'score' => round($score, 4), 'coverage' => round($cov[$i], 2), 'located' => $loc[$i] ?? false];
            if (count(array_unique(array_map(fn ($x) => $x['passage']['src'], $out))) > $sources + 1) {
                break;
            }
        }
        // limite aux $sources premières sources distinctes
        $seen = [];
        $final = [];
        foreach ($out as $r) {
            $src = $r['passage']['src'];
            $seen[$src] = true;
            if (count($seen) > $sources) {
                break;
            }
            $final[] = $r;
        }

        return ['passages' => $final, 'analysis' => $a, 'tokens' => $a['tokens']];
    }
}
