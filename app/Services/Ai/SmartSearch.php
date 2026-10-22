<?php

namespace App\Services\Ai;

use Illuminate\Support\Str;

/**
 * Recherche intelligente en langage naturel (français et anglais) sur le catalogue.
 *
 *  - Compréhension de la demande : ville, type de lieu, intention de prix, synonymes.
 *  - Tolérance aux fautes : un mot inconnu est rapproché du vocabulaire du catalogue
 *    (trigrammes de caractères + distance de Levenshtein), avec « Vouliez-vous dire ».
 *  - Classement par similarité TF-IDF (même index vectoriel que la recommandation),
 *    corrigé par des bonus de titre, de ville, de type et de budget.
 * Les filtres existants du site (région, ville, budget) restent complémentaires.
 */
class SmartSearch
{
    /** Groupes de synonymes (formes déjà normalisées : minuscules, sans accents, sans pluriel). */
    public const SYNONYMS = [
        ['mer', 'plage', 'littoral', 'ocean', 'bord', 'balneaire', 'sable', 'lagune'],
        ['calme', 'tranquille', 'detente', 'repos', 'paisible', 'serein', 'relaxant'],
        ['manger', 'restaurant', 'maquis', 'cuisine', 'gastronomie', 'repas', 'diner', 'dejeuner', 'culinaire'],
        ['dormir', 'hebergement', 'hotel', 'residence', 'logement', 'chambre', 'sejourner', 'auberge', 'lodge'],
        ['visiter', 'decouvrir', 'site', 'visite', 'excursion', 'balade'],
        ['musee', 'exposition', 'galerie', 'patrimoine', 'histoire', 'historique'],
        ['cascade', 'chute', 'riviere', 'lac'],
        ['nature', 'parc', 'foret', 'reserve', 'faune', 'flore', 'randonnee'],
        ['fete', 'festival', 'evenement', 'celebration', 'spectacle', 'ceremonie'],
        ['artisanat', 'artisan', 'atelier', 'creation', 'sculpture', 'tissage', 'pagne'],
        ['transport', 'taxi', 'vtc', 'transfert', 'navette', 'voiture', 'bus'],
        ['agence', 'voyage', 'tour', 'circuit', 'guide'],
        ['compte', 'inscription', 'inscrire', 'creer', 'ouvrir', 'enregistrer', 'profil', 'connexion'],
    ];

    /** Anglais → français (mots courants du tourisme). */
    public const EN_FR = [
        'hotel' => 'hotel', 'hotels' => 'hotel', 'stay' => 'dormir', 'sleep' => 'dormir', 'room' => 'chambre', 'rooms' => 'chambre', 'lodging' => 'hebergement',
        'accommodation' => 'hebergement', 'beach' => 'plage', 'beaches' => 'plage', 'sea' => 'mer', 'ocean' => 'ocean', 'lagoon' => 'lagune', 'eat' => 'manger',
        'food' => 'cuisine', 'dinner' => 'diner', 'lunch' => 'dejeuner', 'restaurant' => 'restaurant', 'restaurants' => 'restaurant', 'museum' => 'musee',
        'museums' => 'musee', 'waterfall' => 'cascade', 'waterfalls' => 'cascade', 'park' => 'parc', 'forest' => 'foret', 'mountain' => 'montagne',
        'mountains' => 'montagne', 'quiet' => 'calme', 'peaceful' => 'calme', 'relax' => 'detente', 'cheap' => 'economique', 'affordable' => 'economique',
        'budget' => 'economique', 'luxury' => 'luxe', 'visit' => 'visiter', 'tour' => 'circuit', 'tours' => 'circuit', 'festival' => 'festival', 'event' => 'evenement',
        'events' => 'evenement', 'craft' => 'artisanat', 'crafts' => 'artisanat', 'handicraft' => 'artisanat', 'village' => 'village', 'culture' => 'culture',
        'heritage' => 'patrimoine', 'history' => 'histoire', 'taxi' => 'taxi', 'transport' => 'transport', 'travel' => 'voyage', 'agency' => 'agence',
        'near' => 'pres', 'wildlife' => 'faune', 'hiking' => 'randonnee', 'nightlife' => 'nuit', 'account' => 'compte', 'register' => 'inscrire', 'cathedral' => 'basilique', 'church' => 'basilique',
    ];

    /** Mots d'intention de type de fiche => type du catalogue. */
    public const TYPE_WORDS = [
        'hotel' => 'accommodation', 'hebergement' => 'accommodation', 'residence' => 'accommodation', 'dormir' => 'accommodation', 'chambre' => 'accommodation', 'logement' => 'accommodation', 'lodge' => 'accommodation',
        'restaurant' => 'restaurant', 'maquis' => 'restaurant', 'manger' => 'restaurant', 'diner' => 'restaurant', 'dejeuner' => 'restaurant',
        'evenement' => 'event', 'festival' => 'event', 'fete' => 'event',
        'transport' => 'transport', 'taxi' => 'transport', 'vtc' => 'transport', 'transfert' => 'transport',
        'agence' => 'agency', 'circuit' => 'agency',
    ];

    public const CHEAP = ['economique', 'abordable', 'bon marche', 'petit budget', 'pas cher', 'peu cher'];

    public const LUXE = ['luxe', 'luxueux', 'standing', 'prestige', 'haut de gamme', 'premium', '5 etoiles', 'cinq etoiles'];

