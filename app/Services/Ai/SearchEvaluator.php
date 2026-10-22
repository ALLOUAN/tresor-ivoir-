<?php

namespace App\Services\Ai;

use Illuminate\Support\Str;

/**
 * Évaluation de la recherche intelligente sur un jeu de requêtes annotées : pour chaque requête,
 * on décrit à l'avance quelles fiches sont pertinentes (règle objective sur le type, la ville,
 * les étiquettes). La recherche intelligente est comparée à la recherche par mots-clés actuelle
 * du site (la requête entière doit apparaître telle quelle dans le nom, la ville ou la description).
 */
class SearchEvaluator
{
    public function __construct(private readonly SmartSearch $smart, private readonly Catalog $catalog) {}

    /** @return array<int,array{q:string,note:string,rel:callable}> */
    public static function queries(): array
    {
        $city = fn (CatalogItem $i, string $name) => $i->city && Str::lower(Str::ascii($i->city)) === Str::lower(Str::ascii($name));
        $type = fn (CatalogItem $i, string ...$t) => in_array($i->type, $t, true);

        return [
            ['q' => 'hôtel calme près de la mer à Assinie', 'note' => 'langage naturel', 'rel' => fn ($i) => $city($i, 'Assinie') && $type($i, 'accommodation')],
            ['q' => 'où manger à Abidjan', 'note' => 'langage naturel', 'rel' => fn ($i) => $city($i, 'Abidjan') && $type($i, 'restaurant')],
            ['q' => 'plage à Grand-Bassam', 'note' => 'langage naturel', 'rel' => fn ($i) => ($city($i, 'Grand-Bassam') && in_array('plage', $i->tags, true)) || ($city($i, 'Grand-Bassam') && $type($i, 'accommodation'))],
            ['q' => 'hôtel pas cher à Man', 'note' => 'intention de prix', 'rel' => fn ($i) => $city($i, 'Man') && $type($i, 'accommodation') && $i->price !== null && $i->price <= 30000],
            ['q' => 'logement économique à Yamoussoukro', 'note' => 'synonymes + prix', 'rel' => fn ($i) => $city($i, 'Yamoussoukro') && $type($i, 'accommodation') && $i->price !== null && $i->price <= 30000],
            ['q' => 'musée à Abidjan', 'note' => 'simple', 'rel' => fn ($i) => $city($i, 'Abidjan') && str_contains(Str::lower(Str::ascii($i->title)), 'musee')],
            ['q' => 'cascades de Man', 'note' => 'simple', 'rel' => fn ($i) => str_contains(Str::lower(Str::ascii($i->title)), 'cascade')],
            ['q' => 'artisanat à Korhogo', 'note' => 'sans mot exact dans le nom', 'rel' => fn ($i) => $city($i, 'Korhogo') && in_array('artisanat', $i->tags, true)],
            ['q' => 'basilique de Yamoussoukro', 'note' => 'simple', 'rel' => fn ($i) => str_contains(Str::lower(Str::ascii($i->title)), 'basilique')],
            ['q' => 'transport VTC', 'note' => 'simple', 'rel' => fn ($i) => $type($i, 'transport') && str_contains(Str::lower($i->title), 'vtc')],
            ['q' => 'agence de voyage', 'note' => 'simple', 'rel' => fn ($i) => $type($i, 'agency')],
            ['q' => 'visiter un parc national', 'note' => 'langage naturel', 'rel' => fn ($i) => str_contains(Str::lower(Str::ascii($i->title)), 'parc national')],
            ['q' => 'hotl plage Bassm', 'note' => 'fautes de frappe', 'rel' => fn ($i) => $city($i, 'Grand-Bassam') && ($type($i, 'accommodation') || in_array('plage', $i->tags, true))],
            ['q' => 'restaurent abidjan', 'note' => 'fautes de frappe', 'rel' => fn ($i) => $city($i, 'Abidjan') && $type($i, 'restaurant')],
            ['q' => 'muse des civilisations', 'note' => 'fautes de frappe', 'rel' => fn ($i) => str_contains(Str::lower(Str::ascii($i->title)), 'civilisations')],
            ['q' => 'where to stay in Yamoussoukro', 'note' => 'anglais', 'rel' => fn ($i) => $city($i, 'Yamoussoukro') && $type($i, 'accommodation')],
            ['q' => 'beach in Assinie', 'note' => 'anglais', 'rel' => fn ($i) => $city($i, 'Assinie')],
            ['q' => 'best restaurants Abidjan', 'note' => 'anglais', 'rel' => fn ($i) => $city($i, 'Abidjan') && $type($i, 'restaurant')],
            ['q' => 'poisson braisé', 'note' => 'plat', 'rel' => fn ($i) => $type($i, 'restaurant')],
            ['q' => 'culture et patrimoine UNESCO', 'note' => 'thématique', 'rel' => fn ($i) => in_array('culture', $i->tags, true) && (str_contains(Str::lower($i->title), 'unesco') || str_contains(Str::lower(Str::ascii($i->title)), 'patrimoine') || $type($i, 'site'))],
            ['q' => 'randonnée nature à Man', 'note' => 'thématique', 'rel' => fn ($i) => $city($i, 'Man') && in_array('nature', $i->tags, true)],
            ['q' => 'fête traditionnelle', 'note' => 'thématique', 'rel' => fn ($i) => $type($i, 'event')],
        ];
    }

