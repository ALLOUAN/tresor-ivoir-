<?php

namespace Database\Seeders;

use App\Models\CulturalDomain;
use App\Models\CulturalElement;
use App\Models\CulturalPeople;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CulturalElementSeeder extends Seeder
{
    public function run(): void
    {
        // Résoudre les IDs une seule fois par slug
        $d = CulturalDomain::pluck('id', 'slug');
        $p = CulturalPeople::pluck('id', 'slug');

        $elements = [

            // ══════════════════════════════════════════════
            // BAOULÉ
            // ══════════════════════════════════════════════
            [
                'slug'               => 'danse-zaouli-baoule',
                'domain_id'          => $d['danse-des-masques'],
                'people_roles'       => [['people_id' => $p['baoule'], 'role' => 'peuple d\'origine']],
                'name'               => 'La Danse du Zaouli',
                'short_description'  => 'Danse acrobatique masquée des Baoulé, reconnue comme chef-d\'œuvre du patrimoine immatériel de l\'humanité.',
                'description'        => 'Le Zaouli est une danse acrobatique virtuose exécutée par un danseur portant un masque représentant un visage humain idéalisé. Caractérisé par des mouvements de pieds d\'une rapidité et d\'une complexité extraordinaires, le Zaouli est à la fois un spectacle fascinant et un rite de guérison. Chaque performance est unique : le danseur improvise dans un état de transe légère, guidé par la musique des tambours et des balafons.',
                'origine_historique' => 'Apparu au milieu du XXe siècle dans la région de Bouaké, le Zaouli aurait été créé par un certain Goly Lou à partir d\'une vision. Rapidement adopté par les communautés baoulé, il est inscrit depuis 2017 au patrimoine culturel immatériel de l\'UNESCO.',
                'niveau_risque'      => 'stable',
                'unesco_status'      => 'Patrimoine culturel immatériel de l\'humanité (UNESCO 2017)',
                'meilleure_periode'  => ['Décembre', 'Janvier', 'Février'],
                'practical_info'     => [
                    ['icon' => 'fas fa-map-pin',    'label' => 'Région',     'value' => 'Bouaké, Centre Côte d\'Ivoire'],
                    ['icon' => 'fas fa-calendar',   'label' => 'Occasions',  'value' => 'Funérailles, fêtes villageoises, cérémonies'],
                    ['icon' => 'fas fa-user',       'label' => 'Pratiquants','value' => 'Danseurs masculins initiés'],
                ],
                'is_featured'  => 1,
                'is_active'    => 1,
                'sort_order'   => 1,
            ],
            [
                'slug'               => 'masque-mblo-baoule',
                'domain_id'          => $d['masques-rituels'],
                'people_roles'       => [['people_id' => $p['baoule'], 'role' => 'peuple d\'origine']],
                'name'               => 'Masque Portrait Mblo',
                'short_description'  => 'Masque de divertissement baoulé représentant un visage humain idéalisé, symbole de beauté et d\'élégance.',
                'description'        => 'Le Mblo est un masque de divertissement qui représente un être humain dont les traits sont idéalisés pour symboliser la beauté parfaite. Contrairement aux masques guerriers, le Mblo est utilisé lors des fêtes et des rassemblements sociaux pour divertir et honorer des personnalités. Il est accompagné de chants et de danses gracieuses. Chaque masque Mblo est unique et porte le nom d\'une personne réelle qu\'il est censé représenter.',
                'origine_historique' => 'Le Mblo est l\'une des formes d\'art masqué les plus sophistiquées de l\'Afrique de l\'Ouest. Apparu dans la région centrale de Côte d\'Ivoire, il témoigne de la maîtrise des sculpteurs baoulé qui ont développé un langage plastique d\'une grande finesse.',
                'niveau_risque'      => 'vulnerable',
                'practical_info'     => [
                    ['icon' => 'fas fa-palette',  'label' => 'Matériaux',  'value' => 'Bois, pigments naturels, fibres végétales'],
                    ['icon' => 'fas fa-theater-masks', 'label' => 'Type', 'value' => 'Masque de divertissement (non sacré)'],
                ],
                'is_featured'  => 0,
                'is_active'    => 1,
                'sort_order'   => 2,
            ],
            [
                'slug'               => 'tissu-kita-baoule',
                'domain_id'          => $d['tissage-textile'],
                'people_roles'       => [['people_id' => $p['baoule'], 'role' => 'peuple d\'origine']],
                'name'               => 'Tissu Kita Baoulé',
                'short_description'  => 'Pagne tissé à bandes étroites multicolores, emblème de l\'identité vestimentaire baoulé.',
                'description'        => 'Le Kita est un pagne tissé sur un métier à bandes étroites, caractérisé par sa richesse chromatique et ses motifs géométriques répétitifs. Porté lors des cérémonies importantes (funérailles, mariages, intronisations), il est le marqueur identitaire par excellence de la culture baoulé. Les maîtres tisserands transmettent leur savoir-faire de père en fils, chaque motif ayant une signification symbolique précise.',
                'niveau_risque'      => 'vulnerable',
                'practical_info'     => [
                    ['icon' => 'fas fa-shirt',   'label' => 'Matière',   'value' => 'Coton filé main, fils teints'],
                    ['icon' => 'fas fa-tools',   'label' => 'Technique', 'value' => 'Métier à tisser à pédale (bandes de 10 cm)'],
                    ['icon' => 'fas fa-tag',     'label' => 'Prix',      'value' => '15 000 – 80 000 FCFA selon qualité'],
                ],
                'is_featured'  => 0,
                'is_active'    => 1,
                'sort_order'   => 3,
            ],
            [
                'slug'               => 'fete-ignames-baoule',
                'domain_id'          => $d['fetes-traditionnelles'],
                'people_roles'       => [['people_id' => $p['baoule'], 'role' => 'peuple organisateur']],
                'name'               => 'Fête des Premières Ignames',
                'short_description'  => 'Cérémonie de remerciement aux ancêtres marquant la nouvelle récolte d\'ignames, moment de communion collective.',
                'description'        => 'La Fête des Premières Ignames est l\'un des rituels agricoles les plus importants de la culture baoulé. Elle marque la fin de la saison des pluies et le début de la récolte. Avant que quiconque puisse consommer les nouvelles ignames, une cérémonie est organisée pour remercier les ancêtres et les divinités de la terre. Des libations sont versées, des prières récitées, et les ignames sont offertes aux esprits des défunts. S\'ensuivent des danses, des chants et des festins collectifs.',
                'niveau_risque'      => 'stable',
                'meilleure_periode'  => ['Août', 'Septembre', 'Octobre'],
                'practical_info'     => [
                    ['icon' => 'fas fa-calendar', 'label' => 'Période',   'value' => 'Août – Octobre (récolte des ignames)'],
                    ['icon' => 'fas fa-users',    'label' => 'Accès',     'value' => 'Ouvert aux visiteurs (selon village)'],
                ],
                'is_featured'  => 0,
                'is_active'    => 1,
                'sort_order'   => 4,
            ],

            // ══════════════════════════════════════════════
            // AGNI
            // ══════════════════════════════════════════════
            [
                'slug'               => 'funerailles-royales-agni',
                'domain_id'          => $d['funerailles-deuil'],
                'people_roles'       => [['people_id' => $p['agni-anyi'], 'role' => 'peuple d\'origine']],
                'name'               => 'Funérailles Royales Agni',
                'short_description'  => 'Cérémonies funèbres grandioses des royaumes Agni, parmi les plus élaborées d\'Afrique de l\'Ouest.',
                'description'        => 'Les funérailles royales Agni (Adjao) sont des cérémonies d\'une ampleur et d\'une sophistication exceptionnelles. Elles peuvent durer plusieurs jours et mobilisent des milliers de personnes. L\'or, les parures précieuses, les costumes ceremonials, la musique des tambours et les danses rituelles se mêlent dans un spectacle unique. La mort d\'un roi ou d\'un notable est l\'occasion d\'un second deuil solennel, célébré plusieurs mois ou années après le décès, lorsque la famille a pu réunir les ressources nécessaires.',
                'origine_historique' => 'Héritières des traditions funéraires akan, les pratiques funèbres Agni ont été influencées par celles des Ashanti du Ghana. Elles témoignent de la conception de la mort comme passage vers un autre monde, et non comme une fin.',
                'niveau_risque'      => 'stable',
                'practical_info'     => [
                    ['icon' => 'fas fa-calendar',  'label' => 'Durée',     'value' => '3 à 7 jours'],
                    ['icon' => 'fas fa-map-pin',   'label' => 'Région',    'value' => 'Abengourou, Aboisso, Agnibilékrou'],
                ],
                'is_featured'  => 1,
                'is_active'    => 1,
                'sort_order'   => 5,
            ],
            [
                'slug'               => 'orfevrerie-or-agni',
                'domain_id'          => $d['orfevrerie-bijoux'],
                'people_roles'       => [['people_id' => $p['agni-anyi'], 'role' => 'peuple d\'origine']],
                'name'               => 'Orfèvrerie en Or Agni',
                'short_description'  => 'Art de la bijouterie en or à la cire perdue, patrimoine royal des cours agni d\'Abengourou.',
                'description'        => 'L\'orfèvrerie Agni est l\'une des traditions métallurgiques les plus raffinées d\'Afrique de l\'Ouest. Les artisans façonnent l\'or à la cire perdue (fonte à la cire perdue) pour créer pendentifs, bagues, bracelets et pectoraux aux motifs symboliques complexes. Ces bijoux ne sont pas de simples ornements : ils sont les insignes du pouvoir royal et des hauts dignitaires, chaque motif codifiant un statut social précis.',
                'niveau_risque'      => 'en_danger',
                'practical_info'     => [
                    ['icon' => 'fas fa-tools',  'label' => 'Technique', 'value' => 'Fonte à la cire perdue (lost wax)'],
                    ['icon' => 'fas fa-gem',    'label' => 'Matière',   'value' => 'Or 18-24 carats, alliages traditionnels'],
                ],
                'is_featured'  => 0,
                'is_active'    => 1,
                'sort_order'   => 6,
            ],

            // ══════════════════════════════════════════════
            // ABRON
            // ══════════════════════════════════════════════
            [
                'slug'               => 'fete-royale-abron-bondoukou',
                'domain_id'          => $d['fetes-traditionnelles'],
                'people_roles'       => [['people_id' => $p['abron-brong'], 'role' => 'peuple d\'origine']],
                'name'               => 'Fête Royale du Gyaman',
                'short_description'  => 'Célébration annuelle de la fondation du royaume Abron du Gyaman à Bondoukou.',
                'description'        => 'La Fête Royale du Gyaman commémore chaque année la fondation du royaume Abron et rend hommage aux ancêtres fondateurs. Le roi (Gyamanhene) préside la cérémonie entouré de ses dignitaires, vêtus des tenues royales en kente et ornés de bijoux en or. Des danses traditionnelles, des processions et des libations honorent les ancêtres. La fête est l\'occasion pour la diaspora abron de se retrouver à Bondoukou.',
                'niveau_risque'      => 'stable',
                'meilleure_periode'  => ['Décembre', 'Janvier'],
                'is_featured'  => 0,
                'is_active'    => 1,
                'sort_order'   => 7,
            ],

            // ══════════════════════════════════════════════
            // BÉTÉ
            // ══════════════════════════════════════════════
            [
                'slug'               => 'masque-gbage-bete',
                'domain_id'          => $d['masques-rituels'],
                'people_roles'       => [['people_id' => $p['bete'], 'role' => 'peuple d\'origine']],
                'name'               => 'Masque Gbagyé (Guerrier Bété)',
                'short_description'  => 'Masque guerrier aux traits expressifs et puissants, symbole de la bravoure et de la justice chez les Bété.',
                'description'        => 'Le Gbagyé est le masque guerrier des Bété, caractérisé par ses traits fortement expressifs : yeux globuleux, mâchoire proéminente, dents saillantes et scarifications stylisées. Il est l\'incarnation de la force guerrière et de la justice communautaire. Lors des cérémonies, son apparition est accompagnée d\'un bruit assourdissant de tambours et de cris rituels. Sa danse est puissante, explosive, mimant les gestes du combat.',
                'origine_historique' => 'Le masque Gbagyé est né de la société guerrière des Bété, qui résistèrent longtemps à la colonisation française. Il incarne la mémoire de cette résistance et reste un symbole fort de l\'identité bété.',
                'niveau_risque'      => 'vulnerable',
                'practical_info'     => [
                    ['icon' => 'fas fa-palette', 'label' => 'Matériaux', 'value' => 'Bois dur (iroko), pigments minéraux, raphia'],
                    ['icon' => 'fas fa-users',   'label' => 'Fonction',  'value' => 'Justice, guerre, funérailles de guerriers'],
                ],
                'is_featured'  => 1,
                'is_active'    => 1,
                'sort_order'   => 8,
            ],
            [
                'slug'               => 'kedjenou-bete',
                'domain_id'          => $d['plats-traditionnels'],
                'people_roles'       => [
                    ['people_id' => $p['bete'],          'role' => 'peuple d\'origine'],
                    ['people_id' => $p['baoule'],        'role' => 'pratique adoptée'],
                ],
                'name'               => 'Le Kedjenou',
                'short_description'  => 'Ragoût de poulet ou de pintade mijoté à l\'étouffée dans une canari en terre, plat emblématique de Côte d\'Ivoire.',
                'description'        => 'Le Kedjenou (du bété "kedjenou" signifiant "qui remue peu") est un ragoût mijoté à l\'étouffée dans une canari (marmite en terre cuite) scellée avec des feuilles de bananier. La viande (poulet, pintade, agoutis) est marinée avec du gingembre, du poivre, des oignons et des épices locales, puis cuite très lentement sans ajout d\'eau. La vapeur interne suffit à cuire la viande qui devient extrêmement tendre et fondante. Servi avec de l\'attiéké ou du riz, c\'est l\'un des plats les plus appréciés de Côte d\'Ivoire.',
                'niveau_risque'      => 'stable',
                'practical_info'     => [
                    ['icon' => 'fas fa-fire',       'label' => 'Cuisson',     'value' => 'Étouffée en canari (sans eau)'],
                    ['icon' => 'fas fa-clock',      'label' => 'Durée',       'value' => '1h30 à 2h de cuisson lente'],
                    ['icon' => 'fas fa-utensils',   'label' => 'Accompagnement', 'value' => 'Attiéké, riz, banane plantain'],
                ],
                'is_featured'  => 1,
                'is_active'    => 1,
                'sort_order'   => 9,
            ],
            [
                'slug'               => 'tam-tam-bete',
                'domain_id'          => $d['musique-traditionnelle'],
                'people_roles'       => [['people_id' => $p['bete'], 'role' => 'peuple d\'origine']],
                'name'               => 'Tam-tam Parlant Bété',
                'short_description'  => 'Système de communication par tambour codifié permettant de transmettre des messages à grande distance.',
                'description'        => 'Le tam-tam parlant des Bété est un instrument de communication à part entière. Par un système de codes rythmiques codifiés, les batteurs transmettent des messages complets (annonces de décès, appels à rassemblement, alertes guerrières) sur des distances pouvant dépasser 10 km. Ce "langage du tambour" est un savoir transmis aux joueurs initiés et représente un des systèmes de télécommunication les plus sophistiqués des civilisations traditionnelles africaines.',
                'niveau_risque'      => 'en_danger',
                'practical_info'     => [
                    ['icon' => 'fas fa-music',  'label' => 'Instrument', 'value' => 'Tambour à fente (bois taillé à la hache)'],
                    ['icon' => 'fas fa-bolt',   'label' => 'Portée',     'value' => 'Jusqu\'à 10-15 km en forêt'],
                ],
                'is_featured'  => 0,
                'is_active'    => 1,
                'sort_order'   => 10,
            ],

            // ══════════════════════════════════════════════
            // GUÉRÉ
            // ══════════════════════════════════════════════
            [
                'slug'               => 'masque-tangle-guere',
                'domain_id'          => $d['masques-rituels'],
                'people_roles'       => [['people_id' => $p['guere-we'], 'role' => 'peuple d\'origine']],
                'name'               => 'Masque Tanglé (Juge Guéré)',
                'short_description'  => 'Masque de justice des Guéré, couvert de centaines d\'éléments symboliques, parmi les plus spectaculaires d\'Afrique.',
                'description'        => 'Le Tanglé est le masque de justice des Guéré — l\'un des masques africains les plus chargés et les plus impressionnants au monde. Son visage est recouvert de centaines d\'éléments : dents animales, plumes, cauris, clous, miroirs, poils et bien d\'autres matériaux symboliques. Son apparition marque la tenue d\'un tribunal traditionnel : il juge les conflits, prononce les sentences et peut condamner ou absoudre. Sa parole est loi.',
                'origine_historique' => 'Le Tanglé est au cœur de la vie judiciaire et spirituelle des Guéré depuis des siècles. L\'accumulation d\'objets sur le masque est un processus continu : chaque génération ajoute de nouveaux éléments, faisant du Tanglé un être vivant qui accumule la puissance du temps.',
                'niveau_risque'      => 'vulnerable',
                'unesco_status'      => 'Candidate au patrimoine UNESCO',
                'practical_info'     => [
                    ['icon' => 'fas fa-scale-balanced', 'label' => 'Fonction',   'value' => 'Tribunal traditionnel, justice'],
                    ['icon' => 'fas fa-map-pin',        'label' => 'Région',     'value' => 'Guiglo, Blolequin, Taï'],
                ],
                'is_featured'  => 1,
                'is_active'    => 1,
                'sort_order'   => 11,
            ],
            [
                'slug'               => 'societe-do-guere',
                'domain_id'          => $d['societes-secretes'],
                'people_roles'       => [['people_id' => $p['guere-we'], 'role' => 'peuple d\'origine']],
                'name'               => 'Société Secrète du Do',
                'short_description'  => 'Confrérie initiatique masculine guéré, gardienne des savoirs ancestraux et de l\'ordre social.',
                'description'        => 'La société secrète du Do est l\'institution centrale qui structure la vie sociale et spirituelle des Guéré. Elle contrôle l\'accès aux savoirs ésotériques, administre les initiations masculines et veille au respect des lois coutumières. Les membres du Do, reconnaissables à leurs scarifications et leurs coiffures initiatiques, sont les dépositaires des secrets du masque et des rituels de forêt. Leur autorité morale est supérieure à celle des chefs politiques.',
                'niveau_risque'      => 'vulnerable',
                'is_featured'  => 0,
                'is_active'    => 1,
                'sort_order'   => 12,
            ],

            // ══════════════════════════════════════════════
            // DAN (YACOUBA)
            // ══════════════════════════════════════════════
            [
                'slug'               => 'danse-echasses-dan',
                'domain_id'          => $d['acrobaties-echasses'],
                'people_roles'       => [['people_id' => $p['dan-yacouba'], 'role' => 'peuple d\'origine']],
                'name'               => 'Danse sur Échasses Dan',
                'short_description'  => 'Danse acrobatique sur échasses de 3 à 4 mètres, performance spectaculaire reconnue patrimoine immatériel de l\'humanité.',
                'description'        => 'Les danseurs sur échasses Dan évoluent sur des perches de 3 à 4 mètres de hauteur avec une agilité stupéfiante. Vêtus de pagnes colorés et coiffés de masques miniatures, ils exécutent des pirouettes, des sauts périlleux et des acrobaties défiant les lois de la physique. La danse sur échasses est à la fois un art sacré (liée aux esprits de la forêt) et un spectacle festif. Elle est inscrite au patrimoine culturel immatériel de l\'UNESCO depuis 2012.',
                'origine_historique' => 'Selon la tradition dan, la danse sur échasses a été enseignée aux hommes par les esprits de la forêt (Go). Les premiers danseurs auraient reçu ce don lors d\'une initiation en forêt sacrée. Aujourd\'hui pratiquée à Man et dans les villages de la région montagneuse, elle attire des visiteurs du monde entier.',
                'niveau_risque'      => 'stable',
                'unesco_status'      => 'Patrimoine culturel immatériel de l\'humanité (UNESCO 2012)',
                'meilleure_periode'  => ['Décembre', 'Janvier', 'Février', 'Août'],
                'practical_info'     => [
                    ['icon' => 'fas fa-map-pin',    'label' => 'Région',      'value' => 'Man et environs (District des Montagnes)'],
                    ['icon' => 'fas fa-person',     'label' => 'Pratiquants', 'value' => 'Garçons et jeunes hommes initiés'],
                    ['icon' => 'fas fa-calendar',   'label' => 'Occasions',   'value' => 'Fêtes villageoises, accueil d\'invités'],
                ],
                'is_featured'  => 1,
                'is_active'    => 1,
                'sort_order'   => 13,
            ],
            [
                'slug'               => 'masque-gueguee-dan',
                'domain_id'          => $d['masques-rituels'],
                'people_roles'       => [['people_id' => $p['dan-yacouba'], 'role' => 'peuple d\'origine']],
                'name'               => 'Masque de Course Guéguée',
                'short_description'  => 'Masque de course dan porté par un coureur d\'une vitesse extraordinaire, utilisé pour transmettre des messages urgents.',
                'description'        => 'Le Guéguée est un masque de course : son porteur, entraîné depuis l\'enfance, peut courir sur de longues distances à grande vitesse pour transmettre des messages urgents entre villages. Son visage de bois lisse aux traits fins est accompagné d\'une tenue de raphia légère. La course du Guéguée est en elle-même un rituel : l\'arrivée du masque dans un village annonce une nouvelle importante (naissance d\'un chef, fin d\'un deuil, convocation guerrière).',
                'niveau_risque'      => 'en_danger',
                'practical_info'     => [
                    ['icon' => 'fas fa-person-running', 'label' => 'Fonction',   'value' => 'Messager rituel inter-villages'],
                    ['icon' => 'fas fa-palette',        'label' => 'Style',      'value' => 'Visage lisse, traits fins, expression sereine'],
                ],
                'is_featured'  => 0,
                'is_active'    => 1,
                'sort_order'   => 14,
            ],
            [
                'slug'               => 'contes-araignee-dan',
                'domain_id'          => $d['contes-legendes'],
                'people_roles'       => [
                    ['people_id' => $p['dan-yacouba'],    'role' => 'peuple conteur'],
                    ['people_id' => $p['baoule'],         'role' => 'tradition partagée'],
                ],
                'name'               => 'Contes de l\'Araignée (Ananse)',
                'short_description'  => 'Cycle de contes mettant en scène Ananse l\'araignée, héros rusé symbole de l\'intelligence face à la force brute.',
                'description'        => 'Ananse l\'araignée est le personnage central d\'un cycle de contes partagé par de nombreux peuples d\'Afrique de l\'Ouest et qui a voyagé jusqu\'aux Antilles et en Amérique du Nord lors de la traite négrière. Dans ces récits, Ananse — petit et physiquement faible — triomphe toujours de ses adversaires puissants grâce à sa ruse et son intelligence. Les contes d\'Ananse sont racontés lors des veillées funèbres et des soirées villageoises, servant à transmettre des valeurs morales de façon ludique.',
                'niveau_risque'      => 'stable',
                'practical_info'     => [
                    ['icon' => 'fas fa-moon',       'label' => 'Moment',      'value' => 'Veillées nocturnes, funérailles'],
                    ['icon' => 'fas fa-book-open',  'label' => 'Transmission', 'value' => 'Orale, de grands-parents aux petits-enfants'],
                ],
                'is_featured'  => 0,
                'is_active'    => 1,
                'sort_order'   => 15,
            ],

            // ══════════════════════════════════════════════
            // DIOULA
            // ══════════════════════════════════════════════
            [
                'slug'               => 'kora-dioula',
                'domain_id'          => $d['musique-traditionnelle'],
                'people_roles'       => [
                    ['people_id' => $p['dioula-mandinka'], 'role' => 'peuple d\'origine'],
                    ['people_id' => $p['malinke'],         'role' => 'tradition partagée'],
                ],
                'name'               => 'La Kora',
                'short_description'  => 'Instrument à 21 cordes des griots mandé, à mi-chemin entre la harpe et le luth, symbole de la mémoire orale africaine.',
                'description'        => 'La kora est un instrument à cordes unique à l\'Afrique de l\'Ouest, dont la caisse de résonance est faite d\'une demi-calebasse recouverte de peau. Ses 21 cordes (7 graves à gauche, 7 médiums à droite, 7 aigus au centre) permettent une polyphonie complexe. Instrument des griots (djéli), gardiens de la mémoire et de l\'histoire orale, la kora accompagne les récits épiques, les louanges et les chants d\'amour. Son apprentissage commence dès l\'enfance et dure toute la vie.',
                'origine_historique' => 'La kora est née il y a environ 300 ans dans la région du Gabu (actuelle Guinée-Bissau) avant de se répandre dans tout l\'espace mandé. Son invention est attribuée au griot Koriyang Musa selon la tradition orale.',
                'niveau_risque'      => 'stable',
                'unesco_status'      => 'Inscrite sur la liste du patrimoine immatériel (UNESCO 2023)',
                'practical_info'     => [
                    ['icon' => 'fas fa-music',  'label' => 'Cordes',     'value' => '21 cordes (boyau ou nylon moderne)'],
                    ['icon' => 'fas fa-tools',  'label' => 'Fabrication', 'value' => 'Calebasse, peau de vache, bois de keng'],
                ],
                'is_featured'  => 1,
                'is_active'    => 1,
                'sort_order'   => 16,
            ],
            [
                'slug'               => 'bogolan-dioula',
                'domain_id'          => $d['tissage-textile'],
                'people_roles'       => [['people_id' => $p['dioula-mandinka'], 'role' => 'peuple d\'origine']],
                'name'               => 'Bogolan (Tissu Mud Cloth)',
                'short_description'  => 'Tissu de coton teint à la boue fermentée, aux motifs géométriques aux significations symboliques codifiées.',
                'description'        => 'Le bogolan est un tissu de coton filé et tissé à la main, décoré par application de boue fermentée (riche en fer) qui réagit avec les tanins du tissu pré-teint pour créer des motifs indélébiles de couleur brun foncé à noir sur fond ocre. Chaque motif géométrique (losanges, croix, lignes brisées) a une signification symbolique précise liée à des proverbes, des événements historiques ou des croyances religieuses. Jadis porté exclusivement lors des rites de passage (initiation, chasse, maternité), il est aujourd\'hui aussi un symbole de mode africaine contemporaine.',
                'niveau_risque'      => 'stable',
                'practical_info'     => [
                    ['icon' => 'fas fa-shirt',   'label' => 'Matière',   'value' => 'Coton, boue fermentée (barè-fini)'],
                    ['icon' => 'fas fa-palette', 'label' => 'Couleurs',  'value' => 'Ocre naturel, noir, blanc'],
                ],
                'is_featured'  => 0,
                'is_active'    => 1,
                'sort_order'   => 17,
            ],
            [
                'slug'               => 'attieke-national',
                'domain_id'          => $d['plats-traditionnels'],
                'people_roles'       => [
                    ['people_id' => $p['adjoukrou'],        'role' => 'peuple inventeur'],
                    ['people_id' => $p['attie'],            'role' => 'tradition partagée'],
                    ['people_id' => $p['dioula-mandinka'], 'role' => 'consommateur principal'],
                ],
                'name'               => 'L\'Attiéké',
                'short_description'  => 'Semoule de manioc fermentée et cuite à la vapeur, plat national de Côte d\'Ivoire, candidat UNESCO.',
                'description'        => 'L\'attiéké est la semoule de manioc fermentée, granulaire et légèrement acidulée, emblème culinaire de la Côte d\'Ivoire. Préparé exclusivement par les femmes selon un procédé qui inclut l\'épluchage, le pressage, la fermentation, le séchage et la cuisson à la vapeur du manioc râpé, il est consommé à tous les repas de la journée. L\'attiéké est servi avec du poisson braisé (poisson braisé sauce oignons), du poulet grillé, du kedjenou ou du garba (thon fumé). Sa préparation collective est un moment de sociabilité féminine fort.',
                'niveau_risque'      => 'stable',
                'unesco_status'      => 'Candidature déposée au patrimoine immatériel UNESCO (2024)',
                'practical_info'     => [
                    ['icon' => 'fas fa-fire',     'label' => 'Cuisson',       'value' => 'Vapeur (couscoussier)'],
                    ['icon' => 'fas fa-clock',    'label' => 'Préparation',   'value' => '2-3 jours (fermentation) + 2h cuisson'],
                    ['icon' => 'fas fa-utensils', 'label' => 'Accompagnement','value' => 'Poisson braisé, poulet, kedjenou'],
                ],
                'is_featured'  => 1,
                'is_active'    => 1,
                'sort_order'   => 18,
            ],

            // ══════════════════════════════════════════════
            // MALINKÉ
            // ══════════════════════════════════════════════
            [
                'slug'               => 'epopee-soundjata-keita',
                'domain_id'          => $d['epopees-griots'],
                'people_roles'       => [
                    ['people_id' => $p['malinke'],         'role' => 'peuple d\'origine'],
                    ['people_id' => $p['dioula-mandinka'], 'role' => 'tradition partagée'],
                ],
                'name'               => 'Épopée de Soundjata Keïta',
                'short_description'  => 'Récit épique fondateur de l\'empire du Mali, transmis oralement par les griots mandé depuis le XIIIe siècle.',
                'description'        => 'L\'épopée de Soundjata Keïta est le récit fondateur de l\'empire du Mali et l\'une des grandes épopées de l\'humanité. Elle narre la vie de Soundjata, né infirme et méprisé, qui surmonta son handicap pour devenir le plus grand guerrier de son époque et unifier les peuples mandé contre l\'oppresseur Soumaoro Kanté. Transmise oralement de génération en génération par les griots (djéli), cette épopée est récitée lors des grandes cérémonies avec accompagnement de kora et de balafon. Elle est le fondement de l\'identité culturelle mandé.',
                'origine_historique' => 'Les événements historiques se déroulent vers 1235 ap. J.-C. lors de la bataille de Kirina. Transmis oralement pendant 800 ans, le récit a été transcrit pour la première fois en 1960 par Djibril Tamsir Niane.',
                'niveau_risque'      => 'stable',
                'unesco_status'      => 'Patrimoine culturel immatériel de l\'humanité (UNESCO 2024)',
                'practical_info'     => [
                    ['icon' => 'fas fa-music',      'label' => 'Accompagnement', 'value' => 'Kora, balafon, dundun'],
                    ['icon' => 'fas fa-clock',      'label' => 'Durée',          'value' => '6 à 8 heures (récitation complète)'],
                ],
                'is_featured'  => 1,
                'is_active'    => 1,
                'sort_order'   => 19,
            ],
            [
                'slug'               => 'djembe-malinke',
                'domain_id'          => $d['musique-traditionnelle'],
                'people_roles'       => [['people_id' => $p['malinke'], 'role' => 'peuple d\'origine']],
                'name'               => 'Le Djembé',
                'short_description'  => 'Tambour gobelet à membrane de peau de chèvre, instrument de communication et de célébration des peuples mandé.',
                'description'        => 'Le djembé est un tambour en forme de calice taillé dans un tronc d\'arbre unique (généralement du lenké ou du dugura) et recouvert d\'une peau de chèvre. Ses trois sons fondamentaux (basse, ton, gifle) permettent une grande richesse rythmique. En Côte d\'Ivoire, il est l\'instrument de fête et de célébration par excellence chez les Malinké et les Dioula. Le maître djembéfola est un musicien respecté, gardien des rythmes rituels et festifs de la communauté.',
                'niveau_risque'      => 'stable',
                'practical_info'     => [
                    ['icon' => 'fas fa-music',  'label' => 'Sons',       'value' => '3 sons fondamentaux : basse, ton, gifle'],
                    ['icon' => 'fas fa-tools',  'label' => 'Matériaux',  'value' => 'Bois de lenké, peau de chèvre, cordes'],
                ],
                'is_featured'  => 0,
                'is_active'    => 1,
                'sort_order'   => 20,
            ],
            [
                'slug'               => 'confrérie-donzo-malinke',
                'domain_id'          => $d['societes-secretes'],
                'people_roles'       => [
                    ['people_id' => $p['malinke'],         'role' => 'peuple d\'origine'],
                    ['people_id' => $p['dioula-mandinka'], 'role' => 'tradition partagée'],
                ],
                'name'               => 'Confrérie des Chasseurs Donzo',
                'short_description'  => 'Société initiatique de chasseurs gardiens de la forêt, dépositaires de savoirs mystiques et médicinaux.',
                'description'        => 'Les Donzo sont une confrérie de chasseurs traditionnels qui jouent un rôle de gardiens de la forêt et de protecteurs des villages. Leur initiation, longue et exigeante, leur confère des savoirs botaniques (plantes médicinales, poisons), cynégétiques (connaissance des animaux) et mystiques (amulettes, gris-gris de protection). Reconnaissables à leur tenue de chasse faite de toile grège brodée de gris-gris et de cornes d\'animaux, les Donzo sont respectés et craints. Dans certaines régions, ils ont joué un rôle de milice communautaire lors des conflits.',
                'niveau_risque'      => 'stable',
                'practical_info'     => [
                    ['icon' => 'fas fa-tree',       'label' => 'Territoire', 'value' => 'Forêts et brousses du Nord et Centre-Nord'],
                    ['icon' => 'fas fa-shield',     'label' => 'Rôle',       'value' => 'Chasse, protection, médecine traditionnelle'],
                ],
                'is_featured'  => 0,
                'is_active'    => 1,
                'sort_order'   => 21,
            ],

            // ══════════════════════════════════════════════
            // SÉNOUFO
            // ══════════════════════════════════════════════
            [
                'slug'               => 'poro-senoufo',
                'domain_id'          => $d['societes-secretes'],
                'people_roles'       => [['people_id' => $p['senoufo'], 'role' => 'peuple d\'origine']],
                'name'               => 'Société d\'Initiation Poro',
                'short_description'  => 'Institution initiatique masculine centrale des Sénoufo, cycle de 21 ans structurant toute la vie sociale.',
                'description'        => 'Le Poro est la société d\'initiation masculine des Sénoufo et l\'institution la plus fondamentale de leur culture. Son cycle dure 21 ans, divisé en trois périodes de 7 ans, chacune marquée par des épreuves, des apprentissages et des rituels de passage. Les initiés apprennent l\'histoire du peuple, les secrets des masques sacrés, les techniques agricoles et artisanales, la musique et les valeurs morales. Le sacré bois du Poro (Sinzang) est le lieu de retraite des initiés, interdit aux femmes et aux non-initiés.',
                'origine_historique' => 'Le Poro est une institution commune à plusieurs peuples du nord de la Côte d\'Ivoire (Sénoufo, Toura, Bron). Son origine exacte est inconnue mais il est attesté depuis au moins le XVIe siècle. Sa survie à la colonisation et à l\'islamisation témoigne de sa profonde enracinement dans la société sénoufo.',
                'niveau_risque'      => 'vulnerable',
                'practical_info'     => [
                    ['icon' => 'fas fa-clock',  'label' => 'Durée du cycle', 'value' => '21 ans (3 × 7 ans)'],
                    ['icon' => 'fas fa-map-pin', 'label' => 'Région',        'value' => 'Korhogo, Boundiali, Ferkessédougou'],
                ],
                'is_featured'  => 1,
                'is_active'    => 1,
                'sort_order'   => 22,
            ],
            [
                'slug'               => 'masque-kpelie-senoufo',
                'domain_id'          => $d['masques-rituels'],
                'people_roles'       => [['people_id' => $p['senoufo'], 'role' => 'peuple d\'origine']],
                'name'               => 'Masque Kpelié',
                'short_description'  => 'Masque facial sénoufo en bois laqué, reconnaissable à ses élégantes appendices latérales en forme de jambes.',
                'description'        => 'Le Kpelié est le masque facial le plus emblématique des Sénoufo. De forme ovale, il représente un visage humain idéalisé avec des traits fins et réguliers. Sa caractéristique distinctive est la présence de deux ou quatre appendices latérales en forme de jambes humaines ou de crochets qui symbolisent les ancêtres. Utilisé lors des cérémonies du Poro, il est à la fois le visage d\'un ancêtre revenu parmi les vivants et un support de puissance surnaturelle. Les Kpelié les plus anciens et les plus chargés de magie sont les plus précieux.',
                'niveau_risque'      => 'vulnerable',
                'practical_info'     => [
                    ['icon' => 'fas fa-palette', 'label' => 'Style',    'value' => 'Visage ovale, appendices latérales, laqué noir'],
                    ['icon' => 'fas fa-eye',     'label' => 'Contexte', 'value' => 'Cérémonies du Poro, funérailles des initiés'],
                ],
                'is_featured'  => 1,
                'is_active'    => 1,
                'sort_order'   => 23,
            ],
            [
                'slug'               => 'balafon-senoufo',
                'domain_id'          => $d['musique-traditionnelle'],
                'people_roles'       => [
                    ['people_id' => $p['senoufo'],        'role' => 'peuple d\'origine'],
                    ['people_id' => $p['dioula-mandinka'],'role' => 'tradition partagée'],
                ],
                'name'               => 'Balafon Sénoufo',
                'short_description'  => 'Xylophone à calebasses résonnantes, instrument sacré du Poro et de toutes les cérémonies sénoufo.',
                'description'        => 'Le balafon sénoufo est un xylophone composé de lames de bois (lingué ou vène) de tailles différentes, suspendues sur un cadre en bois au-dessus de calebasses évidées servant de résonateurs. Chaque calebasse est percée d\'un trou obturé par une membrane de toile d\'araignée qui produit un bourdonnement caractéristique. Instrument des cérémonies du Poro et des funérailles des initiés, son jeu requiert des années d\'apprentissage. Certains balafons sacrés sont considérés comme des êtres vivants dotés d\'une âme.',
                'niveau_risque'      => 'stable',
                'practical_info'     => [
                    ['icon' => 'fas fa-music',  'label' => 'Lames',      'value' => '17 à 21 lames de bois'],
                    ['icon' => 'fas fa-tools',  'label' => 'Résonateurs','value' => 'Calebasses + membrane de toile d\'araignée'],
                ],
                'is_featured'  => 0,
                'is_active'    => 1,
                'sort_order'   => 24,
            ],
            [
                'slug'               => 'pagne-bande-senoufo',
                'domain_id'          => $d['tissage-textile'],
                'people_roles'       => [['people_id' => $p['senoufo'], 'role' => 'peuple d\'origine']],
                'name'               => 'Pagne Tissé à Bandes Sénoufo',
                'short_description'  => 'Pagne de coton tissé à bandes étroites aux motifs géométriques, symbolisant statut social et appartenance rituelle.',
                'description'        => 'Le pagne sénoufo est tissé sur un métier à bandes étroites (entre 5 et 12 cm de large) qui sont ensuite cousues ensemble pour former une pièce de tissu complète. Les motifs géométriques (damiers, chevrons, losanges) sont obtenus par l\'entrelacement de fils de chaîne et de trame de couleurs différentes. Chaque combinaison de motifs et de couleurs a une signification précise : certains pagnes ne peuvent être portés que par des initiés du Poro, d\'autres sont réservés aux funérailles ou aux mariages. Les tisserands sénoufo (kuntigui) occupent une caste respectée dans la société.',
                'niveau_risque'      => 'vulnerable',
                'practical_info'     => [
                    ['icon' => 'fas fa-shirt',   'label' => 'Largeur bandes', 'value' => '5 à 12 cm par bande'],
                    ['icon' => 'fas fa-tag',     'label' => 'Prix',           'value' => '20 000 – 150 000 FCFA'],
                ],
                'is_featured'  => 0,
                'is_active'    => 1,
                'sort_order'   => 25,
            ],

            // ══════════════════════════════════════════════
            // LOBI
            // ══════════════════════════════════════════════
            [
                'slug'               => 'figurines-thil-lobi',
                'domain_id'          => $d['sculpture-bois-bronze'],
                'people_roles'       => [['people_id' => $p['lobi'], 'role' => 'peuple d\'origine']],
                'name'               => 'Figurines Thil en Bronze',
                'short_description'  => 'Statuettes votivement chargées en bronze ou en bois, représentants des esprits protecteurs thil de la cosmologie lobi.',
                'description'        => 'Les thil (singulier : thila) sont des esprits protecteurs de la cosmologie lobi qui habitent des statuettes anthropomorphes ou zoomorphes en bronze, en bois ou en terre. Chaque famille possède ses propres thil, gardiens du foyer et des champs. Les figurines de bronze sont créées par fonte à la cire perdue et représentent des humains aux poses inhabituelles (bras en l\'air, position de combat) ou des animaux (caïman, python, crocodile). Ces œuvres, d\'une grande sobriété formelle, sont parmi les plus recherchées par les collectionneurs internationaux.',
                'origine_historique' => 'L\'art lobi est l\'un des plus anciens et des plus hermétiques d\'Afrique de l\'Ouest. Le refus historique des Lobi de tout pouvoir centralisé a préservé leur art d\'une codification institutionnelle, lui donnant une liberté formelle rare.',
                'niveau_risque'      => 'en_danger',
                'practical_info'     => [
                    ['icon' => 'fas fa-tools',  'label' => 'Technique',  'value' => 'Fonte à la cire perdue (bronze), taille directe (bois)'],
                    ['icon' => 'fas fa-map-pin', 'label' => 'Région',    'value' => 'Bouna, Doropo (Nord-Est CI)'],
                ],
                'is_featured'  => 1,
                'is_active'    => 1,
                'sort_order'   => 26,
            ],
            [
                'slug'               => 'soukala-lobi',
                'domain_id'          => $d['architecture-traditionnelle'],
                'people_roles'       => [['people_id' => $p['lobi'], 'role' => 'peuple d\'origine']],
                'name'               => 'La Soukala (Maison Fortifiée Lobi)',
                'short_description'  => 'Habitation collective en terre battue aux murs épais sans fenêtre, conçue comme une forteresse familiale.',
                'description'        => 'La soukala est la maison traditionnelle des Lobi : une bâtisse en banco (terre battue) à un étage, aux murs épais de 40 à 60 cm, sans fenêtres extérieures et avec une seule entrée étroite. Cette architecture défensive reflète la mentalité lobi de méfiance envers l\'extérieur. L\'intérieur est organisé autour d\'une cour centrale qui assure la lumière et la circulation de l\'air. Les toits plats servent de terrasse de travail et de couchage en saison chaude. La soukala est construite collectivement par la communauté lors d\'une fête qui peut durer plusieurs jours.',
                'niveau_risque'      => 'en_danger',
                'practical_info'     => [
                    ['icon' => 'fas fa-house',   'label' => 'Matériaux', 'value' => 'Banco (terre + paille + eau), bois de doum'],
                    ['icon' => 'fas fa-users',   'label' => 'Capacité',  'value' => '1 famille étendue (10 à 30 personnes)'],
                ],
                'is_featured'  => 0,
                'is_active'    => 1,
                'sort_order'   => 27,
            ],

            // ══════════════════════════════════════════════
            // ADJOUKROU
            // ══════════════════════════════════════════════
            [
                'slug'               => 'fete-abissa-adjoukrou',
                'domain_id'          => $d['fetes-traditionnelles'],
                'people_roles'       => [['people_id' => $p['adjoukrou'], 'role' => 'peuple d\'origine']],
                'name'               => 'Fête de l\'Abissa',
                'short_description'  => 'Grande fête de purification et de liberté totale des Adjoukrou à Grand-Lahou, moment de règlement collectif des comptes.',
                'description'        => 'L\'Abissa est la fête annuelle de purification des Adjoukrou, célébrée chaque année à Grand-Lahou et dans les villages adjoukrou en octobre-novembre. Pendant une semaine, toutes les règles sociales habituelles sont suspendues : chacun peut dire tout haut ce qu\'il pense des autres, régler ses rancœurs, se moquer des notables, et les femmes peuvent s\'habiller comme elles le souhaitent. Cette catharsis collective est organisée par classes d\'âge (echos) et s\'achève par une nuit de danse et de musique qui purifie l\'ensemble de la communauté pour l\'année à venir.',
                'origine_historique' => 'L\'Abissa est liée à l\'organisation en classes d\'âge (echos) qui est la spécificité de l\'organisation sociale adjoukrou. Chaque classe a des responsabilités précises dans l\'organisation de la fête. La tradition remonterait à la période de fondation du peuple adjoukrou sur la presqu\'île de Jacqueville.',
                'niveau_risque'      => 'stable',
                'meilleure_periode'  => ['Octobre', 'Novembre'],
                'practical_info'     => [
                    ['icon' => 'fas fa-calendar',  'label' => 'Période',      'value' => 'Octobre – Novembre (annuel)'],
                    ['icon' => 'fas fa-map-pin',   'label' => 'Lieu',         'value' => 'Grand-Lahou, Jacqueville, villages adjoukrou'],
                    ['icon' => 'fas fa-users',     'label' => 'Organisation', 'value' => 'Classes d\'âge (Echos)'],
                ],
                'is_featured'  => 1,
                'is_active'    => 1,
                'sort_order'   => 28,
            ],

            // ══════════════════════════════════════════════
            // ATTIÉ
            // ══════════════════════════════════════════════
            [
                'slug'               => 'ceremonie-adjao-attie',
                'domain_id'          => $d['funerailles-deuil'],
                'people_roles'       => [
                    ['people_id' => $p['attie'],    'role' => 'peuple d\'origine'],
                    ['people_id' => $p['agni-anyi'],'role' => 'tradition partagée'],
                ],
                'name'               => 'Cérémonie du Second Deuil (Adjao)',
                'short_description'  => 'Célébration funèbre collective organisée des mois après le décès, marquant la réconciliation avec la mort.',
                'description'        => 'L\'Adjao (ou "second deuil") est la cérémonie qui clôt officiellement le deuil, plusieurs mois ou parfois plusieurs années après le décès. C\'est un moment de grande célébration où la tristesse laisse place à la joie de la vie. La famille organise un festin, invite le village entier, loue des orchestres et des groupes de danse. Les proches du défunt exhibent les dons reçus lors du premier deuil. L\'Adjao est aussi l\'occasion de régler les questions d\'héritage et de redistribuer les biens du défunt selon la coutume.',
                'niveau_risque'      => 'stable',
                'practical_info'     => [
                    ['icon' => 'fas fa-clock',     'label' => 'Timing',       'value' => 'Plusieurs mois à 2 ans après le décès'],
                    ['icon' => 'fas fa-users',     'label' => 'Invités',      'value' => 'Village entier + familles alliées'],
                    ['icon' => 'fas fa-music',     'label' => 'Animation',    'value' => 'Orchestres, danses, festin'],
                ],
                'is_featured'  => 0,
                'is_active'    => 1,
                'sort_order'   => 29,
            ],
            [
                'slug'               => 'bangui-vin-palme',
                'domain_id'          => $d['boissons-traditionnelles'],
                'people_roles'       => [
                    ['people_id' => $p['attie'],           'role' => 'tradition forte'],
                    ['people_id' => $p['adjoukrou'],       'role' => 'tradition partagée'],
                    ['people_id' => $p['agni-anyi'],       'role' => 'tradition partagée'],
                ],
                'name'               => 'Le Bangui (Vin de Palme)',
                'short_description'  => 'Boisson fermentée extraite du palmier à huile, boisson rituelle et sociale de nombreux peuples lagunaires.',
                'description'        => 'Le bangui est la sève fermentée extraite du palmier à huile (Elaeis guineensis) par incision du stipe ou du régime. Collectée le matin et consommée fraîche, c\'est une boisson légèrement sucrée et gazeuse. Fermentée quelques heures, elle devient alcoolisée (2 à 8 degrés). Le bangui est une boisson de sociabilité et de rituel : on en verse toujours quelques gouttes à terre pour les ancêtres avant de boire. Dans les villages, les arbres à bangui sont soigneusement entretenus et le grimpeur de palmiers (banguimon) est un personnage important de la communauté.',
                'niveau_risque'      => 'stable',
                'practical_info'     => [
                    ['icon' => 'fas fa-leaf',     'label' => 'Source',      'value' => 'Sève de palmier à huile'],
                    ['icon' => 'fas fa-clock',    'label' => 'Collecte',    'value' => 'Tôt le matin (sève fraîche)'],
                    ['icon' => 'fas fa-wine-glass','label', 'Alcool',       'value' => '2 à 8° selon fermentation'],
                ],
                'is_featured'  => 0,
                'is_active'    => 1,
                'sort_order'   => 30,
            ],

        ];

        $count = 0;
        foreach ($elements as $el) {
            $slug = $el['slug'];
            unset($el['slug']);
            $el['slug'] = $slug;

            CulturalElement::updateOrCreate(['slug' => $slug], $el);
            $count++;
        }

        $total    = CulturalElement::count();
        $featured = CulturalElement::where('is_featured', 1)->count();
        $this->command->info("✓ {$count} éléments seedés — {$total} total en base ({$featured} en vedette).");
    }
}
