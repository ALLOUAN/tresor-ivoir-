<?php

namespace Database\Seeders;

use App\Models\CulturalDomain;
use App\Models\CulturalElement;
use App\Models\CulturalPeople;
use Database\Seeders\Concerns\SeedsMissingReferenceData;
use Database\Seeders\Data\IvorianSources as S;
use Illuminate\Database\Seeder;

/**
 * Éléments culturels ivoiriens absents du référentiel : patrimoine inscrit à l'UNESCO, fêtes,
 * masques, musiques, gastronomie et artisanat. À lancer après CulturalPeopleCompletionSeeder
 * (les peuples d'origine sont résolus par leur slug). N'écrase aucun élément existant ;
 * ajoute seulement les sources officielles UNESCO aux éléments déjà présents qui n'en ont pas.
 */
class CulturalElementCompletionSeeder extends Seeder
{
    use SeedsMissingReferenceData;

    public function run(): void
    {
        $d = CulturalDomain::pluck('id', 'slug');
        $p = CulturalPeople::pluck('id', 'slug');
        $roles = fn (array $slugs) => collect($slugs)->filter(fn ($s) => isset($p[$s]))
            ->map(fn ($s) => ['people_id' => $p[$s], 'role' => 'peuple d\'origine'])->values()->all();

        $gbofe = S::ich('Le Gbofe d\'Afounkaha, la musique des trompes traversières de la communauté tagbana', 'le-gbofe-d-afounkaha-la-musique-des-trompes-traversieres-de-la-communaute-tagbana-00005');
        $pagne = S::ich('Les savoir-faire traditionnels liés au tissage du pagne en Côte d\'Ivoire', 'les-savoir-faire-traditionnels-lies-au-tissage-du-pagne-en-cote-d-ivoire-01949');

        $elements = [
            // ── Patrimoine immatériel inscrit à l'UNESCO ────────────────────
            ['gbofe-afounkaha', 'musique-traditionnelle', ['tagbana'], 'Le Gbofe d\'Afounkaha',
                'Musique des trompes traversières de la communauté tagbana, inscrite au patrimoine culturel immatériel de l\'humanité.',
                "Le Gbofe est un ensemble de trompes traversières taillées dans des racines ou des cornes, accompagnées de tambours et de chants. Les trompes « parlent » : leurs mélodies reproduisent la langue tagbana et transmettent des messages, des louanges ou des conseils. L'ensemble joue lors des funérailles, des fêtes, des cérémonies et des grands rassemblements du village d'Afounkaha, près de Katiola.",
                "Pratique ancestrale de la communauté tagbana, le Gbofe a été proclamé chef-d'œuvre du patrimoine oral et immatériel de l'humanité, puis intégré en 2008 à la Liste représentative du patrimoine culturel immatériel de l'UNESCO.",
                'vulnerable', 'Liste représentative du patrimoine culturel immatériel de l\'humanité (UNESCO, 2008)', null,
                [['fas fa-map-pin', 'Lieu', 'Afounkaha, près de Katiola (Hambol)'], ['fas fa-calendar', 'Occasions', 'Funérailles, fêtes, cérémonies']],
                [$gbofe, S::UNESCO_PCI], true],
            ['tissage-pagne-cote-ivoire', 'tissage-textile', ['baoule', 'senoufo'], 'Le tissage traditionnel du pagne',
                'Savoir-faire du tissage du pagne sur métier traditionnel, inscrit au patrimoine culturel immatériel de l\'humanité en 2023.',
                "Dans de nombreuses régions du pays, des tisserands produisent sur des métiers à pédales des bandes de coton étroites, ensuite cousues entre elles pour former des pagnes. Les motifs, les couleurs et les assemblages varient selon les peuples (pagnes baoulé, sénoufo, dioula, malinké…) et portent souvent des noms et des significations. Le pagne tissé accompagne les grandes étapes de la vie : naissance, mariage, funérailles, intronisations.",
                "Transmis de génération en génération, ces savoir-faire ont été inscrits en 2023 sur la Liste représentative du patrimoine culturel immatériel de l'UNESCO, sous le titre « Les savoir-faire traditionnels liés au tissage du pagne en Côte d'Ivoire ».",
                'stable', 'Liste représentative du patrimoine culturel immatériel de l\'humanité (UNESCO, 2023)', null,
                [['fas fa-palette', 'Matières', 'Fil de coton, teintures'], ['fas fa-map-pin', 'Où voir', 'Waraniéné (Korhogo), Bomizambo (Tiébissou)…']],
                [$pagne, S::UNESCO_PCI], true],

            // ── Fêtes et cérémonies ─────────────────────────────────────────
            ['popo-carnaval-bonoua', 'fetes-traditionnelles', ['aboure'], 'Le Popo Carnaval de Bonoua',
                'Grand carnaval masqué du peuple abouré, à Bonoua : défilés, costumes et musique pendant plusieurs jours.',
                "Le Popo Carnaval de Bonoua est l'un des festivals les plus populaires du pays. Pendant plusieurs jours, la ville vit au rythme des défilés de masques et de costumes, des orchestres et des compétitions, avant le grand défilé final. L'événement attire chaque année des visiteurs de tout le pays et de l'étranger.",
                "Issu des traditions festives abouré, le carnaval s'est structuré au XXe siècle en un grand rendez-vous culturel et touristique organisé par la communauté de Bonoua.",
                'stable', null, ['Mars', 'Avril'],
                [['fas fa-map-pin', 'Lieu', 'Bonoua (Sud-Comoé)'], ['fas fa-users', 'Ambiance', 'Festive, ouverte au public']],
                [S::wiki('Abourés'), S::WIKI_DEMOGRAPHIE], true],
            ['fete-dipri-gomon', 'fetes-traditionnelles', ['abidji'], 'La fête du Dipri',
                'Fête rituelle annuelle du peuple abidji, à Gomon : nuit de purification, transes et exorcisme du village.',
                "Le Dipri est la grande fête du peuple abidji, célébrée à Gomon et dans les villages voisins. Elle commence la nuit par des rites de purification destinés à chasser le mal du village, puis se poursuit le jour par des démonstrations spectaculaires d'initiés en transe. C'est un moment de réconciliation et de renouveau pour toute la communauté.",
                "Le Dipri est rattaché par la tradition orale à un épisode fondateur de l'histoire des Abidji ; il est célébré chaque année et constitue l'un des rites les plus connus du sud du pays.",
                'vulnerable', null, ['Mars', 'Avril'],
                [['fas fa-map-pin', 'Lieu', 'Gomon (Agnéby-Tiassa)'], ['fas fa-triangle-exclamation', 'Respect', 'Rite sacré : suivre les consignes des organisateurs']],
                [S::WIKI_DEMOGRAPHIE, S::WIKI_LANGUES], false],
            ['paquinou-baoule', 'fetes-traditionnelles', ['baoule'], 'Le Paquinou',
                'Fête de Pâques en pays baoulé : retour au village, retrouvailles familiales et festivités.',
                "Le Paquinou (de « Pâques ») est le grand moment de retrouvailles du pays baoulé. Pendant le week-end pascal, les habitants installés en ville reviennent au village : réunions de famille, danses, concerts, compétitions sportives et projets de développement communautaire se succèdent.",
                "Né de la rencontre entre la fête chrétienne de Pâques et les traditions baoulé de rassemblement, le Paquinou est devenu au XXe siècle la principale fête du centre du pays, notamment autour de Bouaké.",
                'stable', null, ['Mars', 'Avril'],
                [['fas fa-map-pin', 'Région', 'Pays baoulé (Bouaké, Yamoussoukro…)'], ['fas fa-calendar', 'Date', 'Week-end de Pâques']],
                [S::wiki('Baoulés'), S::WIKI_DEMOGRAPHIE], false],
            ['adjanou-baoule', 'danses-populaires', ['baoule'], 'L\'Adjanou',
                'Danse rituelle des femmes baoulé, exécutée pour conjurer le malheur et lors des funérailles de grandes personnalités.',
                "L'Adjanou est une danse exclusivement féminine du pays baoulé. Les femmes la pratiquent pour conjurer une menace, lors de crises ou à l'occasion des funérailles de personnalités. Les chants, souvent satiriques ou protecteurs, sont accompagnés de gestes codifiés.",
                "Ancienne pratique rituelle des femmes baoulé, l'Adjanou est aujourd'hui aussi présentée lors d'événements culturels, tout en gardant son caractère protecteur.",
                'vulnerable', null, null,
                [['fas fa-user', 'Pratiquantes', 'Femmes uniquement']],
                [S::wiki('Baoulés')], false],

            // ── Masques et danses masquées ──────────────────────────────────
            ['masque-goli-baoule', 'danse-des-masques', ['baoule'], 'Le Goli',
                'Grande danse masquée baoulé réunissant plusieurs masques, du masque-disque au masque-heaume à cornes.',
                "Le Goli est une cérémonie masquée du pays baoulé qui fait se succéder plusieurs paires de masques dans un ordre précis : les masques-disques (kplé kplé), les masques-heaumes zoomorphes et enfin les masques à visage humain. Il est dansé lors des funérailles et des grandes fêtes villageoises.",
                "Les Baoulé auraient emprunté le Goli à leurs voisins Wan au XIXe siècle, avant d'en faire l'une de leurs danses masquées les plus importantes.",
                'vulnerable', null, null,
                [['fas fa-theater-masks', 'Masques', 'Kplé kplé, Goli glin, Kpan']],
                [S::wiki('Baoulés')], false],
            ['masques-gouro-zamble-gu', 'masques-rituels', ['gouro'], 'Les masques gouro Zamblé et Gu',
                'Masques sacrés du pays gouro : le Zamblé, mi-antilope mi-félin, et le Gu, son épouse au visage humain.',
                "Chez les Gouro, le Zamblé (masque zoomorphe à mâchoire de félin et cornes d'antilope) et le Gu (masque à visage féminin, considéré comme son épouse) forment, avec le Zaouli, une triade de masques qui dansent lors des funérailles et des fêtes. Leur sculpture est réputée pour son élégance.",
                "Ces masques, liés aux croyances et à l'organisation sociale gouro, sont aujourd'hui présents dans les grandes collections d'art africain.",
                'vulnerable', null, null,
                [['fas fa-map-pin', 'Région', 'Bouaflé, Zuénoula (Marahoué)']],
                [S::wiki('Gouro_(peuple)', 'Gouro (peuple)')], false],
            ['danse-boloye-senoufo', 'danse-des-masques', ['senoufo'], 'Le Boloye, danse des hommes-panthères',
                'Danse sénoufo des hommes-panthères, exécutée en costume moucheté lors des funérailles et des fêtes.',
                "Le Boloye est une danse sénoufo exécutée par des danseurs vêtus de costumes de cotonnade mouchetée évoquant la panthère, au son des tambours et des balafons. Spectaculaire et rythmée, elle accompagne notamment les funérailles et les sorties liées à l'initiation.",
                "Le Boloye appartient aux traditions des sociétés initiatiques sénoufo de la région de Korhogo.",
                'vulnerable', null, null,
                [['fas fa-map-pin', 'Région', 'Korhogo et environs (Poro)']],
                [S::wiki('Sénoufos')], false],
            ['masques-yaoure', 'masques-rituels', ['yaoure'], 'Les masques yaouré',
                'Masques à visage humain d\'une grande finesse, sculptés par le peuple yaouré du centre.',
                "Les masques yaouré se reconnaissent à leur visage humain stylisé, encadré d'une bordure dentelée et souvent surmonté d'un animal ou d'une coiffe. Ils sortent lors des funérailles et des cérémonies religieuses.",
                "À la charnière des mondes gouro et baoulé, les sculpteurs yaouré ont développé un style propre, très recherché dans les collections d'art africain.",
                'vulnerable', null, null, [],
                [S::WIKI_DEMOGRAPHIE], false],

            // ── Sculpture et artisanat ──────────────────────────────────────
            ['blolo-bla-bian-baoule', 'sculpture-bois-bronze', ['baoule'], 'Les statuettes Blolo bla et Blolo bian',
                'Statuettes baoulé représentant l\'épouse ou l\'époux de l\'« autre monde », sculptées sur conseil d\'un devin.',
                "Selon les croyances baoulé, chacun possède dans l'« autre monde » (blolo) un conjoint spirituel : une épouse (blolo bla) pour les hommes, un époux (blolo bian) pour les femmes. Lorsque ce conjoint se manifeste par des troubles, un devin peut conseiller de faire sculpter une statuette à son image, que l'on honore ensuite d'offrandes.",
                "Ces statuettes, d'une grande finesse, comptent parmi les œuvres les plus admirées de la sculpture baoulé.",
                'stable', null, null,
                [['fas fa-palette', 'Matériau', 'Bois sculpté et poli']],
                [S::wiki('Baoulés')], false],
            ['poids-a-peser-or-akan', 'sculpture-bois-bronze', ['baoule', 'agni-anyi', 'abron-brong'], 'Les poids à peser l\'or akan',
                'Petits poids en laiton coulés à la cire perdue, servant autrefois à peser la poudre d\'or et illustrant proverbes et récits.',
                "Les peuples akan utilisaient de petits poids en laiton pour peser la poudre d'or, monnaie d'échange. Coulés à la cire perdue, ils représentent des animaux, des personnages, des objets ou des motifs géométriques, et nombre d'entre eux illustrent des proverbes. Ils forment aujourd'hui un vaste répertoire de l'art et de la sagesse akan.",
                "Leur usage, lié au commerce de l'or dans l'aire akan (Côte d'Ivoire et Ghana), a décliné avec l'arrivée des monnaies modernes ; ils restent des objets de collection et de transmission.",
                'disparu', null, null,
                [['fas fa-palette', 'Technique', 'Fonte du laiton à la cire perdue']],
                [S::wiki('Baoulés'), S::wiki('Agnis')], false],
            ['toiles-de-korhogo', 'tissage-textile', ['senoufo'], 'Les toiles de Korhogo',
                'Cotonnades sénoufo peintes à la main de motifs d\'animaux et de masques, nées au village de Fakaha.',
                "Les toiles de Korhogo sont des cotonnades tissées à la main sur lesquelles les artisans peignent des motifs d'animaux, de masques et de scènes rituelles, à l'aide de teintures végétales et de boue fermentée. Autrefois réservées aux vêtements rituels de chasseurs et d'initiés, elles sont devenues un artisanat emblématique du nord du pays.",
                "Le village de Fakaha, près de Korhogo, est considéré comme le berceau de cette tradition, qui a pris son essor comme art décoratif au XXe siècle.",
                'stable', null, null,
                [['fas fa-map-pin', 'Où voir', 'Fakaha et Korhogo (Poro)']],
                [S::wiki('Sénoufos')], false],
            ['poterie-katiola', 'poterie-ceramique', [], 'La poterie de Katiola',
                'Poteries montées à la main et cuites en plein air par les femmes potières de Katiola.',
                "À Katiola, des femmes potières façonnent à la main jarres, canaris et récipients de cuisine, sans tour, puis les cuisent en plein air. Ces poteries, solides et décoratives, sont vendues dans tout le pays.",
                "Cette tradition, transmise de mère en fille, fait la réputation de Katiola et constitue une ressource importante pour les familles de potières.",
                'vulnerable', null, null,
                [['fas fa-map-pin', 'Lieu', 'Katiola (Hambol)']],
                [S::WIKI_DEMOGRAPHIE], false],
            ['pagne-raphia-dida', 'tissage-textile', ['dida'], 'Les pagnes en raphia dida',
                'Textiles en fibres de raphia tissés et teints par réserve, aux motifs géométriques, propres au pays dida.',
                "Les Dida tissent des pièces en fibres de raphia qu'ils teignent selon une technique de réserve (nœuds et ligatures) : il en résulte des motifs de cercles et de losanges aux couleurs naturelles. Ces étoffes, portées lors des cérémonies et des funérailles, comptent parmi les textiles les plus originaux du pays.",
                "Cette tradition textile ancienne, propre au pays dida (Divo, Lakota), est aujourd'hui pratiquée par un nombre réduit d'artisans.",
                'en_danger', null, null,
                [['fas fa-map-pin', 'Région', 'Divo, Lakota (Lôh-Djiboua)']],
                [S::wiki('Didas', 'Dida (peuple)')], false],

            // ── Musique ─────────────────────────────────────────────────────
            ['attoungblan-tambour-parleur', 'musique-traditionnelle', ['agni-anyi', 'baoule', 'abron-brong'], 'L\'Attoungblan, tambour parleur akan',
                'Paire de tambours qui « parlent » en reproduisant la langue, au service des cours royales akan.',
                "L'Attoungblan est une paire de tambours (l'un « mâle », l'autre « femelle ») dont le joueur reproduit les tons de la langue pour transmettre des messages, des louanges ou des appels. Il accompagne les cérémonies des cours royales akan, les fêtes et les funérailles.",
                "Instrument de prestige lié à la royauté, l'Attoungblan est joué par des musiciens spécialisés qui en transmettent le langage de génération en génération.",
                'vulnerable', null, null, [],
                [S::wiki('Agnis'), S::wiki('Baoulés')], false],
            ['ziglibithy', 'musique-contemporaine', ['bete'], 'Le Ziglibithy',
                'Musique populaire créée dans les années 1970 par Ernesto Djédjé à partir des rythmes bété.',
                "Le Ziglibithy est un genre musical urbain né dans les années 1970, qui modernise les rythmes traditionnels bété avec guitares, cuivres et batterie. Son créateur, Ernesto Djédjé, en a fait l'une des premières musiques ivoiriennes modernes à connaître un grand succès populaire.",
                "La disparition d'Ernesto Djédjé en 1983 n'a pas éteint son influence : le Ziglibithy a ouvert la voie aux musiques urbaines ivoiriennes qui ont suivi.",
                'stable', null, null, [],
                [S::wiki('Bétés')], false],
            ['zouglou', 'musique-contemporaine', [], 'Le Zouglou',
                'Musique et danse urbaines nées au début des années 1990 dans le milieu étudiant d\'Abidjan.',
                "Le Zouglou est né au début des années 1990 parmi les étudiants d'Abidjan. Chanté en français populaire et en nouchi, il raconte avec humour et lucidité la vie quotidienne, les difficultés sociales et les espoirs de la jeunesse. Groupes et artistes l'ont porté dans toute l'Afrique francophone.",
                "Issu des chants d'ambiance des cités universitaires, le Zouglou est devenu en quelques années l'une des grandes musiques populaires du pays.",
                'stable', null, null, [],
                [S::WIKI_DEMOGRAPHIE], false],
            ['coupe-decale', 'musique-contemporaine', [], 'Le Coupé-décalé',
                'Musique et danse festives nées en 2002 entre Paris et Abidjan, devenues un phénomène africain.',
                "Le Coupé-décalé est apparu en 2002, porté par des artistes de la diaspora ivoirienne à Paris puis repris à Abidjan. Musique festive aux rythmes rapides, accompagnée de danses aux noms imagés, il est rapidement devenu un phénomène dans toute l'Afrique francophone et au-delà.",
                "Le genre s'est diffusé à une période de crise politique, comme une réponse festive à la morosité ; il reste l'une des musiques ivoiriennes les plus exportées.",
                'stable', null, null, [],
                [S::WIKI_DEMOGRAPHIE], false],

            // ── Langue ──────────────────────────────────────────────────────
            ['nouchi', 'langue-litterature-orale', [], 'Le Nouchi',
                'Parler urbain d\'Abidjan mêlant le français et les langues ivoiriennes, langue de la rue, de la musique et de la créativité.',
                "Le Nouchi est un parler urbain né à Abidjan, qui mélange le français, le dioula, le baoulé, le bété et d'autres langues, avec un vocabulaire sans cesse renouvelé. D'abord langue des jeunes de la rue, il est devenu un marqueur de l'identité urbaine ivoirienne, très présent dans la musique (Zouglou, Coupé-décalé), la publicité et les réseaux sociaux.",
                "Apparu dans les années 1970-1980, le Nouchi a connu une diffusion massive avec l'essor des musiques urbaines et s'enrichit chaque année de nouvelles expressions.",
                'stable', null, null, [],
                [S::WIKI_LANGUES], false],

            // ── Gastronomie ─────────────────────────────────────────────────
            ['alloco', 'plats-traditionnels', [], 'L\'Alloco',
                'Bananes plantains mûres frites dans l\'huile, servies avec piment, oignons ou poisson : le grand classique de la rue.',
                "L'alloco est préparé avec des bananes plantains bien mûres, coupées en rondelles ou en dés et frites dans l'huile. Il se déguste seul, avec une sauce pimentée et des oignons, ou en accompagnement de poisson frit, de poulet ou d'œufs. On le trouve partout, des maquis aux étals de rue.",
                "Le quartier d'Abidjan surnommé « Allocodrome » (à Cocody) témoigne de la place de ce plat dans la culture urbaine ivoirienne.",
                'stable', null, null, [],
                [S::WIKI_DEMOGRAPHIE], false],
            ['garba', 'plats-traditionnels', [], 'Le Garba',
                'Attiéké accompagné de thon frit, de piment et d\'oignons : le plat populaire et économique par excellence.',
                "Le garba associe de l'attiéké (semoule de manioc fermenté) à des morceaux de thon frit, relevés de piment frais, d'oignons et de tomates. Plat rapide et bon marché, il est vendu dans des échoppes appelées « garbadromes » dans tout le pays.",
                "Popularisé dans les années 1980-1990, le garba est devenu un symbole de la cuisine de rue abidjanaise.",
                'stable', null, null, [],
                [S::ich('Les savoir-faire liés à la fabrication de l\'attiéké en Côte d\'Ivoire', 'les-savoir-faire-lies-a-la-fabrication-de-l-attieke-en-cote-d-ivoire-02086')], false],
            ['foutou-sauce-graine', 'plats-traditionnels', ['baoule', 'agni-anyi'], 'Le Foutou et la sauce graine',
                'Pâte de banane plantain ou d\'igname pilée, servie avec la sauce graine à base de noix de palme.',
                "Le foutou est une pâte obtenue en pilant longuement de la banane plantain ou de l'igname cuite. Il est servi avec une sauce : la plus célèbre est la sauce graine, préparée à partir de la pulpe de noix de palme, avec viande, poisson fumé ou escargots. C'est l'un des repas familiaux les plus courants du sud et du centre.",
                "Plat des peuples akan et forestiers, le foutou accompagne les repas du quotidien comme les fêtes.",
                'stable', null, null, [],
                [S::wiki('Baoulés')], false],
            ['placali', 'plats-traditionnels', ['adjoukrou', 'attie'], 'Le Placali',
                'Pâte de manioc fermenté, lisse et élastique, servie avec la sauce kplala ou la sauce graine.',
                "Le placali est une pâte préparée à partir de manioc fermenté, cuite jusqu'à devenir lisse et élastique. Plat des peuples lagunaires du sud, il est servi avec des sauces gluantes comme la sauce kplala (à base de feuilles) ou la sauce graine.",
                "Comme l'attiéké, le placali témoigne du savoir-faire des peuples lagunaires dans la transformation du manioc.",
                'stable', null, null, [],
                [S::WIKI_DEMOGRAPHIE], false],
            ['tchapalo', 'boissons-traditionnelles', ['senoufo'], 'Le Tchapalo',
                'Bière traditionnelle de mil ou de sorgho, brassée par les femmes dans le nord du pays.',
                "Le tchapalo est une bière traditionnelle préparée à partir de mil ou de sorgho germé, cuit puis fermenté. Brassé le plus souvent par des femmes, il est servi dans des calebasses lors des rencontres, des fêtes et des travaux collectifs, principalement dans le nord.",
                "Boisson de convivialité très ancienne dans les savanes, le tchapalo a suivi les migrations vers le sud et se trouve aujourd'hui aussi dans les quartiers d'Abidjan.",
                'stable', null, null, [],
                [S::wiki('Sénoufos')], false],
            ['gnamakoudji', 'boissons-traditionnelles', [], 'Le Gnamakoudji',
                'Jus de gingembre frais, sucré et parfois relevé d\'ananas ou de citron, servi bien frais.',
                "Le gnamakoudji est une boisson à base de gingembre frais râpé, filtré et sucré, parfois parfumé d'ananas, de citron ou de menthe. Rafraîchissant et piquant, il est vendu en sachets ou en bouteilles dans tout le pays et servi lors des fêtes et des cérémonies.",
                "Son nom vient du dioula ; la boisson est devenue l'un des grands classiques de la consommation populaire ivoirienne.",
                'stable', null, null, [],
                [S::WIKI_LANGUES], false],
        ];

        $rows = [];
        $order = 100;
        foreach ($elements as [$slug, $domain, $peoples, $name, $short, $description, $origine, $risk, $unesco, $periode, $info, $sources, $featured]) {
            if (! isset($d[$domain])) {
                $this->command?->warn("Élément « {$name} » ignoré : domaine « {$domain} » introuvable.");

                continue;
            }
            $rows[] = [
                'slug' => $slug,
                'domain_id' => $d[$domain],
                'people_roles' => $roles($peoples),
                'name' => $name,
                'short_description' => $short,
                'description' => $description,
                'origine_historique' => $origine,
                'niveau_risque' => $risk,
                'unesco_status' => $unesco,
                'meilleure_periode' => $periode,
                'practical_info' => array_map(fn ($i) => ['icon' => $i[0], 'label' => $i[1], 'value' => $i[2]], $info),
                'is_featured' => $featured,
                'is_active' => true,
                'sort_order' => $order++,
                'sources' => $sources,
            ];
        }

        $this->report('Éléments culturels', $this->seedMissing(CulturalElement::class, $rows));

        // Sources officielles UNESCO pour les éléments déjà présents (seulement s'ils n'en ont pas).
        $existing = [
            'danse-zaouli-baoule' => [S::ich('Le Zaouli, musique et danse populaires des communautés gouro de Côte d\'Ivoire', 'le-zaouli-musique-et-danse-populaires-des-communautes-gouro-de-cote-d-ivoire-01255'), S::UNESCO_PCI],
            'balafon-senoufo' => [S::ich('Les pratiques et expressions culturelles liées au balafon (et au kolintang)', 'les-pratiques-et-expressions-culturelles-liees-au-balafon-et-au-kolintang-au-mali-au-burkina-faso-en-cote-d-ivoire-et-en-indonesie-02131'), S::UNESCO_PCI],
            'attieke-national' => [S::ich('Les savoir-faire liés à la fabrication de l\'attiéké en Côte d\'Ivoire', 'les-savoir-faire-lies-a-la-fabrication-de-l-attieke-en-cote-d-ivoire-02086'), S::UNESCO_PCI],
            'tissu-kita-baoule' => [$pagne],
            'pagne-bande-senoufo' => [$pagne],
        ];
        $rows = [];
        foreach ($existing as $slug => $sources) {
            if (CulturalElement::where('slug', $slug)->exists()) {
                $rows[] = ['slug' => $slug, 'sources' => $sources];
            }
        }
        $this->report('Sources des éléments existants', $this->seedMissing(CulturalElement::class, $rows));
    }
}
