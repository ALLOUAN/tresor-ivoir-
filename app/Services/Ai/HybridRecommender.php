<?php

namespace App\Services\Ai;

/**
 * Recommandation hybride.
 *
 *  1. Filtrage par contenu : similarité TF-IDF entre la fiche et le goût du visiteur
 *     (centres d'intérêt déclarés + fiches déjà consultées). Fonctionne sans historique.
 *  2. Règles métier : note, popularité, mise en avant, vérification, niveau d'abonnement,
 *     fraîcheur, proximité, adéquation au budget, actualité des événements.
 *  3. Filtrage collaboratif item-item, dès que l'historique le permet.
 *  4. Fusion pondérée : la part du collaboratif (alpha) grandit avec l'historique du visiteur
 *     et le volume d'acteurs actifs ; sinon le contenu et les règles portent le classement.
 *  5. Re-classement MMR (diversité) et créneau « découverte » pour les fiches peu exposées.
 */
class HybridRecommender
{
    private array $items;

    private array $vectors;

    private int $maxViews;

    private float $medianViews;

    private array $cities;

    private TextVectorizer $vectorizer;

    private array $cfg;

    private CollabModel $collab;

    public function __construct(Catalog $catalog, array $matrix = [], ?CollabModel $collab = null)
    {
        $snap = $catalog->snapshot();
        $this->items = $snap['items'];
        $this->vectors = $snap['vectors'];
        $this->maxViews = $snap['maxViews'];
        $this->medianViews = $snap['medianViews'];
        $this->cities = $snap['cities'];
        $this->vectorizer = $catalog->vectorizer();
        $this->cfg = config('ai.recommender');
        $this->collab = $collab ?? new CollabModel($matrix);
    }

    /** Part du collaboratif pour ce visiteur (0 sans historique, jusqu'au plafond configuré). */
    public function alpha(RecoContext $ctx): float
    {
        if (! $ctx->history || ! $this->collab->isUseful()) {
            return 0.0;
        }

        return $this->cfg['collab_max_share']
            * min(1.0, count($ctx->history) / $this->cfg['collab_full_history'])
            * min(1.0, $this->collab->activeActors() / $this->cfg['collab_full_actors']);
    }

    /**
     * @return array<int,array{item:CatalogItem,score:float,content:float,rules:float,collab:float,
     *                        reasons:string[],discovery:bool}>
     */
    public function recommend(RecoContext $ctx, int $k = 8): array
    {
        $alpha = $this->alpha($ctx);
        $city = $ctx->cityId ? ($this->cities[$ctx->cityId] ?? null) : null;
        $query = $this->queryVector($ctx);
        $hasContent = $query !== [];

        // Candidats
        $skip = array_flip($ctx->exclude);
        foreach (array_keys($ctx->history) as $key) {
            $skip[$key] = true;   // déjà vu : on propose du nouveau
        }
        $cands = [];
        foreach ($this->items as $key => $item) {
            if (isset($skip[$key]) || ($ctx->types && ! in_array($item->type, $ctx->types, true))) {
                continue;
            }
            if ($item->endsAt !== null && $item->endsAt < time()) {
                continue;
            }
            $cands[$key] = $item;
        }
        if (! $cands) {
            return [];
        }

        // Scores bruts
        $raw = [];
        $maxContent = 0.0;
        $maxCollab = 0.0;
        foreach ($cands as $key => $item) {
            $c = 0.0;
            if ($hasContent) {
                $c = TextVectorizer::cosine($query, $this->vectors[$key]);
                if ($ctx->interests) {
                    $c += 0.25 * count(array_intersect($ctx->interests, $item->tags)) / count($ctx->interests);
                }
            }
            $cf = $alpha > 0 ? $this->collab->score($ctx->history, $key) : 0.0;
            $maxContent = max($maxContent, $c);
            $maxCollab = max($maxCollab, $cf);
            $raw[$key] = [$c, $cf];
        }

        $scored = [];
        foreach ($cands as $key => $item) {
            [$c, $cf] = $raw[$key];
            $content = $maxContent > 0 ? min(1.0, $c / $maxContent) : 0.0;
            $collab = $maxCollab > 0 ? $cf / $maxCollab : 0.0;
            $rules = $this->rules($item, $ctx, $city);
            $base = ($hasContent && $maxContent > 0)
                ? $this->cfg['content_weight'] * $content + $this->cfg['rules_weight'] * $rules
                : $rules;
            $score = (1 - $alpha) * $base + $alpha * $collab;
            $scored[$key] = compact('score', 'content', 'rules', 'collab') + ['item' => $item];
        }
        uasort($scored, fn ($a, $b) => $b['score'] <=> $a['score']);

        $picked = $this->diversify($scored, $k);
        $picked = $this->discoverySlot($picked, $scored, $k);

        foreach ($picked as &$p) {
            $p['reasons'] = $this->reasons($p, $ctx, $city, $alpha);
        }

        return array_values($picked);
    }

