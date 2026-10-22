<?php

namespace Database\Seeders;

use App\Models\TouristCategory;
use App\Models\TouristCity;
use App\Models\TouristSite;
use Database\Seeders\Concerns\SeedsMissingReferenceData;
use Database\Seeders\Data\IvorianSources as S;
use Illuminate\Database\Seeder;

/**
 * Sites touristiques majeurs de Côte d'Ivoire absents du référentiel : biens du patrimoine
 * mondial (UNESCO), parcs nationaux, sites sacrés, musées et lieux de mémoire.
 *
 * Volontairement sans tarif, horaires, téléphone ni photo : ces informations changent et
 * doivent être vérifiées localement puis saisies dans l'administration (Tourisme › Sites).
 * À lancer après TouristRegionSeeder (villes de rattachement). N'écrase aucun site existant.
 */
class TouristSiteCompletionSeeder extends Seeder
{
    use SeedsMissingReferenceData;

    private const MOSQUEES = 'Mosquées de style soudanais du Nord ivoirien';

    public function run(): void
    {
        $cities = TouristCity::pluck('id', 'slug');
        $cats = TouristCategory::pluck('id', 'slug');
        $mosquees = [S::whc(1648, self::MOSQUEES), S::UNESCO_PATRIMOINE_MONDIAL];

        $sites = [
            // ── Patrimoine mondial naturel ──────────────────────────────────
            ['guiglo', 'parcs-reserves-naturelles', 'Parc national de Taï', 'parc-national-tai',
                "L'un des derniers grands massifs de forêt tropicale primaire d'Afrique de l'Ouest, inscrit au patrimoine mondial.",
                "Le Parc national de Taï protège l'un des derniers vestiges importants de la forêt guinéenne primaire. Il abrite une biodiversité exceptionnelle : chimpanzés (dont les célèbres populations étudiées pour leur usage d'outils), hippopotames pygmées, céphalophes et de nombreuses espèces endémiques.\n\nInscrit au patrimoine mondial de l'UNESCO en 1982, il s'étend dans le sud-ouest du pays, entre les régions du Cavally, de la Nawa et de San-Pédro. Les visites se font avec des guides agréés, au départ des villages riverains.",
                [S::whc(195, 'Parc national de Taï'), S::UNESCO_PATRIMOINE_MONDIAL], true],
            ['bouna', 'parcs-reserves-naturelles', 'Parc national de la Comoé', 'parc-national-comoe',
                "Le plus grand parc national du pays, mosaïque de savanes et de forêts-galeries le long du fleuve Comoé.",
                "Situé dans le nord-est, le Parc national de la Comoé est l'une des plus vastes aires protégées d'Afrique de l'Ouest. Le fleuve Comoé y traverse une mosaïque de savanes, de forêts claires et de forêts-galeries, habitat d'éléphants, d'hippopotames, de buffles, d'antilopes et de nombreux oiseaux.\n\nInscrit au patrimoine mondial en 1983, il a figuré sur la liste du patrimoine en péril de 2003 à 2017, avant d'en être retiré grâce aux efforts de conservation. Bouna et Kong en sont les principales portes d'entrée.",
                [S::whc(227, 'Parc national de la Comoé'), S::UNESCO_PATRIMOINE_MONDIAL], true],
            ['danane', 'cascades-montagnes', 'Réserve naturelle intégrale du mont Nimba', 'reserve-mont-nimba',
                "Massif montagneux à la frontière de la Guinée et du Libéria, réserve intégrale au patrimoine mondial.",
                "Le mont Nimba, partagé entre la Côte d'Ivoire et la Guinée, culmine à la frontière des trois pays. Ses pentes couvertes de forêts et ses prairies d'altitude abritent une faune et une flore très particulières, dont des espèces endémiques comme le crapaud vivipare du Nimba.\n\nInscrite au patrimoine mondial en 1981-1982, la réserve est une réserve naturelle intégrale : l'accès est strictement réglementé et soumis à autorisation. Elle figure sur la liste du patrimoine mondial en péril.",
                [S::whc(155, 'Réserve naturelle intégrale du mont Nimba'), S::UNESCO_PATRIMOINE_MONDIAL], false],

            // ── Mosquées soudanaises (patrimoine mondial, 2021) ─────────────
            ['kong', 'architecture-sacree', 'Grande mosquée de Kong', 'grande-mosquee-kong',
                "Mosquée en terre de style soudanais de l'ancienne capitale de l'empire de Kong, inscrite au patrimoine mondial.",
                "Kong, ancienne cité marchande et religieuse dioula, abrite deux des huit mosquées du bien « Mosquées de style soudanais du Nord ivoirien », inscrit au patrimoine mondial en 2021 : la grande et la petite mosquée.\n\nBâties en terre crue, elles se reconnaissent à leurs contreforts verticaux, aux pièces de bois en saillie qui servent d'échafaudage lors du recrépissage, et à leurs minarets en forme de pyramide tronquée. Ce style, né à Djenné à l'époque de l'empire du Mali, s'est diffusé vers le sud à partir du XVe siècle.",
                $mosquees, true],
            ['ferkessedougou', 'architecture-sacree', 'Mosquée de Kaouara', 'mosquee-kaouara',
                "Mosquée de style soudanais du Tchologo, l'une des huit mosquées inscrites au patrimoine mondial.",
                "Située dans la région du Tchologo, la mosquée de Kaouara fait partie du bien en série « Mosquées de style soudanais du Nord ivoirien », inscrit au patrimoine mondial en 2021. Comme les autres édifices du bien, elle est construite en terre et entretenue par la communauté lors de recrépissages réguliers.",
                $mosquees, false],
            ['boundiali', 'architecture-sacree', 'Mosquée de Kouto', 'mosquee-kouto',
                "Mosquée de style soudanais de la Bagoué, dite « Fofanala Missiri », inscrite au patrimoine mondial.",
                "La mosquée de Kouto, dans la région de la Bagoué, appartient au bien en série « Mosquées de style soudanais du Nord ivoirien » inscrit au patrimoine mondial en 2021. Son architecture en terre crue, ses contreforts et ses bois en saillie illustrent la tradition soudano-sahélienne.",
                $mosquees, false],
            ['boundiali', 'architecture-sacree', 'Mosquée de Tengréla', 'mosquee-tengrela',
                "Mosquée de style soudanais de la Bagoué, près de la frontière malienne, inscrite au patrimoine mondial.",
                "La mosquée de Tengréla fait partie des huit mosquées du bien « Mosquées de style soudanais du Nord ivoirien » (patrimoine mondial, 2021). Tengréla, ville carrefour proche du Mali, témoigne des échanges commerciaux et religieux qui ont porté ce style architectural vers le sud.",
                $mosquees, false],
            ['odienne', 'architecture-sacree', 'Mosquée de Samatiguila', 'mosquee-samatiguila',
                "Mosquée de style soudanais du Kabadougou, l'une des plus anciennes du nord-ouest, au patrimoine mondial.",
                "Dans la région du Kabadougou, au nord d'Odienné, la mosquée de Samatiguila appartient au bien « Mosquées de style soudanais du Nord ivoirien », inscrit au patrimoine mondial en 2021. Elle est un haut lieu de l'islam malinké de la région.",
                $mosquees, false],
            ['korhogo', 'architecture-sacree', 'Mosquée de Nambira (M\'Bengué)', 'mosquee-nambira',
                "Mosquée de style soudanais du Poro, également appelée mosquée de M'Bengué, au patrimoine mondial.",
                "La mosquée de Nambira, aussi appelée M'Bengué ou Namboura, se trouve dans la région du Poro. Elle compte parmi les huit mosquées en terre du bien « Mosquées de style soudanais du Nord ivoirien », inscrit au patrimoine mondial en 2021.",
                $mosquees, false],
            ['bondoukou', 'architecture-sacree', 'Mosquée de Sorobango', 'mosquee-sorobango',
                "Mosquée de style soudanais du Gontougo, près de Bondoukou, inscrite au patrimoine mondial.",
                "Située dans la région du Gontougo, à proximité de Bondoukou, la mosquée de Sorobango fait partie du bien en série « Mosquées de style soudanais du Nord ivoirien » inscrit au patrimoine mondial en 2021.",
                $mosquees, false],

            // ── Parcs nationaux et réserves ─────────────────────────────────
            ['assinie', 'parcs-reserves-naturelles', 'Parc national des Îles Ehotilé', 'parc-national-iles-ehotile',
                "Archipel lagunaire sacré de la lagune Aby, à la fois réserve naturelle et site archéologique.",
                "Le Parc national des Îles Ehotilé regroupe six îles de la lagune Aby, près d'Adiaké. Couvertes de forêt et de mangrove, elles abritent une faune lagunaire riche et des amas coquilliers témoignant d'une occupation humaine ancienne. Les îles sont sacrées pour le peuple Éotilé (Ehotilé), dont elles constituent le berceau.\n\nLes visites se font en pirogue, avec des guides, au départ d'Adiaké ou d'Assinie.",
                [S::wiki('Parc_national_des_îles_Ehotilé', 'Parc national des îles Ehotilé')], false],
            ['grand-lahou', 'parcs-reserves-naturelles', 'Parc national d\'Azagny', 'parc-national-azagny',
                "Parc côtier de mangroves, marécages et forêts à l'embouchure du Bandama, refuge d'éléphants et d'oiseaux.",
                "Le Parc national d'Azagny s'étend entre l'océan, le canal d'Azagny et l'embouchure du fleuve Bandama, près de Grand-Lahou. Marécages, mangroves, savanes littorales et îlots forestiers y abritent des éléphants de forêt, des buffles, des primates et une avifaune abondante, ce qui en fait un site reconnu pour l'observation des oiseaux.",
                [S::wiki('Parc_national_d\'Azagny', 'Parc national d\'Azagny')], false],
            ['bouafle', 'parcs-reserves-naturelles', 'Parc national de la Marahoué', 'parc-national-marahoue',
                "Aire protégée du centre-ouest, à la transition entre forêt et savane, près de Bouaflé.",
                "Situé à l'ouest de Bouaflé, le Parc national de la Marahoué occupe une zone de transition entre la forêt dense et la savane. Il a beaucoup souffert de l'exploitation agricole illégale ; des programmes de restauration sont en cours. Les visites sont à organiser avec les services de l'Office ivoirien des parcs et réserves (OIPR).",
                [S::wiki('Parc_national_de_la_Marahoué', 'Parc national de la Marahoué')], false],
            ['duekoue', 'parcs-reserves-naturelles', 'Parc national du Mont Péko', 'parc-national-mont-peko',
                "Massif forestier de l'ouest dominé par le mont Péko, en cours de restauration.",
                "Le Parc national du Mont Péko, entre Duékoué et Bangolo, protège un massif montagneux couvert de forêt. Fortement dégradé par les plantations illégales, il a fait l'objet d'opérations de libération et de reboisement. Renseignez-vous auprès de l'OIPR avant toute visite.",
                [S::wiki('Parc_national_du_Mont_Péko', 'Parc national du Mont Péko')], false],
            ['man', 'cascades-montagnes', 'Parc national du Mont Sangbé', 'parc-national-mont-sangbe',
                "Parc de montagnes et de savanes d'altitude au nord de Man, aux nombreux sommets granitiques.",
                "Le Parc national du Mont Sangbé, entre Man et Touba, réunit une série de sommets granitiques, de savanes d'altitude et de forêts-galeries. C'est l'un des paysages les plus montagneux du pays, propice à la randonnée accompagnée.",
                [S::wiki('Parc_national_du_Mont_Sangbé', 'Parc national du Mont Sangbé')], false],
            ['toumodi', 'parcs-reserves-naturelles', 'Réserve de faune d\'Abokouamékro', 'reserve-abokouamekro',
                "Réserve de savane entre Yamoussoukro et Toumodi, où l'on peut observer de grands mammifères.",
                "Créée au début des années 1990 entre Yamoussoukro et Toumodi, la réserve de faune d'Abokouamékro est une savane clôturée où ont été introduites plusieurs espèces de grands mammifères. Elle est l'une des rares réserves du centre du pays accessibles pour un safari de découverte, sur réservation.",
                [S::wiki('Réserve_d\'Abokouamékro', 'Réserve d\'Abokouamékro')], false],

            // ── Montagnes et sites naturels de l'ouest ──────────────────────
            ['man', 'cascades-montagnes', 'Mont Tonkoui', 'mont-tonkoui',
                "Point culminant des environs de Man, offrant par temps clair une vue jusqu'à la Guinée et au Libéria.",
                "Le mont Tonkoui domine la région de Man. Une route sinueuse mène vers son sommet, d'où la vue embrasse les monts environnants et, par temps clair, les pays voisins. Les forêts d'altitude et les plantations de café et de quinquina rappellent le climat frais de cette région de montagnes.",
                [S::wiki('Mont_Tonkoui', 'Mont Tonkoui')], false],
            ['man', 'cascades-montagnes', 'La Dent de Man', 'dent-de-man',
                "Pic rocheux emblématique en forme de dent, symbole de la ville de Man.",
                "La Dent de Man est un pic granitique à la silhouette caractéristique qui domine la ville. Son ascension, accompagnée par un guide local, se fait à travers les plantations et la forêt, et récompense les marcheurs par un panorama sur la région des Dix-Huit Montagnes.",
                [S::wiki('Dent_de_Man', 'Dent de Man')], false],
            ['danane', 'villages-ethnies', 'Ponts de lianes de la région de Danané', 'ponts-de-lianes-danane',
                "Passerelles végétales tressées au-dessus des rivières, ouvrage traditionnel et sacré du pays dan.",
                "Dans les villages proches de Danané, les ponts de lianes enjambent les cours d'eau. Selon la tradition dan, ils sont tressés en une nuit par des initiés, à l'abri des regards. Ces ouvrages, entretenus par les communautés, sont entourés de règles et d'interdits : la visite se fait avec un guide du village et le respect des coutumes locales.",
                [S::wiki('Dan_(peuple)', 'Dan (peuple)')], false],

            // ── Patrimoine, mémoire et musées ───────────────────────────────
            ['grand-bassam', 'musees-galeries', 'Musée national du Costume', 'musee-national-costume-grand-bassam',
                "Ancien palais des gouverneurs de Grand-Bassam, devenu musée des costumes et parures des peuples ivoiriens.",
                "Installé dans l'ancien palais des gouverneurs, au cœur du quartier France de la ville historique de Grand-Bassam (patrimoine mondial), le Musée national du Costume présente des tenues, parures et objets des différents peuples du pays, ainsi que des maquettes d'habitats traditionnels.",
                [S::whc(1322, 'Ville historique de Grand-Bassam'), S::wiki('Grand-Bassam')], false],
            ['bingerville', 'sites-historiques', 'Bingerville : ancienne capitale et jardin botanique', 'bingerville-ancienne-capitale',
                "Capitale de la colonie de 1900 à 1934 : palais du gouverneur, jardin botanique et musée Charles Combes.",
                "Bingerville fut la capitale de la colonie de 1900 à 1934, avant Abidjan. La ville conserve l'ancien palais du gouverneur, un vaste jardin botanique planté au début du XXe siècle et le musée consacré au sculpteur et peintre Charles Combes. Un site agréable pour une excursion à l'est d'Abidjan, au bord de la lagune Ébrié.",
                [S::wiki('Bingerville')], false],
            ['abidjan', 'architecture-sacree', 'Cathédrale Saint-Paul d\'Abidjan', 'cathedrale-saint-paul-abidjan',
                "Cathédrale au style audacieux du Plateau, dessinée par l'architecte Aldo Spirito et consacrée en 1985.",
                "Œuvre de l'architecte italien Aldo Spirito, la cathédrale Saint-Paul domine la lagune Ébrié depuis le Plateau. Sa structure, en forme de personnage (saint Paul) retenant le toit par des haubans, et ses vitraux colorés en font l'un des monuments les plus remarquables d'Abidjan. Elle a été consacrée par le pape Jean-Paul II en 1985.",
                [S::wiki('Cathédrale_Saint-Paul_d\'Abidjan', 'Cathédrale Saint-Paul d\'Abidjan')], true],
            ['yamoussoukro', 'monuments-patrimoine', 'Fondation Félix Houphouët-Boigny pour la recherche de la paix', 'fondation-houphouet-boigny-yamoussoukro',
                "Vaste édifice de marbre de Yamoussoukro, siège de la fondation pour la paix créée par le premier président.",
                "Bâtiment monumental de Yamoussoukro, la Fondation Félix Houphouët-Boigny pour la recherche de la paix accueille conférences et événements internationaux. Ses salles, jardins et l'architecture d'inspiration moderne témoignent de l'ambition du premier président ivoirien pour sa ville natale, devenue capitale politique.",
                [S::wiki('Yamoussoukro')], false],
            ['yamoussoukro', 'lacs-cours-eau', 'Lac de Kossou', 'lac-de-kossou',
                "Grand lac de retenue sur le Bandama, né de la construction du barrage de Kossou.",
                "Le barrage de Kossou, mis en service au début des années 1970 sur le fleuve Bandama, a créé l'un des plus grands lacs artificiels d'Afrique de l'Ouest. Le lac, au nord-ouest de Yamoussoukro, est un lieu de pêche et de paysages, bordé de villages baoulé.",
                [S::wiki('Lac_de_Kossou', 'Lac de Kossou')], false],
            ['bondoukou', 'sites-historiques', 'Bondoukou, la ville aux mille mosquées', 'bondoukou-ville-mille-mosquees',
                "Ancienne cité marchande du Zanzan, carrefour des routes de l'or et de la cola, aux nombreuses mosquées.",
                "Bondoukou, l'une des plus anciennes villes du pays, fut un carrefour du commerce de l'or et de la noix de cola entre la forêt et le Sahel. On la surnomme « la ville aux mille mosquées ». Siège du royaume abron du Gyaman, elle mêle traditions akan, koulango et dioula ; la mosquée de Sorobango, inscrite au patrimoine mondial, se trouve à proximité.",
                [S::wiki('Bondoukou'), S::wiki('Abron_(peuple)', 'Abron (peuple)')], false],

            // ── Villages d'artisans et patrimoine vivant ────────────────────
            ['korhogo', 'artisanat-marches', 'Fakaha, village des toiles de Korhogo', 'fakaha-toiles-korhogo',
                "Village sénoufo des peintres sur toile, berceau des célèbres « toiles de Korhogo ».",
                "À quelques kilomètres de Korhogo, le village de Fakaha est le berceau des toiles peintes sénoufo, dites « toiles de Korhogo ». Sur des cotonnades tissées à la main, les artisans dessinent des motifs d'animaux, de masques et de scènes rituelles avec des teintures végétales et de la boue fermentée. On peut y voir les artistes au travail et acheter directement aux ateliers.",
                [S::wiki('Sénoufos')], false],
            ['korhogo', 'artisanat-marches', 'Waraniéné, village des tisserands', 'waraniene-tisserands',
                "Village de tisserands sénoufo près de Korhogo, où l'on tisse le coton en bandes sur métiers traditionnels.",
                "Le village de Waraniéné, près de Korhogo, est connu pour ses tisserands qui produisent des bandes de coton sur des métiers à pédales, assemblées ensuite en pagnes et en tenues. Les savoir-faire du tissage du pagne en Côte d'Ivoire sont inscrits au patrimoine culturel immatériel de l'UNESCO depuis 2023.",
                [S::ich('Les savoir-faire traditionnels liés au tissage du pagne en Côte d\'Ivoire', 'les-savoir-faire-traditionnels-lies-au-tissage-du-pagne-en-cote-d-ivoire-01949'), S::wiki('Sénoufos')], false],
            ['katiola', 'villages-ethnies', 'Afounkaha, village du Gbofe', 'afounkaha-village-gbofe',
                "Village tagbana du Hambol, gardien de la musique des trompes traversières inscrite par l'UNESCO.",
                "Le village d'Afounkaha, près de Katiola, est le berceau du Gbofe, musique des trompes traversières de la communauté tagbana, jouée lors des funérailles, des fêtes et des cérémonies. Inscrit sur la Liste représentative du patrimoine culturel immatériel de l'UNESCO en 2008, ce patrimoine se découvre en s'adressant à la chefferie du village.",
                [S::ich('Le Gbofe d\'Afounkaha', 'le-gbofe-d-afounkaha-la-musique-des-trompes-traversieres-de-la-communaute-tagbana-00005')], false],
            ['san-pedro', 'plages-lagunes', 'Plage de Monogaga', 'plage-monogaga',
                "Longue plage sauvage du sud-ouest, entre San-Pédro et Sassandra, bordée de cocotiers et de forêt.",
                "Entre San-Pédro et Sassandra, la plage de Monogaga déroule des kilomètres de sable doré entre l'océan et la forêt. Peu aménagée, elle est appréciée pour son calme ; la baignade y demande de la prudence en raison des courants de l'océan Atlantique.",
                [S::wiki('San-Pédro')], false],
            ['sassandra', 'plages-lagunes', 'Embouchure du fleuve Sassandra', 'embouchure-sassandra',
                "Rencontre du fleuve Sassandra et de l'océan, entourée de plages, de mangroves et de villages de pêcheurs.",
                "À Sassandra, ancien port colonial, le fleuve rejoint l'océan dans un décor de collines, de mangroves et de plages. Les balades en pirogue sur l'estuaire et la visite des villages de pêcheurs, ainsi que les vestiges de l'architecture coloniale, font le charme de la ville.",
                [S::wiki('Sassandra')], false],
            ['abidjan', 'parcs-loisirs-zoos', 'Parc zoologique d\'Abidjan', 'parc-zoologique-abidjan',
                "Le zoo de la capitale économique, à Cocody, consacré à la faune ivoirienne et africaine.",
                "Situé dans la commune de Cocody, le Parc zoologique d'Abidjan présente des espèces de la faune ivoirienne et africaine. Il participe à la sensibilisation du public, notamment des scolaires, à la protection de la biodiversité.",
                [S::wiki('Zoo_d\'Abidjan', 'Zoo d\'Abidjan')], false],
        ];

        $rows = [];
        $order = 100;
        foreach ($sites as [$city, $cat, $name, $slug, $short, $description, $sources, $featured]) {
            if (! isset($cities[$city], $cats[$cat])) {
                $this->command?->warn("Site « {$name} » ignoré : ville « {$city} » ou catégorie « {$cat} » introuvable.");

                continue;
            }
            $rows[] = [
                'city_id' => $cities[$city],
                'category_id' => $cats[$cat],
                'name' => $name,
                'slug' => $slug,
                'short_description' => $short,
                'description' => $description,
                'is_featured' => $featured,
                'is_active' => true,
                'sort_order' => $order++,
                'sources' => $sources,
            ];
        }

        $this->report('Sites touristiques', $this->seedMissing(TouristSite::class, $rows));
    }
}
