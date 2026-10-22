<?php

namespace Database\Seeders;

use App\Models\CulturalPeople;
use Database\Seeders\Concerns\SeedsMissingReferenceData;
use Database\Seeders\Data\IvorianSources as S;
use Illuminate\Database\Seeder;

/**
 * Peuples de Côte d'Ivoire absents du référentiel (le pays en compte plus de soixante), répartis
 * dans les cinq grands groupes : Akan (dont les peuples lagunaires), Krou, Mandé du Nord,
 * Mandé du Sud et Gour (Voltaïque).
 *
 * Les effectifs ne sont pas renseignés : le recensement 2021 (INS) publie les grands groupes,
 * pas le détail par peuple. Aucune photo : à ajouter depuis l'administration (Cultures › Peuples).
 * N'écrase aucun peuple existant ; ajoute seulement leurs sources s'ils n'en ont pas.
 */
class CulturalPeopleCompletionSeeder extends Seeder
{
    use SeedsMissingReferenceData;

    public function run(): void
    {
        $general = [S::WIKI_DEMOGRAPHIE, S::WIKI_LANGUES, S::ETHNOLOGUE, S::INS_RGPH];

        $peoples = [
            // ── AKAN lagunaires (Sud) ────────────────────────────────────────
            $this->people('Abbey (Abé)', 'abbey-abe', 'Sud', 'Kwa (Akan lagunaire)', 'Abbey', 'Agboville',
                "Peuple akan lagunaire de la région de l'Agnéby-Tiassa, les Abbey vivent autour d'Agboville, dans une zone de forêt et de plantations. Leur société est organisée en classes d'âge, qui structurent la vie collective et les fêtes de génération.",
                "Comme les autres peuples lagunaires, les Abbey se sont installés dans le sud-est forestier à la suite des grands mouvements de population akan. Agboville est devenue au XXe siècle un important carrefour sur la ligne de chemin de fer Abidjan–Niger.",
                [S::wiki('Abés', 'Abés (Abbey)'), ...$general]),
            $this->people('Abouré', 'aboure', 'Sud', 'Kwa (Akan lagunaire)', 'Abouré', 'Bonoua',
                "Peuple akan du Sud-Comoé, les Abouré sont établis autour de Bonoua et de Moossou, près de Grand-Bassam. Ils sont célèbres pour le Popo Carnaval de Bonoua, grand festival masqué et coloré.",
                "Les Abouré font partie des peuples akan venus de l'est et installés près du littoral. Leur organisation en classes d'âge et leurs fêtes de génération rythment la vie sociale.",
                [S::wiki('Abourés'), ...$general]),
            $this->people('Abidji', 'abidji', 'Sud', 'Kwa (Akan lagunaire)', 'Abidji', 'Sikensi',
                "Peuple lagunaire de la région de Sikensi et de Gomon, les Abidji sont connus dans tout le pays pour le Dipri, fête rituelle de purification et de renouveau.",
                "Les Abidji occupent un territoire forestier à l'ouest d'Agboville. Leur fête du Dipri, célébrée chaque année, attire de nombreux visiteurs à Gomon.",
                $general),
            $this->people('Alladian', 'alladian', 'Sud', 'Kwa (Akan lagunaire)', 'Alladian', 'Jacqueville',
                "Peuple du cordon littoral entre la lagune Ébrié et l'océan, les Alladian vivent de la pêche et du commerce autour de Jacqueville. Leur région conserve d'anciennes maisons de l'époque coloniale.",
                "Peuple de pêcheurs et de commerçants, les Alladian ont été des intermédiaires actifs dans les échanges entre l'intérieur et les navires européens sur la côte.",
                $general),
            $this->people('Avikam', 'avikam', 'Sud', 'Kwa (Akan lagunaire)', 'Avikam', 'Grand-Lahou',
                "Peuple lagunaire de l'embouchure du Bandama, les Avikam vivent à Grand-Lahou et dans les villages de la lagune Tagba.",
                "Grand-Lahou, au débouché du fleuve Bandama, fut un comptoir de traite actif ; les Avikam y ont joué un rôle d'intermédiaires commerciaux.",
                $general),
            $this->people('Ébrié (Tchaman)', 'ebrie-tchaman', 'Sud', 'Kwa (Akan lagunaire)', 'Ébrié (Tchaman)', 'Abidjan',
                "Peuple autochtone de la région d'Abidjan, les Ébrié (qui se nomment Tchaman) ont donné leur nom à la lagune Ébrié. Leurs villages, comme Anoumabo, Adjamé ou Blockhauss, sont aujourd'hui au cœur de la métropole.",
                "Les Ébrié sont organisés en classes d'âge (générations) qui se transmettent les responsabilités au cours de grandes cérémonies. L'urbanisation d'Abidjan au XXe siècle s'est faite en grande partie sur leurs terres.",
                $general),
            $this->people('Éotilé (Ehotilé)', 'eotile-ehotile', 'Sud', 'Kwa (Akan lagunaire)', 'Éotilé', 'Adiaké',
                "Peuple lagunaire de la lagune Aby, autour d'Adiaké, les Éotilé considèrent les Îles Ehotilé, aujourd'hui parc national, comme leur berceau sacré.",
                "Installés anciennement sur les îles de la lagune Aby, les Éotilé ont ensuite été intégrés au royaume agni du Sanwi, tout en conservant leur langue et leurs lieux sacrés.",
                [S::wiki('Parc_national_des_îles_Ehotilé', 'Parc national des îles Ehotilé'), ...$general]),
            $this->people('N\'Zima (Appolo)', 'nzima-appolo', 'Sud', 'Kwa (Akan)', 'Nzema', 'Grand-Bassam',
                "Peuple akan du littoral, présent à Grand-Bassam et de part et d'autre de la frontière avec le Ghana, les N'Zima célèbrent chaque année l'Abissa, grande fête de purification et de liberté de parole.",
                "Venus de l'actuel Ghana, les N'Zima se sont établis dans la région de Grand-Bassam ; ils y ont une royauté et une fête annuelle, l'Abissa, qui se tient généralement entre octobre et novembre.",
                $general),

            // ── KROU (Ouest et Sud-Ouest) ────────────────────────────────────
            $this->people('Dida', 'dida', 'Sud', 'Krou', 'Dida', 'Divo',
                "Peuple krou de la région de Divo et de Lakota, les Dida sont réputés pour leurs pagnes en fibres de raphia, tissés et teints selon une technique de réserve aux motifs géométriques.",
                "Les Dida occupent une zone de forêt du centre-sud, à l'est du pays bété. Leur artisanat du raphia est l'un des plus originaux du pays.",
                [S::wiki('Didas'), ...$general]),
            $this->people('Godié', 'godie', 'Sud', 'Krou', 'Godié', 'Lakota',
                "Peuple krou du centre-sud, les Godié vivent entre Lakota, Fresco et Sassandra.",
                "Voisins des Dida et des Bété, les Godié partagent avec eux de nombreux traits culturels du monde krou : organisation lignagère, danses masquées et littérature orale.",
                [S::wiki('Godiés'), ...$general]),
            $this->people('Néyo', 'neyo', 'Sud', 'Krou', 'Néyo', 'Sassandra',
                "Peuple krou du littoral, les Néyo sont établis à Sassandra et dans les villages côtiers voisins, tournés vers la pêche et l'océan.",
                "Installés autour de l'embouchure du Sassandra, les Néyo ont connu très tôt les échanges avec les navigateurs européens.",
                [S::wiki('Néyo'), ...$general]),
            $this->people('Kroumen', 'kroumen', 'Ouest', 'Krou', 'Kroumen', 'Tabou',
                "Peuple krou du littoral sud-ouest, autour de Tabou et de San-Pédro, les Kroumen sont réputés pour leur tradition maritime : leurs marins et piroguiers ont longtemps servi sur les navires de la côte ouest-africaine.",
                "Le nom « Krou » donné aux marins de la côte (les « Krou boys ») témoigne de cette longue tradition de navigation, partagée avec les peuples krou du Libéria voisin.",
                [S::wiki('Kroumen'), ...$general]),
            $this->people('Bakwé', 'bakwe', 'Ouest', 'Krou', 'Bakwé', 'Soubré',
                "Peuple krou du Bas-Sassandra, les Bakwé vivent dans la région de Soubré et de San-Pédro, dans une zone de forêt devenue la grande région du cacao.",
                "Peuple forestier peu nombreux, les Bakwé ont vu leur territoire profondément transformé par l'essor des plantations à partir des années 1970.",
                [S::wiki('Bakwé_(peuple)', 'Bakwé (peuple)'), ...$general]),

            // ── MANDÉ DU SUD (Centre-Ouest et Ouest) ─────────────────────────
            $this->people('Gouro', 'gouro', 'Centre', 'Mandé du Sud', 'Gouro', 'Bouaflé',
                "Peuple mandé du centre-ouest (Bouaflé, Zuénoula), les Gouro sont célèbres pour leurs masques sculptés et pour le Zaouli, musique et danse masquée inscrite au patrimoine culturel immatériel de l'UNESCO en 2017.",
                "Les Gouro sont organisés en lignages ; leurs masques (Zaouli, Zamblé, Gu) interviennent lors des funérailles et des fêtes. Le Zaouli, apparu au XXe siècle, est devenu l'un des symboles culturels du pays.",
                [S::wiki('Gouro_(peuple)', 'Gouro (peuple)'), S::ich('Le Zaouli, musique et danse populaires des communautés gouro', 'le-zaouli-musique-et-danse-populaires-des-communautes-gouro-de-cote-d-ivoire-01255'), ...$general]),
            $this->people('Yaouré', 'yaoure', 'Centre', 'Mandé du Sud', 'Yaouré', 'Bouaflé',
                "Peuple mandé du centre, entre Bouaflé et Yamoussoukro, les Yaouré sont connus pour leurs masques sculptés d'une grande finesse, proches de ceux de leurs voisins baoulé et gouro.",
                "Peuple peu nombreux à la charnière des mondes mandé et akan, les Yaouré ont développé un art du masque très apprécié des collectionneurs.",
                $general),
            $this->people('Gban (Gagou)', 'gban-gagou', 'Centre', 'Mandé du Sud', 'Gban', 'Oumé',
                "Peuple mandé du centre-ouest, autour d'Oumé et de Gagnoa, les Gban (ou Gagou) comptent parmi les plus anciens occupants de la région.",
                "Les traditions orales présentent les Gban comme l'un des premiers peuples installés dans la forêt du centre-ouest, avant l'arrivée de leurs voisins.",
                $general),
            $this->people('Toura', 'toura', 'Ouest', 'Mandé du Sud', 'Toura', 'Biankouma',
                "Peuple mandé de la région montagneuse de Biankouma, voisin des Dan, les Toura partagent avec eux une riche tradition de masques.",
                "Installés dans les reliefs du nord de Man, les Toura ont conservé une organisation villageoise et des rites initiatiques proches de ceux des Dan.",
                $general),

            // ── MANDÉ DU NORD (Nord-Ouest et Centre-Nord) ────────────────────
            $this->people('Koyaka', 'koyaka', 'Nord', 'Mandé du Nord', 'Koyaka', 'Mankono',
                "Peuple mandé du centre-nord, établi autour de Mankono (région du Béré), les Koyaka sont proches des Malinké par la langue et la culture.",
                "Agriculteurs de savane, les Koyaka ont été en contact avec les grands centres commerciaux et religieux mandé du nord.",
                [S::wiki('Koyaka_(peuple)', 'Koyaka (peuple)'), ...$general]),
            $this->people('Mahou', 'mahou', 'Nord', 'Mandé du Nord', 'Mahou', 'Touba',
                "Peuple mandé du nord-ouest, établi autour de Touba (région du Bafing), les Mahou vivent au contact des montagnes de l'ouest et des savanes du nord.",
                "Les Mahou sont majoritairement musulmans ; Touba est un centre religieux et commercial ancien de la région.",
                $general),

            // ── GOUR / VOLTAÏQUE (Nord et Nord-Est) ──────────────────────────
            $this->people('Koulango', 'koulango', 'Nord', 'Gur (Voltaïque)', 'Koulango', 'Bouna',
                "Peuple voltaïque du nord-est (Bouna, Nassian, Bondoukou), les Koulango sont, selon la tradition, les premiers occupants de la région. Le royaume de Bouna en est l'une des grandes institutions.",
                "Les Koulango ont fondé ou intégré le royaume de Bouna et cohabitent avec les Abron, les Lobi et les Dioula dans le Zanzan.",
                [S::wiki('Koulangos'), ...$general]),
            $this->people('Tagbana', 'tagbana', 'Nord', 'Gur (Voltaïque, Sénoufo)', 'Tagbana', 'Katiola',
                "Peuple du groupe sénoufo établi autour de Katiola (région du Hambol), les Tagbana sont les gardiens du Gbofe d'Afounkaha, musique des trompes traversières inscrite au patrimoine culturel immatériel de l'UNESCO.",
                "Rattachés au grand ensemble sénoufo, les Tagbana occupent la frange sud des savanes, au contact du pays baoulé.",
                [S::ich('Le Gbofe d\'Afounkaha', 'le-gbofe-d-afounkaha-la-musique-des-trompes-traversieres-de-la-communaute-tagbana-00005'), S::wiki('Sénoufos'), ...$general]),
            $this->people('Djimini', 'djimini', 'Nord', 'Gur (Voltaïque, Sénoufo)', 'Djimini', 'Dabakala',
                "Peuple du groupe sénoufo de la région de Dabakala, les Djimini sont des agriculteurs de savane aux traditions de masques et d'initiation.",
                "Les Djimini ont subi au XIXe siècle les campagnes de Samory Touré, qui ont profondément marqué la région de Dabakala.",
                [S::wiki('Sénoufos'), ...$general]),
            $this->people('Nafana', 'nafana', 'Est', 'Gur (Voltaïque, Sénoufo)', 'Nafaanra', 'Bondoukou',
                "Peuple du groupe sénoufo établi au nord de Bondoukou, à la frontière du Ghana, les Nafana sont voisins des Abron et des Koulango.",
                "Isolés de l'ensemble sénoufo principal, les Nafana ont conservé leur langue tout en partageant de nombreux usages avec les peuples du Zanzan.",
                $general),
            $this->people('Lorhon', 'lorhon', 'Nord', 'Gur (Voltaïque)', 'Lorhon', 'Téhini',
                "Peuple voltaïque du Bounkani, près de Téhini et de Doropo, les Lorhon sont proches des Koulango et des Lobi.",
                "Peuple peu nombreux de l'extrême nord-est, les Lorhon partagent avec leurs voisins les traditions agricoles et rituelles des savanes voltaïques.",
                $general),
            $this->people('Birifor', 'birifor', 'Nord', 'Gur (Voltaïque)', 'Birifor', 'Bouna',
                "Peuple voltaïque de la région de Bouna, apparenté aux Lobi et aux Dagara, les Birifor vivent également au Burkina Faso et au Ghana.",
                "Agriculteurs de savane, les Birifor habitent comme les Lobi des maisons en terre dispersées dans les champs, et partagent avec eux l'initiation du joro.",
                $general),
        ];

        $this->report('Peuples', $this->seedMissing(CulturalPeople::class, $peoples));

        // Sources des peuples déjà présents (ajoutées seulement s'ils n'en ont pas).
        $existing = [
            'baoule' => S::wiki('Baoulés'), 'agni-anyi' => S::wiki('Agnis'), 'abron-brong' => S::wiki('Abron_(peuple)', 'Abron (peuple)'),
            'attie' => S::wiki('Attié_(peuple)', 'Attié (peuple)'), 'malinke' => S::wiki('Malinkés_(peuple)', 'Malinkés (peuple)'),
            'dan-yacouba' => S::wiki('Dan_(peuple)', 'Dan (peuple)'), 'senoufo' => S::wiki('Sénoufos'), 'lobi' => S::wiki('Lobi_(peuple)', 'Lobi (peuple)'),
            'bete' => S::wiki('Bétés'), 'guere-we' => S::wiki('Wés'),
        ];
        $rows = [];
        foreach ($existing as $slug => $source) {
            $rows[] = ['slug' => $slug, 'sources' => [$source, ...$general]];
        }
        $this->report('Sources des peuples existants', $this->seedMissing(CulturalPeople::class, array_filter($rows, fn ($r) => CulturalPeople::where('slug', $r['slug'])->exists())));
    }

    /** @param list<array{label:string,url:?string}> $sources */
    private function people(string $name, string $slug, string $zone, string $famille, string $langue, string $capitale, string $description, string $histoire, array $sources): array
    {
        static $order = 100;

        return [
            'name' => $name,
            'slug' => $slug,
            'zone_geographique' => $zone,
            'famille_linguistique' => $famille,
            'langue_principale' => $langue,
            'population_estimee' => null,
            'capitale_culturelle' => $capitale,
            'description' => $description,
            'histoire' => $histoire,
            'is_featured' => false,
            'is_active' => true,
            'sort_order' => $order++,
            'sources' => $sources,
        ];
    }
}
