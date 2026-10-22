<?php

namespace Database\Seeders\Data;

use App\Models\CulturalPeople;

/**
 * Corrections factuelles du référentiel culturel, appliquées par la migration
 * 2026_10_19_130000 et réappliquées par CulturalElementSeeder (pour qu'un nouvel
 * ensemencement ne réintroduise pas les erreurs).
 *
 * Seuls 5 éléments ivoiriens sont inscrits au patrimoine culturel immatériel de l'UNESCO :
 * Gbofe (2008), balafon (2012, étendu en 2024), Zaouli (2017), tissage du pagne (2023)
 * et attiéké (2024) — https://ich.unesco.org/fr/etat/cote-d-ivoire-CI
 */
final class CulturalCorrections
{
    /**
     * Champs corrigés par slug d'élément. 'peoples' : slugs des peuples porteurs (remplace la liste).
     *
     * @return array<string, array<string, mixed>>
     */
    public static function elements(): array
    {
        $unescoPci = IvorianSources::UNESCO_PCI;

        return [
            // Le Zaouli est gouro (Bouaflé, Zuénoula) selon l'UNESCO, pas baoulé.
            'danse-zaouli-baoule' => [
                'peoples' => ['gouro'],
                'short_description' => 'Musique et danse masquée des Gouro, rendant hommage à la beauté féminine, inscrite au patrimoine culturel immatériel de l\'humanité en 2017.',
                'description' => "Le Zaouli est une musique et une danse populaires des communautés gouro des départements de Bouaflé et de Zuénoula. Il rend hommage à la beauté féminine et s'inspire des masques Blou et Djela ; son autre nom, « Djela lou Zaouli », signifie « Zaouli, la fille de Djela ».\n\nLe masque se décline en sept masques faciaux, qui traduisent chacun une légende particulière. Le spectacle associe la sculpture, le tissage, l'orchestre, le chant et la danse : le danseur masqué y exécute un jeu de jambes d'une rapidité et d'une précision spectaculaires. Des représentations populaires ont lieu plusieurs fois par semaine, et des festivals et concours inter-villages en assurent la vitalité.",
                'origine_historique' => "Pratique des communautés gouro, le Zaouli a une fonction éducative, ludique et esthétique : il véhicule l'identité culturelle de ses détenteurs et favorise la cohésion sociale. Il a été inscrit en 2017 sur la Liste représentative du patrimoine culturel immatériel de l'humanité.",
                'unesco_status' => 'Liste représentative du patrimoine culturel immatériel de l\'humanité (UNESCO, 2017)',
                'practical_info' => [
                    ['icon' => 'fas fa-map-pin', 'label' => 'Région', 'value' => 'Bouaflé et Zuénoula (Marahoué)'],
                    ['icon' => 'fas fa-theater-masks', 'label' => 'Masques', 'value' => 'Sept masques faciaux, chacun lié à une légende'],
                    ['icon' => 'fas fa-calendar', 'label' => 'Occasions', 'value' => 'Représentations populaires, festivals, funérailles'],
                ],
                'sources' => [IvorianSources::ich('Le Zaouli, musique et danse populaires des communautés gouro de Côte d\'Ivoire', 'le-zaouli-musique-et-danse-populaires-des-communautes-gouro-de-cote-d-ivoire-01255'), $unescoPci, IvorianSources::wiki('Gouro_(peuple)', 'Gouro (peuple)')],
            ],

            // L'Abissa est la fête des N'Zima (Kotoko) de Grand-Bassam, pas des Adjoukrou.
            'fete-abissa-adjoukrou' => [
                'peoples' => ['nzima-appolo'],
                'short_description' => 'Fête du Nouvel An du peuple N\'Zima à Grand-Bassam : purification, pardon public et danses autour du tam-tam sacré.',
                'description' => "L'Abissa est la fête du Nouvel An des N'Zima (N'Zima Kotoko) de Grand-Bassam. Elle se tient chaque année entre la fin octobre et le début novembre, à des dates fixées par la cour royale.\n\nElle s'ouvre par la retraite du tam-tam sacré, l'Edo-n'gbolé, purifié dans le bois sacré pendant une période d'interdits où aucun autre tambour ne doit résonner. Viennent ensuite les phases publiques de danse et de réjouissance. L'Abissa est un temps de vérité : chacun peut reconnaître publiquement ses fautes pour obtenir le pardon, les bonnes actions sont célébrées, et les liens entre les vivants et les ancêtres sont renouvelés.",
                'origine_historique' => "Fête traditionnelle des N'Zima, peuple akan établi à Grand-Bassam et de part et d'autre de la frontière ghanéenne, l'Abissa est organisée sous l'autorité du roi des N'Zima Kotoko et attire chaque année de nombreux visiteurs dans la ville historique.",
                'practical_info' => [
                    ['icon' => 'fas fa-calendar', 'label' => 'Période', 'value' => 'Fin octobre – début novembre (dates fixées chaque année)'],
                    ['icon' => 'fas fa-map-pin', 'label' => 'Lieu', 'value' => 'Grand-Bassam (Sud-Comoé)'],
                    ['icon' => 'fas fa-drum', 'label' => 'Tam-tam sacré', 'value' => 'Edo-n\'gbolé'],
                ],
                'meilleure_periode' => ['Octobre', 'Novembre'],
                'sources' => [
                    ['label' => 'Fraternité Matin — « Grand-Bassam : l\'Abissa ou le Nouvel an pour le peuple N\'zima Kotoko »', 'url' => 'https://www.fratmat.info/article/70195/Culture/grand-bassam-labissa-ou-le-nouvel-an-pour-le-peuple-nzima-kotoko'],
                    IvorianSources::wiki('Abissa'),
                    ['label' => 'Abidjan.net — « Grand-Bassam, Abissa 2013 : Sa Majesté Awoula Tanoé Amon a fixé la date »', 'url' => 'https://news.abidjan.net/articles/467618/grand-bassam-abissa-2013-sa-majeste-awoula-tanoe-amon-a-fixe-la-date'],
                ],
            ],

            // Ananse vient de la tradition orale akan.
            'contes-araignee-dan' => [
                'peoples' => ['baoule', 'agni-anyi', 'abron-brong'],
                'origine_historique' => "Le personnage d'Ananse est issu de la tradition orale des peuples akan (Ghana et Côte d'Ivoire : Baoulé, Agni, Abron…), d'où il s'est diffusé dans toute l'Afrique de l'Ouest puis, avec la traite, jusqu'aux Antilles et aux Amériques.",
            ],

            // Mentions UNESCO non fondées pour la Côte d'Ivoire.
            'danse-echasses-dan' => [
                'unesco_status' => null,
                'short_description' => 'Danse acrobatique sur échasses de plusieurs mètres, performance spectaculaire et sacrée du pays dan.',
                'description' => "Les danseurs sur échasses dan évoluent sur des perches de plusieurs mètres de hauteur avec une agilité stupéfiante. Vêtus de pagnes colorés et coiffés de masques, ils exécutent des pirouettes, des sauts et des acrobaties. La danse sur échasses est à la fois un art sacré (liée aux esprits de la forêt) et un spectacle festif, très présent dans la région de Man.",
            ],
            'kora-dioula' => ['unesco_status' => null],
            'epopee-soundjata-keita' => ['unesco_status' => null],
            'masque-tangle-guere' => ['unesco_status' => null],

            // L'attiéké est inscrit depuis 2024 ; savoir-faire des peuples lagunaires.
            'attieke-national' => [
                'peoples' => ['adjoukrou', 'attie', 'ebrie-tchaman', 'alladian', 'avikam', 'abbey-abe'],
                'unesco_status' => 'Liste représentative du patrimoine culturel immatériel de l\'humanité (UNESCO, 2024)',
                'short_description' => 'Semoule de manioc fermentée et cuite à la vapeur, plat emblématique de la Côte d\'Ivoire, inscrit au patrimoine culturel immatériel de l\'humanité en 2024.',
            ],

            // Le balafon sénoufo n'est pas un élément dioula.
            'balafon-senoufo' => [
                'peoples' => ['senoufo'],
                'unesco_status' => 'Patrimoine culturel immatériel de l\'humanité (UNESCO, 2012 ; étendu en 2024)',
            ],
        ];
    }