    /**
     * Jeu de VALIDATION : requêtes écrites après coup, jamais utilisées pour régler le moteur.
     * Il donne une mesure moins optimiste que le jeu principal (réglé en regardant ses résultats).
     *
     * @return array<int,array{q:string,note:string,rel:callable}>
     */
    public static function heldOut(): array
    {
        $city = fn (CatalogItem $i, string $name) => $i->city && Str::lower(Str::ascii($i->city)) === Str::lower(Str::ascii($name));
        $type = fn (CatalogItem $i, string ...$t) => in_array($i->type, $t, true);
        $title = fn (CatalogItem $i, string $needle) => str_contains(Str::lower(Str::ascii($i->title)), $needle);

        return [
            ['q' => 'dormir pas cher à Korhogo', 'note' => 'prix + synonyme', 'rel' => fn ($i) => $city($i, 'Korhogo') && $type($i, 'accommodation')],
            ['q' => 'séjour au bord de la lagune', 'note' => 'thématique', 'rel' => fn ($i) => isset($i->tf['lagune']) && in_array($i->city, ['Grand-Bassam', 'Assinie', 'Abidjan'], true)],
            ['q' => 'restaurant gastronomique Cocody', 'note' => 'quartier', 'rel' => fn ($i) => $type($i, 'restaurant') && $title($i, 'cocody')],
            ['q' => 'danseurs traditionnels', 'note' => 'sans ville', 'rel' => fn ($i) => $title($i, 'danseurs')],
            ['q' => 'tisserands sénoufo', 'note' => 'sans ville', 'rel' => fn ($i) => $title($i, 'tisserands')],
            ['q' => 'lacs sacrés crocodiles', 'note' => 'ordre des mots', 'rel' => fn ($i) => $title($i, 'crocodiles')],
            ['q' => 'marché de Treichville', 'note' => 'simple', 'rel' => fn ($i) => $title($i, 'treichville')],
            ['q' => 'resort de luxe à Assinie', 'note' => 'prix + ville', 'rel' => fn ($i) => $city($i, 'Assinie') && $type($i, 'accommodation')],
            ['q' => 'Quartier Frnace patrimoine', 'note' => 'faute de frappe', 'rel' => fn ($i) => $title($i, 'quartier france')],
            ['q' => 'hôtel avec piscine à Abidjan', 'note' => 'équipement', 'rel' => fn ($i) => $city($i, 'Abidjan') && $type($i, 'accommodation') && isset($i->tf['piscine'])],
            ['q' => 'accommodation near the beach in Bassam', 'note' => 'anglais', 'rel' => fn ($i) => $city($i, 'Grand-Bassam') && $type($i, 'accommodation')],
            ['q' => 'visite guidée', 'note' => 'intention', 'rel' => fn ($i) => $type($i, 'experience', 'agency')],
            ['q' => 'parc de Taï chimpanzés', 'note' => 'espèce', 'rel' => fn ($i) => $title($i, 'tai')],
            ['q' => 'taxis Abidjan', 'note' => 'type + ville', 'rel' => fn ($i) => $type($i, 'transport')],
            ['q' => 'dormir à Mann', 'note' => 'faute sur la ville', 'rel' => fn ($i) => $city($i, 'Man') && $type($i, 'accommodation')],
        ];
    }

