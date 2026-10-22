<?php

namespace App\Services\Ai;

use Illuminate\Support\Str;

/**
 * Représentation vectorielle des textes (TF-IDF, similarité cosinus).
 * Même rôle qu'un « embedding » : placer fiches et requêtes dans un même espace pour
 * mesurer leur proximité de sens. L'interface reste identique si l'on branche plus tard
 * un modèle d'embeddings externe : seuls tokens() et vector() changeraient.
 */
final class TextVectorizer
{
    private const STOPWORDS = ['les', 'des', 'une', 'dans', 'pour', 'avec', 'sur', 'sous', 'par', 'est', 'sont', 'qui', 'que', 'quoi', 'aux', 'ses', 'son', 'leur', 'leurs', 'vous', 'nous', 'plus', 'tres', 'tout', 'tous', 'toute', 'toutes', 'cette', 'cet', 'ces', 'etre', 'avoir', 'fait', 'faire', 'aussi', 'ainsi', 'dont', 'mais', 'comme', 'entre', 'vers', 'chez', 'sans', 'the', 'and', 'for'];

    /** @var array<string,float> */
    private array $idf = [];

    private float $defaultIdf = 1.0;

    /** @return string[] mots normalisés (minuscules, sans accents, pluriels simplifiés) */
    public static function tokens(string $text): array
    {
        $text = Str::lower(Str::ascii($text));
        preg_match_all('/[a-z0-9]{3,}/', $text, $m);
        $out = [];
        foreach ($m[0] as $w) {
            if (in_array($w, self::STOPWORDS, true)) {
                continue;
            }
            if (strlen($w) > 4 && str_ends_with($w, 's')) {
                $w = substr($w, 0, -1);
            }
            $out[] = $w;
        }

        return $out;
    }

    /** Fréquences pondérées : liste de [texte, poids] => [terme => tf] */
    public static function termFrequencies(array $fields): array
    {
        $tf = [];
        foreach ($fields as [$text, $weight]) {
            foreach (self::tokens((string) $text) as $t) {
                $tf[$t] = ($tf[$t] ?? 0) + $weight;
            }
        }

        return $tf;
    }

    /** Apprend l'IDF sur l'ensemble du catalogue. @param array<string,array<string,float>> $docs */
    public function fit(array $docs): void
    {
        $n = max(1, count($docs));
        $df = [];
        foreach ($docs as $tf) {
            foreach (array_keys($tf) as $t) {
                $df[$t] = ($df[$t] ?? 0) + 1;
            }
        }
        $this->idf = [];
        foreach ($df as $t => $c) {
            $this->idf[$t] = log(1 + $n / $c);
        }
        $this->defaultIdf = log(1 + $n);
    }

    public function exportIdf(): array
    {
        return ['idf' => $this->idf, 'default' => $this->defaultIdf];
    }

    public static function fromExport(array $data): self
    {
        $v = new self;
        $v->idf = $data['idf'];
        $v->defaultIdf = $data['default'];

        return $v;
    }

    /** Vecteur TF-IDF (tf sous-linéaire) normalisé. @param array<string,float> $tf */
    public function vector(array $tf): array
    {
        $vec = [];
        $norm = 0.0;
        foreach ($tf as $t => $f) {
            if ($f <= 0) {
                continue;
            }
            $w = (1 + log($f)) * ($this->idf[$t] ?? $this->defaultIdf);
            $vec[$t] = $w;
            $norm += $w * $w;
        }
        if ($norm > 0) {
            $norm = sqrt($norm);
            foreach ($vec as $t => $w) {
                $vec[$t] = $w / $norm;
            }
        }

        return $vec;
    }

    public static function cosine(array $a, array $b): float
    {
        if ($a === [] || $b === []) {
            return 0.0;
        }
        if (count($a) > count($b)) {
            [$a, $b] = [$b, $a];
        }
        $dot = 0.0;
        foreach ($a as $t => $w) {
            if (isset($b[$t])) {
                $dot += $w * $b[$t];
            }
        }

        return $dot;
    }

    /** Combinaison linéaire de vecteurs puis renormalisation. @param array<int,array{0:array,1:float}> $parts */
    public static function blend(array $parts): array
    {
        $sum = [];
        foreach ($parts as [$vec, $weight]) {
            foreach ($vec as $t => $w) {
                $sum[$t] = ($sum[$t] ?? 0) + $w * $weight;
            }
        }
        $norm = sqrt(array_sum(array_map(fn ($w) => $w * $w, $sum)));
        if ($norm > 0) {
            foreach ($sum as $t => $w) {
                $sum[$t] = $w / $norm;
            }
        }

        return $sum;
    }

    /** Trigrammes de caractères : tolérance aux fautes de frappe pour la recherche. */
    public static function trigrams(string $word): array
    {
        $w = '  '.Str::lower(Str::ascii($word)).' ';
        $out = [];
        for ($i = 0, $n = strlen($w) - 2; $i < $n; $i++) {
            $out[substr($w, $i, 3)] = true;
        }

        return array_keys($out);
    }
}