    // ── Étapes ──────────────────────────────────────────────────────────────

    private function queryVector(RecoContext $ctx): array
    {
        $parts = [];
        foreach ($ctx->interests as $key) {
            $tf = [];
            foreach (Taxonomy::INTERESTS[$key]['keywords'] ?? [] as $kw) {
                foreach (TextVectorizer::tokens($kw) as $t) {
                    $tf[$t] = 1.0;
                }
            }
            if ($tf) {
                $parts[] = [$this->vectorizer->vector($tf), 1.0];
            }
        }
        if ($ctx->travelType && isset(Taxonomy::TRAVEL_TYPES[$ctx->travelType])) {
            $tf = [];
            foreach (Taxonomy::TRAVEL_TYPES[$ctx->travelType]['hints'] as $h) {
                foreach (TextVectorizer::tokens($h) as $t) {
                    $tf[$t] = 1.0;
                }
            }
            if ($tf) {
                $parts[] = [$this->vectorizer->vector($tf), 0.4];
            }
        }
        $hist = [];
        foreach ($ctx->history as $key => $w) {
            if (isset($this->vectors[$key])) {
                $hist[] = [$this->vectors[$key], $w];
            }
        }
        if ($hist) {
            $parts[] = [TextVectorizer::blend($hist), 0.8];
        }

        return $parts ? TextVectorizer::blend($parts) : [];
    }

    /** Règles métier, 0..1. */
    private function rules(CatalogItem $i, RecoContext $ctx, ?array $city): float
    {
        $n = $i->ratingCount;
        $bayes = ($i->rating * $n + $this->cfg['popularity_prior_rating'] * $this->cfg['popularity_prior_count']) / ($n + $this->cfg['popularity_prior_count']);
        $rating = $n > 0 ? $bayes / 5 : 0.5;
        $pop = $this->maxViews > 0 ? log(1 + $i->views) / log(1 + $this->maxViews) : 0.0;
        $age = $i->createdAt ? (time() - $i->createdAt) / 86400 : 365;
        $fresh = $age <= 45 ? 1.0 : max(0.0, 1 - ($age - 45) / 320);

        $r = 0.25 * $rating + 0.25 * $pop + 0.15 * ($i->featured ? 1 : 0) + 0.10 * ($i->verified ? 1 : 0)
            + 0.10 * $i->planLevel + 0.15 * $fresh;

        if ($ctx->budget && $i->price !== null) {
            $r *= 0.6 + 0.4 * $this->budgetFit($ctx->budget, $i->price);
        }
        if ($city && $i->hasGeo()) {
            $km = Geo::km($city['lat'], $city['lng'], $i->lat, $i->lng);
            if ($km !== null) {
                $r = 0.85 * $r + 0.15 * exp(-$km / 80);
            }
        }
        if ($i->type === 'event' && $i->startsAt) {
            $days = ($i->startsAt - time()) / 86400;
            if ($days <= 30) {
                $r = min(1.0, $r * 1.2);
            }
        }

        return min(1.0, $r);
    }

    private function budgetFit(string $budget, int $price): float
    {
        $c = Taxonomy::BUDGET_CEILING;

        return match ($budget) {
            'eco' => $price <= $c['eco'] ? 1.0 : ($price <= $c['moyen'] ? 0.4 : 0.1),
            'moyen' => $price <= $c['moyen'] ? 1.0 : ($price <= 150000 ? 0.6 : 0.3),
            'confort' => $price >= 60000 ? 1.0 : 0.7,
            default => 0.6,
        };
    }