    /**
     * Corrections des fiches peuples du référentiel d'origine.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function peoples(): array
    {
        return [
            // Aucune inscription UNESCO propre aux Dan.
            'dan-yacouba' => [
                'description' => 'Peuple de la région des montagnes (Man), les Dan sont réputés pour leurs masques de course (Guéguée), leurs acrobates sur échasses et leur célèbre danse du feu. Le masque est au cœur de leur vie sociale, religieuse et initiatique.',
                'symboles' => [
                    ['label' => 'Masque emblématique', 'valeur' => 'Masque de course Guéguée'],
                    ['label' => 'Danse emblématique', 'valeur' => 'Danse du feu & échasses'],
                    ['label' => 'Grand groupe', 'valeur' => 'Mandé du Sud'],
                ],
            ],
            // Les Adjoukrou vivent autour de Dabou ; l'Abissa est une fête n'zima.
            'adjoukrou' => [
                'description' => 'Peuple lagunaire du Sud ivoirien, les Adjoukrou vivent autour de Dabou, sur la rive nord de la lagune Ébrié, à l\'ouest d\'Abidjan. Agriculteurs et pêcheurs, ils sont connus pour leur système de classes d\'âge, dont le passage d\'une génération à l\'autre donne lieu à de grandes fêtes.',
                'histoire' => 'Les Adjoukrou sont établis de longue date dans la région de Dabou, entre la lagune Ébrié et les forêts de l\'intérieur. Les plantations de palmiers à huile et l\'urbanisation de la région d\'Abidjan ont profondément transformé leur mode de vie, tout en ravivant l\'attachement à leur organisation en classes d\'âge.',
                'symboles' => [
                    ['label' => 'Organisation', 'valeur' => 'Classes d\'âge (générations)'],
                    ['label' => 'Ville de référence', 'valeur' => 'Dabou'],
                    ['label' => 'Territoire', 'valeur' => 'Rive nord de la lagune Ébrié'],
                ],
                'capitale_culturelle' => 'Dabou',
            ],
        ];
    }

    /** Sources ciblées des éléments du référentiel d'origine qui n'en avaient pas. */
    public static function sources(): array
    {
        $w = fn (string $t, ?string $l = null) => IvorianSources::wiki($t, $l);

        return [
            'masque-mblo-baoule' => [$w('Baoulés')],
            'fete-ignames-baoule' => [$w('Fête_des_ignames', 'Fête des ignames'), $w('Baoulés')],
            'funerailles-royales-agni' => [$w('Agnis')],
            'orfevrerie-or-agni' => [$w('Agnis')],
            'fete-royale-abron-bondoukou' => [$w('Abron_(peuple)', 'Abron (peuple)')],
            'masque-gbage-bete' => [$w('Bétés')],
            'kedjenou-bete' => [$w('Kédjénou'), $w('Bétés')],
            'tam-tam-bete' => [$w('Bétés')],
            'masque-tangle-guere' => [$w('Wés')],
            'societe-do-guere' => [$w('Wés')],
            'danse-echasses-dan' => [$w('Dan_(peuple)', 'Dan (peuple)')],
            'masque-gueguee-dan' => [$w('Dan_(peuple)', 'Dan (peuple)')],
            'contes-araignee-dan' => [$w('Anansi'), $w('Baoulés')],
            'kora-dioula' => [$w('Kora')],
            'bogolan-dioula' => [$w('Bogolan')],
            'epopee-soundjata-keita' => [$w('Soundiata_Keïta', 'Soundiata Keïta')],
            'djembe-malinke' => [$w('Djembé'), $w('Malinkés')],
            'confrérie-donzo-malinke' => [$w('Confréries_de_chasseurs_en_Afrique', 'Confréries de chasseurs en Afrique'), $w('Malinkés')],
            'poro-senoufo' => [$w('Sénoufos')],
            'masque-kpelie-senoufo' => [$w('Sénoufos')],
            'figurines-thil-lobi' => [$w('Lobi_(peuple)', 'Lobi (peuple)')],
            'soukala-lobi' => [$w('Lobi_(peuple)', 'Lobi (peuple)')],
            'ceremonie-adjao-attie' => [$w('Attié_(peuple)', 'Attié (peuple)')],
            'bangui-vin-palme' => [$w('Vin_de_palme', 'Vin de palme')],
        ];
    }

    /** Applique les corrections aux données d'un élément (tableau du seeder ou attributs). */
    public static function apply(string $slug, array $attributes): array
    {
        $fix = self::elements()[$slug] ?? [];
        if (isset($fix['peoples'])) {
            $ids = CulturalPeople::whereIn('slug', $fix['peoples'])->pluck('id', 'slug');
            $roles = collect($fix['peoples'])->filter(fn ($s) => isset($ids[$s]))
                ->map(fn ($s) => ['people_id' => $ids[$s], 'role' => 'peuple d\'origine'])->values()->all();
            // Peuples pas encore créés (premier ensemencement) : rattachement laissé tel quel,
            // réappliqué par CulturalPeopleProfileSeeder une fois tous les peuples présents.
            if (count($roles) === count($fix['peoples'])) {
                $attributes['people_roles'] = $roles;
            }
            unset($fix['peoples']);
        }

        return array_merge($attributes, $fix);
    }
}
