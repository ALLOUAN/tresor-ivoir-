<?php

namespace App\Services\Ai;

/**
 * Filtrage collaboratif « item-item » : deux fiches sont proches quand les mêmes acteurs
 * les ont consultées / aimées / réservées (similarité cosinus sur la matrice acteur × fiche).
 * « Les visiteurs qui ont aimé ceci ont aussi aimé cela ».
 */
final class CollabModel
{
    /** @var array<string,array<string,float>> fiche => [acteur => poids] */
    private array $byItem = [];

    /** @var array<string,float> */
    private array $norm = [];

    private array $simCache = [];

    private int $activeActors = 0;

    /** @param array<string,array<string,float>> $matrix acteur => [fiche => poids] */
    public function __construct(array $matrix)
    {
        foreach ($matrix as $actor => $items) {
            if (count($items) >= 2) {
                $this->activeActors++;
            }
            foreach ($items as $key => $w) {
                $this->byItem[$key][$actor] = $w;
            }
        }
        foreach ($this->byItem as $key => $actors) {
            $this->norm[$key] = sqrt(array_sum(array_map(fn ($w) => $w * $w, $actors)));
        }
    }

    public function activeActors(): int
    {
        return $this->activeActors;
    }

    public function isUseful(): bool
    {
        return $this->activeActors >= 2;
    }

    public function similarity(string $a, string $b): float
    {
        if ($a === $b || ! isset($this->byItem[$a], $this->byItem[$b])) {
            return 0.0;
        }
        $ck = $a < $b ? $a.'|'.$b : $b.'|'.$a;
        if (isset($this->simCache[$ck])) {
            return $this->simCache[$ck];
        }
        $x = $this->byItem[$a];
        $y = $this->byItem[$b];
        if (count($x) > count($y)) {
            [$x, $y] = [$y, $x];
        }
        $dot = 0.0;
        foreach ($x as $actor => $w) {
            if (isset($y[$actor])) {
                $dot += $w * $y[$actor];
            }
        }
        $den = $this->norm[$a] * $this->norm[$b];

        return $this->simCache[$ck] = $den > 0 ? $dot / $den : 0.0;
    }

    /** Score d'une fiche candidate d'après l'historique pondéré d'un acteur. @param array<string,float> $history */
    public function score(array $history, string $candidate): float
    {
        $num = 0.0;
        $den = 0.0;
        foreach ($history as $key => $w) {
            $den += $w;
            $num += $w * $this->similarity($key, $candidate);
        }

        return $den > 0 ? $num / $den : 0.0;
    }
}