    /** Re-classement MMR : pertinence moins redondance, avec un plafond par type de fiche. */
    private function diversify(array $scored, int $k): array
    {
        $lambda = $this->cfg['mmr_lambda'];
        $maxPer = $this->cfg['max_per_type'];
        $picked = [];
        $perType = [];
        $pool = array_slice($scored, 0, 40, true);

        while (count($picked) < $k && $pool) {
            $bestKey = null;
            $bestVal = -INF;
            foreach ($pool as $key => $row) {
                if (($perType[$row['item']->type] ?? 0) >= $maxPer) {
                    continue;
                }
                $redundancy = 0.0;
                foreach ($picked as $pk => $_) {
                    $redundancy = max($redundancy, TextVectorizer::cosine($this->vectors[$key], $this->vectors[$pk]));
                }
                $val = $lambda * $row['score'] - (1 - $lambda) * $redundancy;
                if ($val > $bestVal) {
                    [$bestVal, $bestKey] = [$val, $key];
                }
            }
            if ($bestKey === null) {
                break;
            }
            $picked[$bestKey] = $pool[$bestKey] + ['discovery' => false];
            $perType[$pool[$bestKey]['item']->type] = ($perType[$pool[$bestKey]['item']->type] ?? 0) + 1;
            unset($pool[$bestKey]);
        }

        return $picked;
    }

    /**
     * Exposition minimale : si aucune fiche peu vue n'a été retenue, la meilleure d'entre elles
     * (pertinence suffisante) prend un créneau près de la fin de liste.
     */
    private function discoverySlot(array $picked, array $scored, int $k): array
    {
        if (count($picked) < 4) {
            return $picked;
        }
        foreach ($picked as $p) {
            if ($p['item']->views <= $this->medianViews) {
                return $picked;
            }
        }
        $top = reset($picked)['score'];
        foreach ($scored as $key => $row) {
            if (isset($picked[$key]) || $row['item']->views > $this->medianViews || $row['score'] < 0.35 * $top) {
                continue;
            }
            $keys = array_keys($picked);
            $victim = $keys[max(0, count($keys) - 2)];
            $new = [];
            foreach ($picked as $pk => $pv) {
                $new[$pk === $victim ? $key : $pk] = $pk === $victim ? $row + ['discovery' => true] : $pv;
            }

            return $new;
        }

        return $picked;
    }

    /** @return string[] deux raisons au plus, formulées pour le visiteur */
    private function reasons(array $p, RecoContext $ctx, ?array $city, float $alpha): array
    {
        $i = $p['item'];
        $out = [];

        $shared = array_values(array_intersect($ctx->interests, $i->tags));
        if ($shared) {
            $out[] = 'Correspond à votre goût : '.Taxonomy::INTERESTS[$shared[0]]['label'];
        }
        if ($ctx->history) {
            $best = null;
            $bestSim = 0.25;
            foreach ($ctx->history as $hk => $_) {
                if (isset($this->vectors[$hk], $this->vectors[$i->key])) {
                    $s = TextVectorizer::cosine($this->vectors[$hk], $this->vectors[$i->key]);
                    if ($s > $bestSim) {
                        [$bestSim, $best] = [$s, $hk];
                    }
                }
            }
            if ($best && isset($this->items[$best])) {
                $out[] = 'Proche de « '.$this->items[$best]->title.' » que vous avez consulté';
            }
        }
        if ($alpha > 0 && $p['collab'] >= 0.5) {
            $out[] = 'Apprécié par des visiteurs aux goûts proches des vôtres';
        }
        if ($p['discovery']) {
            $out[] = 'À découvrir : une adresse encore peu connue';
        }
        if ($ctx->budget && $i->price !== null && $this->budgetFit($ctx->budget, $i->price) >= 1.0) {
            $out[] = 'Dans votre budget';
        }
        if ($city && $i->hasGeo() && ($km = Geo::km($city['lat'], $city['lng'], $i->lat, $i->lng)) !== null && $km <= 40) {
            $out[] = 'À proximité de '.$city['name'];
        }
        if ($i->ratingCount > 0 && $i->rating >= 4.0) {
            $out[] = 'Noté '.number_format($i->rating, 1, ',', '').'/5 par les visiteurs';
        }
        if ($i->type === 'event' && $i->startsAt && ($i->startsAt - time()) / 86400 <= 30) {
            $out[] = 'Bientôt : '.\Illuminate\Support\Carbon::createFromTimestamp($i->startsAt)->locale('fr')->translatedFormat('j F');
        }
        if ($i->createdAt && (time() - $i->createdAt) / 86400 <= 45) {
            $out[] = 'Nouveau sur la plateforme';
        }
        if ($i->featured) {
            $out[] = 'Mis en avant par la rédaction';
        }
        if (! $out) {
            $out[] = 'Populaire en Côte d\'Ivoire';
        }

        return array_slice(array_values(array_unique($out)), 0, 2);
    }
}
