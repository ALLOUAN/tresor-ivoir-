<?php

namespace App\Services\Ai;

/**
 * Vocabulaire commun aux modules IA : centres d'intérêt, budgets, types de voyage.
 * Les mots-clés servent à étiqueter les fiches et à traduire le profil d'un visiteur
 * en requête (filtrage par contenu).
 */
final class Taxonomy
{
    public const INTERESTS = [
        'culture' => [
            'label' => 'Culture & traditions', 'icon' => 'fa-masks-theater',
            'keywords' => ['culture', 'culturel', 'tradition', 'traditionnel', 'patrimoine', 'musee', 'monument', 'histoire', 'historique', 'ethnie', 'village', 'masque', 'ceremonie', 'rituel', 'basilique', 'unesco', 'colonial', 'festival', 'sacre'],
        ],
        'nature' => [
            'label' => 'Nature & parcs', 'icon' => 'fa-tree',
            'keywords' => ['nature', 'parc', 'reserve', 'foret', 'cascade', 'montagne', 'lac', 'faune', 'flore', 'chimpanze', 'elephant', 'randonnee', 'ecotourisme', 'savane', 'grotte', 'riviere', 'naturel'],
        ],
        'gastronomie' => [
            'label' => 'Gastronomie', 'icon' => 'fa-utensils',
            'keywords' => ['gastronomie', 'restaurant', 'maquis', 'cuisine', 'plat', 'attieke', 'kedjenou', 'braise', 'saveur', 'menu', 'boisson', 'bissap', 'poisson', 'alloco', 'degustation', 'culinaire'],
        ],
        'plage' => [
            'label' => 'Plages & détente', 'icon' => 'fa-umbrella-beach',
            'keywords' => ['plage', 'lagune', 'mer', 'ocean', 'sable', 'littoral', 'balneaire', 'cocotier', 'baignade', 'detente', 'spa', 'piscine', 'resort', 'bord'],
        ],
        'aventure' => [
            'label' => 'Aventure & sport', 'icon' => 'fa-person-hiking',
            'keywords' => ['aventure', 'sport', 'kayak', 'surf', 'trek', 'escalade', 'quad', 'safari', 'plongee', 'velo', 'camping', 'activite', 'excursion', 'pirogue'],
        ],
        'sorties' => [
            'label' => 'Sorties & loisirs', 'icon' => 'fa-champagne-glasses',
            'keywords' => ['nuit', 'nocturne', 'divertissement', 'loisir', 'concert', 'musique', 'fete', 'club', 'bar', 'zoo', 'spectacle', 'soiree', 'festival', 'danse'],
        ],
        'artisanat' => [
            'label' => 'Art & artisanat', 'icon' => 'fa-palette',
            'keywords' => ['artisanat', 'artisan', 'sculpture', 'tissage', 'pagne', 'galerie', 'creation', 'peinture', 'poterie', 'bijou', 'atelier', 'marche', 'art'],
        ],
    ];

    public const BUDGETS = [
        'eco' => 'Petit budget',
        'moyen' => 'Budget moyen',
        'confort' => 'Confort',
    ];

    public const TRAVEL_TYPES = [
        'solo' => ['label' => 'Seul(e)', 'hints' => ['decouverte', 'randonnee', 'musee', 'culture']],
        'couple' => ['label' => 'En couple', 'hints' => ['romantique', 'plage', 'spa', 'vue', 'detente']],
        'famille' => ['label' => 'En famille', 'hints' => ['famille', 'enfant', 'parc', 'zoo', 'plage', 'piscine']],
        'amis' => ['label' => 'Entre amis', 'hints' => ['festival', 'nuit', 'aventure', 'sport', 'soiree']],
        'affaires' => ['label' => "Voyage d'affaires", 'hints' => ['affaire', 'conference', 'seminaire', 'wifi', 'centre']],
    ];

    /** Étiquettes d'office selon le type de fiche (même sans mot-clé explicite). */
    public const TYPE_PRIORS = [
        'restaurant' => ['gastronomie'],
        'leisure' => ['sorties'],
        'experience' => ['aventure'],
    ];

    /** Plafond de prix (XOF) au-delà duquel une fiche sort de chaque gamme de budget. */
    public const BUDGET_CEILING = ['eco' => 30000, 'moyen' => 90000];

    public static function interestKeys(): array
    {
        return array_keys(self::INTERESTS);
    }

    public static function cleanInterests(mixed $values): array
    {
        return array_values(array_intersect(self::interestKeys(), array_map('strval', (array) $values)));
    }
}
