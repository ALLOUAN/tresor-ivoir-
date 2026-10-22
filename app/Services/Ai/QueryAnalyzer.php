<?php

namespace App\Services\Ai;

use Illuminate\Support\Str;

/**
 * Compréhension d'une demande en langage naturel (français / anglais), partagée par la recherche
 * intelligente et l'assistant : ville, type de lieu, intention de prix, traduction des mots
 * anglais courants, correction des fautes de frappe (par rapport à un vocabulaire donné) et
 * élargissement par synonymes.
 */
class QueryAnalyzer
{
    private array $vocab;

    private array $trigramIndex = [];

    /** @param string[] $vocabulary termes connus de l'index interrogé ; @param array<int,array> $cities villes du catalogue */
    public function __construct(array $vocabulary, private readonly array $cities)
    {
        $this->vocab = array_flip($vocabulary);
    }

    /**
     * @return array{tokens:string[], tf:array<string,float>, interp:array<string,mixed>, did_you_mean:?string}
     */
    public function analyze(string $query): array
    {
        $query = trim(preg_replace('/\s+/', ' ', $query));
        $norm = Str::lower(Str::ascii($query));
        $interp = ['city' => null, 'type' => null, 'price' => null, 'terms' => [], 'translated' => false];

        $norm = $this->extractCity($norm, $interp);

        foreach (SmartSearch::CHEAP as $p) {
            if (str_contains($norm, $p)) {
                $interp['price'] = 'eco';
                $norm = str_replace($p, ' ', $norm);
            }
        }
        foreach (SmartSearch::LUXE as $p) {
            if (str_contains($norm, $p)) {
                $interp['price'] = 'luxe';
                $norm = str_replace($p, ' ', $norm);
            }
        }

        $tokens = [];
        $corrected = [];
        $changed = false;
        foreach (TextVectorizer::tokens($norm) as $t) {
            if (in_array($t, SmartSearch::FILLERS, true)) {
                continue;
            }
            if (isset(SmartSearch::EN_FR[$t])) {
                if (SmartSearch::EN_FR[$t] !== $t) {
                    $interp['translated'] = true;
                }
                $t = SmartSearch::EN_FR[$t];
                if ($t === 'economique') {
                    $interp['price'] = 'eco';
                    continue;
                }
                if ($t === 'luxe') {
                    $interp['price'] = 'luxe';
                    continue;
                }
            }
            if (in_array($t, ['economique', 'abordable'], true)) {
                $interp['price'] = 'eco';
                continue;
            }
            $fixed = $this->correct($t);
            if ($fixed !== $t) {
                $changed = true;
            }
            $tokens[] = $fixed;
            $corrected[] = $fixed;
        }
        $tokens = array_values(array_unique($tokens));
        $interp['terms'] = $tokens;

        foreach ($tokens as $t) {
            if (isset(SmartSearch::TYPE_WORDS[$t]) && ! $interp['type']) {
                $interp['type'] = SmartSearch::TYPE_WORDS[$t];
            }
        }

        $didYouMean = null;
        if ($changed) {
            $didYouMean = $interp['city'] ? trim(implode(' ', $corrected).' '.$interp['city']['name']) : trim(implode(' ', $corrected));
        }

        // Vecteur de termes : exacts (poids 1) + synonymes (0,5 ; 0,9 si le mot lui-même est inconnu)
        $tf = [];
        foreach ($tokens as $t) {
            $known = isset($this->vocab[$t]);
            if ($known) {
                $tf[$t] = ($tf[$t] ?? 0) + 1.0;
            }
            foreach (SmartSearch::SYNONYMS as $group) {
                if (in_array($t, $group, true)) {
                    foreach ($group as $syn) {
                        if ($syn !== $t && isset($this->vocab[$syn])) {
                            $tf[$syn] = max($tf[$syn] ?? 0, $known ? 0.5 : 0.9);
                        }
                    }
                }
            }
        }

        return ['tokens' => $tokens, 'tf' => $tf, 'interp' => $interp, 'did_you_mean' => $didYouMean];
    }

    /** Retrouve une ville dans la requête (une faute de frappe est tolérée) et la retire du texte. */
    private function extractCity(string $norm, array &$interp): string
    {
        foreach ($this->cities as $c) {
            $name = Str::lower(Str::ascii($c['name']));
            // « Grand-Bassam » doit aussi se reconnaître écrit « grand bassam » : on quote chaque mot puis on les relie.
            $pattern = '/\b'.implode('[\s-]', array_map(fn ($part) => preg_quote($part, '/'), preg_split('/[\s-]+/', $name))).'\b/';
            if (preg_match($pattern, $norm)) {
                $interp['city'] = $c;

                return preg_replace($pattern, ' ', $norm);
            }
        }
        $words = preg_split('/[\s,;]+/', $norm, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($words as $w) {
            if (strlen($w) < 5) {
                continue;
            }
            foreach ($this->cities as $c) {
                foreach (preg_split('/[\s-]/', Str::lower(Str::ascii($c['name']))) as $part) {
                    if (strlen($part) >= 5 && levenshtein($w, $part) <= 1 && $w !== $part) {
                        $interp['city'] = $c;

                        return str_replace($w, ' ', $norm);
                    }
                }
            }
        }

        return $norm;
    }

    /** Rapproche un mot inconnu du vocabulaire (fautes de frappe courantes). */
    private function correct(string $t): string
    {
        if (strlen($t) < 4 || isset($this->vocab[$t]) || $this->isKnown($t)) {
            return $t;
        }
        $this->buildTrigramIndex();
        $tri = TextVectorizer::trigrams($t);
        $cands = [];
        foreach ($tri as $g) {
            foreach ($this->trigramIndex[$g] ?? [] as $w) {
                $cands[$w] = ($cands[$w] ?? 0) + 1;
            }
        }
        $best = null;
        $bestScore = 0.0;
        $special = array_flip(array_merge(array_keys(SmartSearch::TYPE_WORDS), array_merge(...SmartSearch::SYNONYMS)));
        foreach ($cands as $w => $shared) {
            $union = count($tri) + count(TextVectorizer::trigrams($w)) - $shared;
            $jac = $shared / max(1, $union);
            $lev = levenshtein($t, $w);
            if ($lev > max(1, (int) floor(strlen($t) / 4)) || $jac < 0.3) {
                continue;
            }
            $val = 1.0 / (1 + $lev) + (isset($special[$w]) ? 0.3 : 0.0) + 0.2 * $jac;
            if ($val > $bestScore) {
                [$bestScore, $best] = [$val, $w];
            }
        }

        return $best ?? $t;
    }

    private function isKnown(string $t): bool
    {
        if (isset(SmartSearch::TYPE_WORDS[$t]) || isset(SmartSearch::EN_FR[$t])) {
            return true;
        }
        foreach (SmartSearch::SYNONYMS as $g) {
            if (in_array($t, $g, true)) {
                return true;
            }
        }

        return false;
    }

    private function buildTrigramIndex(): void
    {
        if ($this->trigramIndex) {
            return;
        }
        $words = array_unique(array_merge(array_keys($this->vocab), array_merge(...SmartSearch::SYNONYMS), array_keys(SmartSearch::TYPE_WORDS)));
        foreach ($words as $w) {
            foreach (TextVectorizer::trigrams((string) $w) as $g) {
                $this->trigramIndex[$g][] = $w;
            }
        }
    }
}