    /**
     * @return array{queries:int, systems:array<string,array<string,float>>, details:array<int,array<string,mixed>>, no_result_logged:int, logged:int}
     */
    public function run(int $k = 3, bool $heldOut = false): array
    {
        $items = $this->catalog->items();
        $acc = ['mots_cles' => $this->blank(), 'intelligente' => $this->blank()];
        $details = [];

        foreach (($heldOut ? self::heldOut() : self::queries()) as $row) {
            $relevant = array_keys(array_filter($items, fn ($i) => ($row['rel'])($i)));
            if (! $relevant) {
                continue;   // aucune fiche pertinente dans le catalogue actuel : requête écartée
            }
            $lists = [
                'mots_cles' => $this->keyword($row['q'], $items),
                'intelligente' => array_map(fn ($r) => $r['item']->key, $this->smart->search($row['q'], 10)['results']),
            ];
            $line = ['q' => $row['q'], 'note' => $row['note'], 'relevant' => count($relevant)];
            foreach ($lists as $name => $keys) {
                $a = &$acc[$name];
                $a['n']++;
                $rank = null;
                foreach ($keys as $pos => $key) {
                    if (in_array($key, $relevant, true)) {
                        $rank = $pos + 1;
                        break;
                    }
                }
                $a['hit1'] += $rank === 1 ? 1 : 0;
                $a['hit3'] += ($rank !== null && $rank <= $k) ? 1 : 0;
                $a['mrr'] += $rank ? 1 / $rank : 0;
                $top = array_slice($keys, 0, $k);
                $a['p3'] += $top ? count(array_intersect($top, $relevant)) / $k : 0;
                $a['zero'] += $keys ? 0 : 1;
                $line[$name] = $rank;
                unset($a);
            }
            $details[] = $line;
        }

        $systems = [];
        foreach ($acc as $name => $a) {
            $n = max(1, $a['n']);
            $systems[$name] = [
                'hit1' => round($a['hit1'] / $n, 4), 'hit3' => round($a['hit3'] / $n, 4), 'mrr' => round($a['mrr'] / $n, 4),
                'precision3' => round($a['p3'] / $n, 4), 'sans_resultat' => round($a['zero'] / $n, 4),
            ];
        }

        $logged = \App\Support\SchemaCache::hasTable('ai_search_logs') ? (int) \Illuminate\Support\Facades\DB::table('ai_search_logs')->count() : 0;
        $zero = $logged ? (int) \Illuminate\Support\Facades\DB::table('ai_search_logs')->where('smart_results', 0)->where('keyword_results', 0)->count() : 0;

        return ['queries' => $acc['intelligente']['n'], 'systems' => $systems, 'details' => $details, 'logged' => $logged, 'no_result_logged' => $zero];
    }

    private function blank(): array
    {
        return ['n' => 0, 'hit1' => 0, 'hit3' => 0, 'mrr' => 0.0, 'p3' => 0.0, 'zero' => 0];
    }

    /** Reproduit la logique du site : la requête entière doit figurer dans le nom, la ville ou la description. */
    private function keyword(string $q, array $items): array
    {
        $needle = Str::lower(trim($q));
        $out = [];
        foreach ($items as $key => $i) {
            $hay = Str::lower($i->title.' '.($i->city ?? '').' '.$i->excerpt.' '.$i->category);
            $single = count(TextVectorizer::tokens($q)) === 1 ? TextVectorizer::tokens($q)[0] : null;
            // Un seul mot : on le cherche aussi dans tout le texte de la fiche (comme le LIKE du site sur la description).
            if (str_contains($hay, $needle) || ($single !== null && isset($i->tf[$single]))) {
                $out[] = $key;
            }
        }

        return $out;
    }
}
