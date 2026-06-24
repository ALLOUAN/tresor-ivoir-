<?php

namespace Database\Seeders;

use App\Models\CulturalPeople;
use Illuminate\Database\Seeder;

class CulturalPeopleSeeder extends Seeder
{
    public function run(): void
    {
        $peoples = [

            // ── AKAN — Zone Centre & Est ─────────────────────────────────────

            [
                'name'                => 'Baoulé',
                'slug'                => 'baoule',
                'zone_geographique'   => 'Centre',
                'famille_linguistique'=> 'Kwa (Akan)',
                'langue_principale'   => 'Baoulé',
                'population_estimee'  => 3200000,
                'capitale_culturelle' => 'Bouaké',
                'description'         => 'Peuple majoritaire de Côte d\'Ivoire, les Baoulé occupent la région centrale. Réputés pour leur art de cour (statuettes, masques portraits), leur tisserand et leur organisation sociale matrilinéaire, ils forment l\'un des groupes culturels les plus influents du pays.',
                'histoire'            => 'Les Baoulé sont issus d\'une migration menée par la reine Abla Pokou au XVIIIe siècle depuis le Ghana actuel. Selon la légende, la reine aurait sacrifié son fils unique pour permettre la traversée du fleuve Comoé. Leur nom signifie "l\'enfant n\'est pas mort" en référence à ce sacrifice fondateur.',
                'symboles'            => [
                    ['label' => 'Animal totem', 'valeur' => 'Crocodile'],
                    ['label' => 'Couleurs', 'valeur' => 'Or et noir'],
                    ['label' => 'Emblème', 'valeur' => 'Masque portrait (Mblo)'],
                ],
                'is_featured'         => 1,
                'is_active'           => 1,
                'sort_order'          => 1,
            ],

            [
                'name'                => 'Agni (Anyi)',
                'slug'                => 'agni-anyi',
                'zone_geographique'   => 'Est',
                'famille_linguistique'=> 'Kwa (Akan)',
                'langue_principale'   => 'Anyi',
                'population_estimee'  => 520000,
                'capitale_culturelle' => 'Abengourou',
                'description'         => 'Peuple du Sud-Est ivoirien, les Agni sont connus pour leur monarchie traditionnelle, leur orfèvrerie (bijoux en or) et leurs cérémonies funéraires élaborées (deuil royal). Leur organisation politique en royaumes est l\'une des plus structurées du pays.',
                'histoire'            => 'Les Agni partagent une origine commune avec les Baoulé et les Ashanti du Ghana. Ils se sont établis dans l\'est de la Côte d\'Ivoire au XVIIIe siècle en fondant plusieurs royaumes dont le Sanwi (Krinjabo), l\'Indénié (Abengourou) et le Moronou.',
                'symboles'            => [
                    ['label' => 'Emblème', 'valeur' => 'Trône royal en bois sculpté'],
                    ['label' => 'Artisanat clé', 'valeur' => 'Orfèvrerie en or (bijoux royaux)'],
                ],
                'is_featured'         => 0,
                'is_active'           => 1,
                'sort_order'          => 2,
            ],

            [
                'name'                => 'Abron (Brong)',
                'slug'                => 'abron-brong',
                'zone_geographique'   => 'Est',
                'famille_linguistique'=> 'Kwa (Akan)',
                'langue_principale'   => 'Abron',
                'population_estimee'  => 210000,
                'capitale_culturelle' => 'Bondoukou',
                'description'         => 'Peuple du nord-est ivoirien, les Abron ont fondé le royaume du Gyaman dont Bondoukou était la capitale. Leur culture mêle influences akan et mandé, reflétant leur position de carrefour commercial historique.',
                'histoire'            => 'Le royaume du Gyaman, fondé au XVIIe siècle, était un important centre commercial et politique. Les Abron ont longtemps résisté à l\'expansion ashanti avant d\'être intégrés dans les routes commerciales de l\'or et des kola.',
                'symboles'            => [
                    ['label' => 'Héritage', 'valeur' => 'Royaume du Gyaman'],
                    ['label' => 'Commerce historique', 'valeur' => 'Route de l\'or et des noix de kola'],
                ],
                'is_featured'         => 0,
                'is_active'           => 1,
                'sort_order'          => 3,
            ],

            // ── KROU — Zone Ouest & Sud-Ouest ───────────────────────────────

            [
                'name'                => 'Bété',
                'slug'                => 'bete',
                'zone_geographique'   => 'Ouest',
                'famille_linguistique'=> 'Krou',
                'langue_principale'   => 'Bété',
                'population_estimee'  => 1100000,
                'capitale_culturelle' => 'Gagnoa',
                'description'         => 'Peuple de la forêt du Centre-Ouest, les Bété sont réputés pour leurs masques de guerre (Gbagyé), leur musique percussive et leurs danses guerrières. Ils ont donné à la Côte d\'Ivoire le président Laurent Gbagbo. Leur art du masque est considéré comme l\'un des plus expressifs d\'Afrique de l\'Ouest.',
                'histoire'            => 'Les Bété sont originaires de la région forestière et ont longtemps résisté à la pénétration coloniale. Leur société traditionnelle est organisée autour de lignages patrilinéaires et de conseils d\'anciens. La période coloniale a profondément marqué leur mémoire collective.',
                'symboles'            => [
                    ['label' => 'Masque emblématique', 'valeur' => 'Masque Gbagyé (guerrier)'],
                    ['label' => 'Valeur centrale', 'valeur' => 'Bravoure et résistance'],
                ],
                'is_featured'         => 1,
                'is_active'           => 1,
                'sort_order'          => 4,
            ],

            [
                'name'                => 'Guéré (Wê)',
                'slug'                => 'guere-we',
                'zone_geographique'   => 'Ouest',
                'famille_linguistique'=> 'Krou',
                'langue_principale'   => 'Guéré',
                'population_estimee'  => 350000,
                'capitale_culturelle' => 'Guiglo',
                'description'         => 'Peuple de la forêt profonde de l\'ouest, les Guéré (ou Wê) sont célèbres pour leurs masques polychromes spectaculaires, parmi les plus expressifs et les plus complexes d\'Afrique. Ces masques (Tanglé, Kagle, Gla) jouent un rôle central dans la vie sociale, judiciaire et spirituelle.',
                'histoire'            => 'Établis dans la forêt dense de l\'ouest ivoirien, les Guéré ont maintenu un mode de vie forestier jusqu\'au XXe siècle. Leur résistance à la colonisation française (1905-1920) reste un épisode majeur de leur histoire. La société secrète du "Do" structure encore aujourd\'hui leur organisation sociale.',
                'symboles'            => [
                    ['label' => 'Masque emblématique', 'valeur' => 'Masque Tanglé (jugement)'],
                    ['label' => 'Institution', 'valeur' => 'Société secrète du Do'],
                    ['label' => 'Territoire', 'valeur' => 'Forêt de l\'ouest (Taï)'],
                ],
                'is_featured'         => 1,
                'is_active'           => 1,
                'sort_order'          => 5,
            ],

            [
                'name'                => 'Dan (Yacouba)',
                'slug'                => 'dan-yacouba',
                'zone_geographique'   => 'Ouest',
                'famille_linguistique'=> 'Mandé du Sud',
                'langue_principale'   => 'Dan',
                'population_estimee'  => 400000,
                'capitale_culturelle' => 'Man',
                'description'         => 'Peuple de la région des montagnes (Man), les Dan sont réputés pour leurs masques de course (Guéguée), leurs acrobates sur échasses et leur célèbre danse du feu. Leur culture du masque et leurs traditions initiatiques sont inscrites au patrimoine culturel immatériel de l\'UNESCO.',
                'histoire'            => 'Les Dan occupent la région montagneuse de l\'ouest, à cheval entre la Côte d\'Ivoire et le Liberia. Leur société est organisée en villages autonomes liés par des alliances et des confréries initiatiques. La forêt sacrée est au cœur de leur cosmologie.',
                'symboles'            => [
                    ['label' => 'Masque emblématique', 'valeur' => 'Masque de course Guéguée'],
                    ['label' => 'Danse emblématique', 'valeur' => 'Danse du feu & échasses'],
                    ['label' => 'UNESCO', 'valeur' => 'Patrimoine culturel immatériel'],
                ],
                'is_featured'         => 1,
                'is_active'           => 1,
                'sort_order'          => 6,
            ],

            // ── MANDÉ — Zone Nord ───────────────────────────────────────────

            [
                'name'                => 'Dioula (Mandinka)',
                'slug'                => 'dioula-mandinka',
                'zone_geographique'   => 'Nord',
                'famille_linguistique'=> 'Mandé',
                'langue_principale'   => 'Dioula',
                'population_estimee'  => 2800000,
                'capitale_culturelle' => 'Kong',
                'description'         => 'Peuple commerçant par excellence, les Dioula sont présents sur tout le territoire ivoirien. Leur langue est la lingua franca du commerce en Côte d\'Ivoire. De culture musulmane, ils ont fondé d\'importants centres commerciaux et religieux comme Kong. Leur tisserand (teinture bogolan) et leur musique (balafon, kora) rayonnent bien au-delà de leurs frontières.',
                'histoire'            => 'Les Dioula sont les héritiers des grands commerçants mandé qui contrôlaient les routes de l\'or et du sel au Moyen Âge. Ils ont propagé l\'islam en Afrique de l\'Ouest et fondé la cité-état de Kong au XVIIe siècle, détruite par Samory Touré en 1897. Aujourd\'hui leur langue est parlée par plus de 7 millions de personnes en CI.',
                'symboles'            => [
                    ['label' => 'Instrument emblématique', 'valeur' => 'Kora & Balafon'],
                    ['label' => 'Tissu emblématique', 'valeur' => 'Bogolan (tissu mud cloth)'],
                    ['label' => 'Héritage', 'valeur' => 'Cité-état de Kong'],
                    ['label' => 'Religion', 'valeur' => 'Islam (Sunnite)'],
                ],
                'is_featured'         => 1,
                'is_active'           => 1,
                'sort_order'          => 7,
            ],

            [
                'name'                => 'Malinké',
                'slug'                => 'malinke',
                'zone_geographique'   => 'Nord',
                'famille_linguistique'=> 'Mandé',
                'langue_principale'   => 'Malinké',
                'population_estimee'  => 480000,
                'capitale_culturelle' => 'Odienné',
                'description'         => 'Peuple du nord-ouest ivoirien, les Malinké (ou Mandingues) sont les descendants directs de l\'empire du Mali. Guerriers et commerçants, ils ont maintenu de fortes traditions épiques (griots, récits de Soundjata Keïta) et une culture musicale riche autour de la kora et du djembé.',
                'histoire'            => 'Héritiers de l\'empire du Mali (XIIIe-XIVe siècle), les Malinké se sont dispersés en Afrique de l\'Ouest après sa chute. Leur mémoire épique, transmise par les griots (djeli), célèbre Soundjata Keïta, fondateur de l\'empire. La confrérie des chasseurs (Donzo) reste une institution centrale.',
                'symboles'            => [
                    ['label' => 'Tradition orale', 'valeur' => 'Épopée de Soundjata Keïta'],
                    ['label' => 'Gardiens de mémoire', 'valeur' => 'Griots (Djeli)'],
                    ['label' => 'Confrérie', 'valeur' => 'Chasseurs Donzo'],
                ],
                'is_featured'         => 0,
                'is_active'           => 1,
                'sort_order'          => 8,
            ],

            // ── GUR (VOLTAÏQUE) — Zone Nord ─────────────────────────────────

            [
                'name'                => 'Sénoufo',
                'slug'                => 'senoufo',
                'zone_geographique'   => 'Nord',
                'famille_linguistique'=> 'Gur (Voltaïque)',
                'langue_principale'   => 'Sénoufo (Tyebara)',
                'population_estimee'  => 1600000,
                'capitale_culturelle' => 'Korhogo',
                'description'         => 'Peuple agriculteur et artisan du nord ivoirien, les Sénoufo sont renommés pour leurs tisserands (pagne sénoufo), leurs sculpteurs de masques Kpelié, leurs forgerons et leurs danseurs du Poro. La société d\'initiation masculine Poro structure toute la vie sociale et culturelle sénoufo sur plusieurs années.',
                'histoire'            => 'Les Sénoufo occupent le nord de la Côte d\'Ivoire, le sud du Mali et le Burkina Faso. Leur résistance à l\'empire mandingue et à la colonisation française a forgé une identité forte. La société du Poro, institution initiatique fondamentale, structure les cycles de vie masculins sur 21 ans.',
                'symboles'            => [
                    ['label' => 'Institution centrale', 'valeur' => 'Poro (société initiatique masculine)'],
                    ['label' => 'Masque emblématique', 'valeur' => 'Masque Kpelié (face plate)'],
                    ['label' => 'Artisanat', 'valeur' => 'Pagne tissé à bandes & sculpture'],
                    ['label' => 'Oiseau sacré', 'valeur' => 'Calao (Nasholo)'],
                ],
                'is_featured'         => 1,
                'is_active'           => 1,
                'sort_order'          => 9,
            ],

            [
                'name'                => 'Lobi',
                'slug'                => 'lobi',
                'zone_geographique'   => 'Nord',
                'famille_linguistique'=> 'Gur (Voltaïque)',
                'langue_principale'   => 'Lobiri',
                'population_estimee'  => 110000,
                'capitale_culturelle' => 'Bouna',
                'description'         => 'Peuple du nord-est ivoirien, les Lobi sont connus pour leurs sculptures en bronze et leurs habitations fortifiées (soukala). Leur art ancestral — notamment les figurines "thil" — est très prisé des collectionneurs internationaux. Leur société sans chef centralisé est régie par des esprits protecteurs.',
                'histoire'            => 'Originaires du Ghana et du Burkina Faso, les Lobi se sont installés dans le nord-est de la CI au XVIIIe siècle. Leur résistance farouche à toute forme d\'autorité extérieure (royaumes voisins, colonisation) leur a valu la réputation d\'être "indomptables". Leur habitat en terre (soukala) reflète cette mentalité défensive.',
                'symboles'            => [
                    ['label' => 'Art emblématique', 'valeur' => 'Figurines thil en bronze'],
                    ['label' => 'Habitat', 'valeur' => 'Soukala (maison fortifiée en terre)'],
                    ['label' => 'Organisation', 'valeur' => 'Société sans chefferie (acéphale)'],
                ],
                'is_featured'         => 0,
                'is_active'           => 1,
                'sort_order'          => 10,
            ],

            // ── LAGUNAIRES — Zone Sud ───────────────────────────────────────

            [
                'name'                => 'Adjoukrou',
                'slug'                => 'adjoukrou',
                'zone_geographique'   => 'Sud',
                'famille_linguistique'=> 'Kwa (Lagunaire)',
                'langue_principale'   => 'Adjoukrou',
                'population_estimee'  => 120000,
                'capitale_culturelle' => 'Jacqueville',
                'description'         => 'Peuple lagunaire du Sud ivoirien, les Adjoukrou vivent sur les îles et les rives de la lagune Ébrié près d\'Abidjan. Pêcheurs et agriculteurs, ils sont connus pour leur fête traditionnelle de l\'Abissa (danse de purification collective) et leur système de classes d\'âge unique.',
                'histoire'            => 'Les Adjoukrou sont l\'un des peuples autochtones de la région d\'Abidjan. Leur territoire original correspond aujourd\'hui à la presqu\'île de Jacqueville. L\'urbanisation d\'Abidjan et l\'industrialisation du littoral ont profondément transformé leur mode de vie tout en ravivant l\'attachement à leurs traditions (Abissa).',
                'symboles'            => [
                    ['label' => 'Fête emblématique', 'valeur' => 'Abissa (purification collective)'],
                    ['label' => 'Organisation', 'valeur' => 'Classes d\'âge (Echos)'],
                    ['label' => 'Territoire', 'valeur' => 'Lagune Ébrié & presqu\'île de Jacqueville'],
                ],
                'is_featured'         => 0,
                'is_active'           => 1,
                'sort_order'          => 11,
            ],

            [
                'name'                => 'Attié',
                'slug'                => 'attie',
                'zone_geographique'   => 'Sud',
                'famille_linguistique'=> 'Kwa (Lagunaire)',
                'langue_principale'   => 'Attié',
                'population_estimee'  => 430000,
                'capitale_culturelle' => 'Agboville',
                'description'         => 'Peuple du couloir lagunaire entre Abidjan et Agboville, les Attié sont agriculteurs (café, cacao) et pêcheurs. Leur culture est marquée par des rites funéraires élaborés, des cérémonies de second deuil (Adjao) et une tradition de sculpture sur bois moins connue mais de grande qualité.',
                'histoire'            => 'Les Attié font partie du groupe des peuples lagunaires qui occupaient la région côtière avant l\'arrivée des colons. Leur intégration précoce dans l\'économie de plantation coloniale (café-cacao) a façonné leur mode de vie contemporain tout en préservant leurs pratiques cérémonielles.',
                'symboles'            => [
                    ['label' => 'Cérémonie emblématique', 'valeur' => 'Adjao (second deuil)'],
                    ['label' => 'Activité traditionnelle', 'valeur' => 'Agriculture (café-cacao)'],
                ],
                'is_featured'         => 0,
                'is_active'           => 1,
                'sort_order'          => 12,
            ],

        ];

        foreach ($peoples as $people) {
            CulturalPeople::updateOrCreate(['slug' => $people['slug']], $people);
        }

        $total = CulturalPeople::count();
        $this->command->info("✓ {$total} peuples culturels ivoiriens chargés.");
    }
}