    /** Mots trop génériques pour compter comme intention de recherche. */
    public const FILLERS = ['pres', 'proche', 'ou', 'quel', 'quelle', 'quels', 'quelles', 'meilleur', 'meilleure', 'bon', 'bonne', 'cherche', 'chercher', 'trouver', 'avec', 'dans', 'aux', 'where', 'what', 'best', 'good', 'near', 'around', 'looking', 'les', 'des', 'une', 'pour', 'que', 'qui', 'est', 'mon', 'ses', 'sur', 'the', 'and', 'for', 'with', 'you', 'can', 'find'];

    private array $items;

    private array $vectors;

    private array $cities;

    private array $vocab;

    private TextVectorizer $vectorizer;

    private QueryAnalyzer $analyzer;

    public function __construct(private readonly Catalog $catalog)
    {
        $snap = $catalog->snapshot();
        $this->items = $snap['items'];
        $this->vectors = $snap['vectors'];
        $this->cities = $snap['cities'];
        $this->vocab = array_keys($snap['idf']);
        $this->vectorizer = $catalog->vectorizer();
        $this->analyzer = new QueryAnalyzer($this->vocab, $this->cities);
    }

    /**
     * @return array{results: array<int,array{item:CatalogItem,score:float,why:string[]}>,
     *               interpreted: array<string,mixed>, did_you_mean: ?string}
     */
    /** @var array<string,array> mémo par requête : la page de recherche interroge deux fois le même moteur */
    private static array $memo = [];

    public function search(string $query, int $k = 8): array
    {
        $key = $k.'|'.$query;

        return self::$memo[$key] ??= $this->compute($query, $k);
    }

    private function compute(string $query, int $k): array
    {
        $a = $this->analyzer->analyze($query);
        $interp = $a['interp'];
        $tokens = $a['tokens'];
        $didYouMean = $a['did_you_mean'];

        if (! $tokens && ! $interp['city']) {
            return ['results' => [], 'interpreted' => $interp, 'did_you_mean' => $didYouMean];
        }
        $q = $a['tf'] ? $this->vectorizer->vector($a['tf']) : [];

        // 5. Notation
        $scored = [];
        foreach ($this->items as $key => $item) {
            if ($item->endsAt !== null && $item->endsAt < time()) {
                continue;
            }
            $cos = $q ? TextVectorizer::cosine($q, $this->vectors[$key]) : 0.0;
            $titleTokens = TextVectorizer::tokens($item->title);
            $titleHits = count(array_intersect($tokens, $titleTokens));
            $score = 0.8 * $cos + ($tokens ? 0.25 * $titleHits / count($tokens) : 0.0);

            $why = [];
            if ($titleHits) {
                $why[] = 'Le nom correspond à votre recherche';
            }
            if ($interp['city']) {
                $inCity = $this->inCity($item, $interp['city']);
                if ($inCity) {
                    $score = $score * 1.35 + ($tokens ? 0.05 : 0.5);
                    $why[] = 'Situé à '.$interp['city']['name'];
                } else {
                    $score *= 0.35;
                }
            }
            if ($interp['type']) {
                if ($item->type === $interp['type']) {
                    $score = $score * 1.4 + 0.1;
                    $why[] = 'Type de lieu recherché : '.$item->typeLabel;
                } else {
                    $score *= 0.7;
                }
            }
            if ($interp['price'] === 'eco' && $item->price !== null) {
                if ($item->price <= Taxonomy::BUDGET_CEILING['eco']) {
                    $score *= 1.25;
                    $why[] = 'Petit prix (dès '.number_format($item->price, 0, ',', ' ').' FCFA)';
                } else {
                    $score *= 0.7;
                }
            } elseif ($interp['price'] === 'luxe' && $item->price !== null) {
                if ($item->price >= 60000 || $item->planLevel >= 1.0) {
                    $score *= 1.25;
                    $why[] = 'Haut de gamme';
                } else {
                    $score *= 0.75;
                }
            }
            $shared = array_values(array_filter($tokens, fn ($t) => isset($this->vectors[$key][$t])));
            if ($shared && ! $titleHits) {
                $why[] = 'Parle de : '.implode(', ', array_slice($shared, 0, 3));
            }
            if ($item->ratingCount > 0) {
                $score *= 1 + min(0.1, ($item->rating / 5) * 0.1);
            }
            if ($score >= 0.08 && ($cos > 0 || $titleHits > 0 || ($interp["city"] && ! $tokens))) {
                $scored[$key] = ['item' => $item, 'score' => round($score, 4), 'why' => array_slice(array_values(array_unique($why)), 0, 2)];
            }
        }
        uasort($scored, fn ($a, $b) => $b['score'] <=> $a['score']);

        return ['results' => array_slice(array_values($scored), 0, $k), 'interpreted' => $interp, 'did_you_mean' => $didYouMean];
    }

    private function inCity(CatalogItem $i, array $city): bool
    {
        if ($i->cityId === $city['id']) {
            return true;
        }
        if ($i->city && Str::lower(Str::ascii($i->city)) === Str::lower(Str::ascii($city['name']))) {
            return true;
        }
        if ($i->hasGeo() && $city['lat'] !== null) {
            return (Geo::km($city['lat'], $city['lng'], $i->lat, $i->lng) ?? INF) <= 20;
        }

        return false;
    }

}
