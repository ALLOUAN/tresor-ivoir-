<?php

namespace Database\Seeders\Data;

/**
 * Références documentaires communes aux seeders du référentiel ivoirien.
 * Chaque source : ['label' => …, 'url' => …|null].
 */
final class IvorianSources
{
    public const DECRET_REGIONS = ['label' => 'Décret n° 2011-263 du 28 septembre 2011 portant organisation du territoire national en districts et en régions (Journal officiel de la République de Côte d\'Ivoire)', 'url' => null];

    public const DECRET_MORONOU = ['label' => 'Décret n° 2012-612 du 4 juillet 2012 portant création de la région du Moronou', 'url' => null];

    public const WIKI_REGIONS = ['label' => 'Wikipédia — Régions de Côte d\'Ivoire', 'url' => 'https://fr.wikipedia.org/wiki/Régions_de_Côte_d\'Ivoire'];

    public const WIKI_DEMOGRAPHIE = ['label' => 'Wikipédia — Démographie de la Côte d\'Ivoire (répartition des grands groupes, recensement 2021)', 'url' => 'https://fr.wikipedia.org/wiki/Démographie_de_la_Côte_d\'Ivoire'];

    public const WIKI_LANGUES = ['label' => 'Wikipédia — Langues en Côte d\'Ivoire', 'url' => 'https://fr.wikipedia.org/wiki/Langues_en_Côte_d\'Ivoire'];

    public const ETHNOLOGUE = ['label' => 'Ethnologue — Langues de Côte d\'Ivoire (SIL International)', 'url' => 'https://www.ethnologue.com/country/CI/'];

    public const INS_RGPH = ['label' => 'Institut National de la Statistique (INS) — Recensement général de la population et de l\'habitat 2021', 'url' => 'https://www.ins.ci/'];

    public const UNESCO_PATRIMOINE_MONDIAL = ['label' => 'UNESCO — Côte d\'Ivoire, biens inscrits sur la Liste du patrimoine mondial', 'url' => 'https://whc.unesco.org/fr/etatsparties/ci'];

    public const UNESCO_PCI = ['label' => 'UNESCO — Côte d\'Ivoire, patrimoine culturel immatériel', 'url' => 'https://ich.unesco.org/fr/etat/cote-d-ivoire-CI'];

    /** Bien du patrimoine mondial (numéro de la liste UNESCO). */
    public static function whc(int $id, string $name): array
    {
        return ['label' => "UNESCO — Patrimoine mondial : {$name}", 'url' => "https://whc.unesco.org/fr/list/{$id}"];
    }

    /** Article de Wikipédia en français (titre exact de l'article). */
    public static function wiki(string $title, ?string $label = null): array
    {
        return ['label' => 'Wikipédia — '.($label ?? str_replace('_', ' ', $title)), 'url' => 'https://fr.wikipedia.org/wiki/'.$title];
    }

    /** Élément inscrit au patrimoine culturel immatériel (page officielle UNESCO). */
    public static function ich(string $label, string $path): array
    {
        return ['label' => "UNESCO — Patrimoine culturel immatériel : {$label}", 'url' => 'https://ich.unesco.org/fr/RL/'.$path];
    }
}
