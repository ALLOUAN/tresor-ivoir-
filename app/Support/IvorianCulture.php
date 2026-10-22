<?php

namespace App\Support;

use App\Models\TouristCity;

/**
 * Aire culturelle d'une ville ivoirienne, pour habiller ses pages (partials.ivorian-motifs).
 * Regroupement volontairement large : on décrit la culture majoritaire de la région, pas une appartenance
 * exclusive. Chaque aire a ses motifs (dessinés en SVG), un libellé et une mention explicative.
 */
class IvorianCulture
{
    /** slug de ville => clé d'aire culturelle */
    private const CITY_AREA = [
        // Sénoufo (dont Tagbana à Katiola)
        'korhogo' => 'senoufo', 'ferkessedougou' => 'senoufo', 'boundiali' => 'senoufo', 'katiola' => 'senoufo',
        // Baoulé
        'bouake' => 'baoule', 'yamoussoukro' => 'baoule', 'dimbokro' => 'baoule', 'toumodi' => 'baoule',
        // Akan de l'Est : Agni, Abron
        'abengourou' => 'akan', 'bondoukou' => 'akan', 'aboisso' => 'akan', 'bongouanou' => 'akan', 'daoukro' => 'akan',
        // Peuples lagunaires du Sud
        'abidjan' => 'lagunaire', 'bingerville' => 'lagunaire', 'grand-bassam' => 'lagunaire', 'assinie' => 'lagunaire',
        'jacqueville' => 'lagunaire', 'dabou' => 'lagunaire', 'grand-lahou' => 'lagunaire', 'adzope' => 'lagunaire', 'agboville' => 'lagunaire',
        // Krou : littoral et forêt
        'san-pedro' => 'krou-cote', 'sassandra' => 'krou-cote',
        'daloa' => 'krou', 'gagnoa' => 'krou', 'soubre' => 'krou', 'divo' => 'krou',
        // Ouest montagneux : Dan (Yacouba) et Wè (Guéré, Wobé)
        'man' => 'dan', 'danane' => 'dan', 'guiglo' => 'dan', 'duekoue' => 'dan',
        // Gouro
        'bouafle' => 'gouro',
        // Mandé du Nord-Ouest : Malinké, Dioula
        'odienne' => 'mande', 'minignan' => 'mande', 'seguela' => 'mande', 'mankono' => 'mande', 'touba' => 'mande', 'kong' => 'mande',
        // Nord-Est : Lobi, Koulango
        'bouna' => 'lobi',
    ];

    private const AREAS = [
        'senoufo' => ['label' => 'Pays sénoufo', 'credit' => 'Motifs inspirés des toiles de Korhogo, peintes par les artisans sénoufo (village de Fakaha)'],
        'baoule' => ['label' => 'Pays baoulé', 'credit' => 'Motifs inspirés du masque Goli, des poids à peser l\'or et du pagne kita baoulés'],
        'akan' => ['label' => 'Pays agni et abron', 'credit' => 'Motifs inspirés des symboles adinkra, associés au royaume abron du Gyaman (Bondoukou), et des sièges royaux akan'],
        'lagunaire' => ['label' => 'Peuples lagunaires', 'credit' => 'Motifs inspirés de la vie lagunaire : pirogues, filets de pêche, cocotiers et coquillages'],
        'krou-cote' => ['label' => 'Littoral krou', 'credit' => 'Motifs inspirés des pêcheurs du littoral krou : pirogues, filets et vagues de l\'Atlantique'],
        'krou' => ['label' => 'Pays krou', 'credit' => 'Motifs inspirés de la forêt du pays krou (Bété, Dida) : palmiers, tambours et cabosses'],
        'dan' => ['label' => 'Pays dan et wè', 'credit' => 'Motifs inspirés des masques dan et wè, des danseurs sur échasses et des montagnes de l\'Ouest'],
        'gouro' => ['label' => 'Pays gouro', 'credit' => 'Motifs inspirés du masque du Zaouli, danse gouro inscrite à l\'UNESCO'],
        'mande' => ['label' => 'Pays malinké et dioula', 'credit' => 'Motifs inspirés des mosquées de style soudanais (Kong, Kouto, Kaouara) et des calebasses gravées'],
        'national' => ['label' => 'Côte d\'Ivoire', 'credit' => 'Motifs inspirés des toiles de Korhogo, emblème de l\'artisanat ivoirien'],
        'lobi' => ['label' => 'Pays lobi et koulango', 'credit' => 'Motifs inspirés des statuettes lobi (bateba) et de la faune du parc national de la Comoé'],
    ];

    /** slug de peuple => clé d'aire culturelle (pour habiller les pages des peuples) */
    private const PEOPLE_AREA = [
        'baoule' => 'baoule', 'yaoure' => 'baoule',
        'agni-anyi' => 'akan', 'abron-brong' => 'akan', 'nafana' => 'akan',
        'adjoukrou' => 'lagunaire', 'attie' => 'lagunaire', 'abbey-abe' => 'lagunaire', 'aboure' => 'lagunaire', 'abidji' => 'lagunaire',
        'alladian' => 'lagunaire', 'avikam' => 'lagunaire', 'ebrie-tchaman' => 'lagunaire', 'eotile-ehotile' => 'lagunaire', 'nzima-appolo' => 'lagunaire',
        'bete' => 'krou', 'dida' => 'krou', 'godie' => 'krou', 'bakwe' => 'krou', 'neyo' => 'krou-cote', 'kroumen' => 'krou-cote',
        'guere-we' => 'dan', 'dan-yacouba' => 'dan', 'toura' => 'dan',
        'gouro' => 'gouro', 'gban-gagou' => 'gouro',
        'dioula-mandinka' => 'mande', 'malinke' => 'mande', 'koyaka' => 'mande', 'mahou' => 'mande',
        'senoufo' => 'senoufo', 'tagbana' => 'senoufo', 'djimini' => 'senoufo',
        'lobi' => 'lobi', 'koulango' => 'lobi', 'lorhon' => 'lobi', 'birifor' => 'lobi',
    ];

    /** @return array{key: string, label: string, credit: string} */
    public static function forPeople(?string $slug): array
    {
        $key = self::PEOPLE_AREA[(string) $slug] ?? 'national';
        $area = self::AREAS[$key];

        return ['key' => $key, 'label' => __($area['label']), 'credit' => __($area['credit'])];
    }

    /** @return array{key: string, label: string, credit: string} */
    public static function forCity(?TouristCity $city): array
    {
        $key = self::CITY_AREA[(string) $city?->slug] ?? 'national';
        $area = self::AREAS[$key];

        return ['key' => $key, 'label' => __($area['label']), 'credit' => __($area['credit'])];
    }
}
