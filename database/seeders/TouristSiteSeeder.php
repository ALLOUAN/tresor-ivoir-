<?php

namespace Database\Seeders;

use App\Models\TouristCategory;
use App\Models\TouristCity;
use App\Models\TouristSite;
use App\Models\TouristSiteMedia;
use Illuminate\Database\Seeder;

class TouristSiteSeeder extends Seeder
{
    public function run(): void
    {
        // Charger les IDs par slug
        $cities = TouristCity::pluck('id', 'slug');
        $cats   = TouristCategory::pluck('id', 'slug');

        $sites = [

            // ── ABIDJAN ───────────────────────────────────────────────────────

            [
                'city'              => 'abidjan',
                'category'          => 'parcs-espaces-naturels',
                'name'              => 'Parc National du Banco',
                'slug'              => 'parc-national-banco',
                'short_description' => 'Forêt tropicale humide au cœur d\'Abidjan, sanctuaire de biodiversité unique en Afrique.',
                'description'       => "Le Parc National du Banco est une forêt tropicale humide classée parc national, située en plein cœur de la métropole abidjanaise. Couvrant plus de 3 000 hectares, il abrite une faune et une flore exceptionnelles : primates, oiseaux rares, papillons et une végétation luxuriante.\n\nCe poumon vert d'Abidjan offre des sentiers pédestres, des zones de pique-nique et une occasion unique d'observer la nature sauvage sans quitter la ville. La rivière Banco traverse le parc, créant des paysages pittoresques très appréciés des promeneurs et des photographes.",
                'thumbnail'         => 'https://upload.wikimedia.org/wikipedia/commons/thumb/8/8e/Banco_National_Park.jpg/800px-Banco_National_Park.jpg',
                'entrance_fee'      => '500 FCFA / adulte — Gratuit pour les moins de 12 ans',
                'phone'             => '+225 27 21 35 00 00',
                'latitude'          => 5.3897,
                'longitude'         => -4.0452,
                'departement'       => 'Abidjan',
                'sous_prefecture'   => 'Yopougon',
                'localite'          => 'Quartier Banco',
                'superficie_ha'     => 3474.0,
                'distance_centre_km'=> 12.0,
                'point_repere'      => 'Accessible depuis le boulevard de Marseille, après le carrefour de Yopougon.',
                'acces_description' => "En voiture : prendre le boulevard de Marseille en direction de Yopougon.\nEn commun : bus SOTRA ligne 49 depuis le Plateau, arrêt Banco.",
                'schedules'         => [
                    ['day' => 'Lundi',    'opens' => '07:00', 'closes' => '18:00', 'closed' => false],
                    ['day' => 'Mardi',    'opens' => '07:00', 'closes' => '18:00', 'closed' => false],
                    ['day' => 'Mercredi', 'opens' => '07:00', 'closes' => '18:00', 'closed' => false],
                    ['day' => 'Jeudi',    'opens' => '07:00', 'closes' => '18:00', 'closed' => false],
                    ['day' => 'Vendredi', 'opens' => '07:00', 'closes' => '18:00', 'closed' => false],
                    ['day' => 'Samedi',   'opens' => '07:00', 'closes' => '19:00', 'closed' => false],
                    ['day' => 'Dimanche', 'opens' => '07:00', 'closes' => '19:00', 'closed' => false],
                ],
                'practical_info'    => [
                    ['icon' => 'fas fa-car',      'label' => 'Parking',      'value' => 'Parking gratuit à l\'entrée principale'],
                    ['icon' => 'fas fa-shoe-prints','label' => 'Tenue',       'value' => 'Chaussures fermées recommandées'],
                    ['icon' => 'fas fa-language',  'label' => 'Guides',       'value' => 'Guides disponibles sur place (français)'],
                    ['icon' => 'fas fa-droplet',   'label' => 'Eau',          'value' => 'Apporter de l\'eau'],
                    ['icon' => 'fas fa-bug',       'label' => 'Protection',   'value' => 'Anti-moustiques conseillé'],
                ],
                'is_featured'  => 1,
                'is_active'    => 1,
                'sort_order'   => 1,
                'media'        => [],
            ],

            [
                'city'              => 'abidjan',
                'category'          => 'musees-galeries',
                'name'              => 'Musée des Civilisations de Côte d\'Ivoire',
                'slug'              => 'musee-civilisations-cote-ivoire',
                'short_description' => 'Premier musée national, il conserve plus de 10 000 objets d\'art et artefacts des peuples ivoiriens.',
                'description'       => "Fondé en 1942, le Musée des Civilisations de Côte d'Ivoire est le plus grand musée du pays. Il abrite une collection permanente de plus de 10 000 pièces : masques sacrés, statues fétiches, instruments de musique, bijoux, textiles et objets rituels représentant les 60 groupes ethniques du pays.\n\nLes collections sont organisées par région et par ethnie, offrant un voyage fascinant à travers les civilisations Akan, Mandé, Gur et Krou. Des expositions temporaires accueillent régulièrement des artistes contemporains africains.",
                'thumbnail'         => null,
                'entrance_fee'      => '2 000 FCFA / adulte — 1 000 FCFA / étudiant',
                'phone'             => '+225 27 20 21 56 17',
                'email'             => 'musee.abidjan@culture.ci',
                'website'           => null,
                'latitude'          => 5.3207,
                'longitude'         => -4.0128,
                'departement'       => 'Abidjan',
                'sous_prefecture'   => 'Plateau',
                'localite'          => 'Plateau, Avenue Joseph Anoma',
                'distance_centre_km'=> 0.5,
                'point_repere'      => 'Face à la cathédrale Saint-Paul du Plateau.',
                'acces_description' => "Situé au cœur du Plateau (centre des affaires).\nBus SOTRA : plusieurs lignes s'arrêtent au Plateau.\nTaxi : commun dans tout Abidjan.",
                'schedules'         => [
                    ['day' => 'Lundi',    'opens' => null,    'closes' => null,    'closed' => true],
                    ['day' => 'Mardi',    'opens' => '09:00', 'closes' => '17:00', 'closed' => false],
                    ['day' => 'Mercredi', 'opens' => '09:00', 'closes' => '17:00', 'closed' => false],
                    ['day' => 'Jeudi',    'opens' => '09:00', 'closes' => '17:00', 'closed' => false],
                    ['day' => 'Vendredi', 'opens' => '09:00', 'closes' => '17:00', 'closed' => false],
                    ['day' => 'Samedi',   'opens' => '09:00', 'closes' => '13:00', 'closed' => false],
                    ['day' => 'Dimanche', 'opens' => null,    'closes' => null,    'closed' => true],
                ],
                'practical_info'    => [
                    ['icon' => 'fas fa-camera',      'label' => 'Photos',       'value' => 'Autorisées sans flash'],
                    ['icon' => 'fas fa-ticket',      'label' => 'Tarif groupe', 'value' => 'Réductions pour groupes scolaires'],
                    ['icon' => 'fas fa-language',    'label' => 'Visites',      'value' => 'Visites guidées en français et anglais'],
                    ['icon' => 'fas fa-wheelchair',  'label' => 'Accessibilité','value' => 'Accès handicapés disponible'],
                ],
                'is_featured'  => 1,
                'is_active'    => 1,
                'sort_order'   => 2,
                'media'        => [],
            ],

            [
                'city'              => 'abidjan',
                'category'          => 'gastronomie-marches',
                'name'              => 'Marché de Treichville',
                'slug'              => 'marche-treichville',
                'short_description' => 'Le plus grand marché populaire d\'Abidjan, véritable temple de la gastronomie et de l\'artisanat local.',
                'description'       => "Le marché de Treichville est le poumon commercial et gastronomique d'Abidjan. Ce marché populaire animé rassemble des centaines de vendeurs proposant épices, tissus wax, poissons fumés, attiéké frais, sculptures sur bois et bijoux traditionnels.\n\nC'est ici que bat le vrai cœur d'Abidjan : bruyant, coloré, parfumé et authentique. On y trouve des maquis proposant des plats ivoiriens typiques à prix modiques : foutou banane, sauce graine, poisson braisé et kedjenou.",
                'thumbnail'         => null,
                'entrance_fee'      => 'Gratuit',
                'latitude'          => 5.2997,
                'longitude'         => -3.9972,
                'departement'       => 'Abidjan',
                'sous_prefecture'   => 'Treichville',
                'localite'          => 'Commune de Treichville',
                'distance_centre_km'=> 3.0,
                'point_repere'      => 'Accessible depuis le pont Houphouët-Boigny, direction Treichville.',
                'acces_description' => "En taxi ou bus SOTRA depuis le Plateau.\nTraverser le pont Houphouët-Boigny, puis 5 min à pied.",
                'schedules'         => [
                    ['day' => 'Lundi au Samedi', 'opens' => '06:00', 'closes' => '20:00', 'closed' => false],
                    ['day' => 'Dimanche',        'opens' => '06:00', 'closes' => '14:00', 'closed' => false],
                ],
                'practical_info'    => [
                    ['icon' => 'fas fa-wallet',    'label' => 'Paiement',  'value' => 'Espèces uniquement'],
                    ['icon' => 'fas fa-utensils',  'label' => 'Maquis',    'value' => 'Nombreux restaurants locaux sur place'],
                    ['icon' => 'fas fa-shield-alt','label' => 'Sécurité',  'value' => 'Restez vigilant, affluence importante'],
                ],
                'is_featured'  => 0,
                'is_active'    => 1,
                'sort_order'   => 3,
                'media'        => [],
            ],

            // ── YAMOUSSOUKRO ──────────────────────────────────────────────────

            [
                'city'              => 'yamoussoukro',
                'category'          => 'monuments-patrimoine',
                'name'              => 'Basilique Notre-Dame de la Paix',
                'slug'              => 'basilique-notre-dame-paix',
                'short_description' => 'Plus grande église du monde selon le Livre Guinness, inspirée de la Basilique Saint-Pierre de Rome.',
                'description'       => "La Basilique Notre-Dame de la Paix de Yamoussoukro est l'un des édifices religieux les plus spectaculaires du monde. Construite entre 1985 et 1989 à l'initiative du président Félix Houphouët-Boigny, elle dépasse en superficie la Basilique Saint-Pierre de Rome.\n\nAvec son dôme culminant à 158 mètres, ses vitraux signés Gustave Zubena couvrant 7 400 m², ses 36 colonnes monumentales et sa capacité d'accueil de 18 000 personnes, la basilique est un chef-d'œuvre architectural. Le pape Jean-Paul II l'a consacrée en septembre 1990.\n\nL'esplanade peut accueillir plus de 300 000 personnes lors des grandes cérémonies.",
                'thumbnail'         => 'https://upload.wikimedia.org/wikipedia/commons/thumb/5/57/Yamoussoukro_basilica.jpg/800px-Yamoussoukro_basilica.jpg',
                'entrance_fee'      => 'Visite guidée : 3 000 FCFA / adulte',
                'phone'             => '+225 27 30 64 00 00',
                'website'           => null,
                'latitude'          => 6.8063,
                'longitude'         => -5.2765,
                'departement'       => 'Yamoussoukro',
                'localite'          => 'Centre de Yamoussoukro',
                'altitude_m'        => 305,
                'distance_centre_km'=> 2.0,
                'point_repere'      => 'Visible depuis toute la ville, boulevard Houphouët-Boigny.',
                'acces_description' => "En voiture depuis Abidjan : autoroute A1, environ 2h30.\nEn bus : plusieurs compagnies relient Abidjan à Yamoussoukro quotidiennement.",
                'map_embed_url'     => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3961.09!2d-5.2787!3d6.8063!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zNsKwNDgnMjIuNyJOIDXCsDE2JzM1LjQiVw!5e0!3m2!1sfr!2sci!4v1234567890',
                'schedules'         => [
                    ['day' => 'Lundi',    'opens' => '08:00', 'closes' => '18:00', 'closed' => false],
                    ['day' => 'Mardi',    'opens' => '08:00', 'closes' => '18:00', 'closed' => false],
                    ['day' => 'Mercredi', 'opens' => '08:00', 'closes' => '18:00', 'closed' => false],
                    ['day' => 'Jeudi',    'opens' => '08:00', 'closes' => '18:00', 'closed' => false],
                    ['day' => 'Vendredi', 'opens' => '08:00', 'closes' => '18:00', 'closed' => false],
                    ['day' => 'Samedi',   'opens' => '08:00', 'closes' => '18:00', 'closed' => false],
                    ['day' => 'Dimanche', 'opens' => '09:00', 'closes' => '17:00', 'closed' => false],
                ],
                'practical_info'    => [
                    ['icon' => 'fas fa-tshirt',    'label' => 'Code vestimentaire', 'value' => 'Tenue correcte exigée (épaules et genoux couverts)'],
                    ['icon' => 'fas fa-car',       'label' => 'Parking',           'value' => 'Grand parking gratuit sur l\'esplanade'],
                    ['icon' => 'fas fa-language',  'label' => 'Guides',            'value' => 'Visites guidées en français et anglais'],
                    ['icon' => 'fas fa-camera',    'label' => 'Photos',            'value' => 'Autorisées à l\'extérieur, interdites pendant les offices'],
                ],
                'is_featured'  => 1,
                'is_active'    => 1,
                'sort_order'   => 1,
                'media'        => [
                    ['type' => 'photo', 'url' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/5/57/Yamoussoukro_basilica.jpg/800px-Yamoussoukro_basilica.jpg', 'caption' => 'Vue extérieure de la basilique', 'alt_text' => 'Basilique Notre-Dame de la Paix'],
                ],
            ],

            [
                'city'              => 'yamoussoukro',
                'category'          => 'parcs-espaces-naturels',
                'name'              => 'Lacs aux Crocodiles Sacrés',
                'slug'              => 'lacs-crocodiles-sacres-yamoussoukro',
                'short_description' => 'Lacs entourant le palais présidentiel, abritant des centaines de crocodiles sacrés nourris chaque soir.',
                'description'       => "Les lacs entourant le palais présidentiel de Yamoussoukro abritent des centaines de crocodiles du Nil considérés comme sacrés. Selon la tradition Baoulé, ces reptiles sont les gardiens spirituels de la ville et de ses habitants.\n\nChaque soir au coucher du soleil, une cérémonie de nourrissage est organisée : les crocodiles se rassemblent en grand nombre sur les berges pour dévorer des poulets entiers. Ce spectacle unique attire chaque jour de nombreux visiteurs. La légende veut que les crocodiles ne s'attaquent jamais aux humains tant que les rites traditionnels sont respectés.",
                'thumbnail'         => null,
                'entrance_fee'      => '1 500 FCFA pour le nourrissage du soir',
                'latitude'          => 6.8178,
                'longitude'         => -5.2752,
                'departement'       => 'Yamoussoukro',
                'localite'          => 'Palais présidentiel, Yamoussoukro',
                'distance_centre_km'=> 1.5,
                'point_repere'      => 'Autour du palais présidentiel, rue des Jardins.',
                'acces_description' => "Accessible à pied depuis la basilique en 15 minutes.\nTaxi recommandé depuis le centre-ville.",
                'schedules'         => [
                    ['day' => 'Tous les jours', 'opens' => '17:30', 'closes' => '19:00', 'closed' => false],
                ],
                'practical_info'    => [
                    ['icon' => 'fas fa-clock',      'label' => 'Meilleur moment', 'value' => '17h30 pour le nourrissage du soir'],
                    ['icon' => 'fas fa-camera',     'label' => 'Photos',          'value' => 'Autorisées'],
                    ['icon' => 'fas fa-child',      'label' => 'Enfants',         'value' => 'Déconseillé aux très jeunes enfants'],
                ],
                'is_featured'  => 1,
                'is_active'    => 1,
                'sort_order'   => 2,
                'media'        => [],
            ],

            // ── GRAND-BASSAM ──────────────────────────────────────────────────

            [
                'city'              => 'grand-bassam',
                'category'          => 'monuments-patrimoine',
                'name'              => 'Quartier France — Patrimoine UNESCO',
                'slug'              => 'quartier-france-grand-bassam',
                'short_description' => 'Ancienne capitale coloniale classée au patrimoine mondial de l\'UNESCO depuis 2012, avec son architecture coloniale préservée.',
                'description'       => "Le Quartier France de Grand-Bassam est classé au Patrimoine mondial de l'UNESCO depuis 2012. Première capitale coloniale de la Côte d'Ivoire (1893-1900), il conserve un ensemble architectural colonial exceptionnel : maisons à varangues, entrepôts commerciaux, bâtiments administratifs et résidences de style tropical français.\n\nLes rues pavées du quartier abritent aujourd'hui des galeries d'art, des ateliers de batik, des restaurants et des hôtels de charme. Le Musée National du Costume et le Musée Brazza enrichissent la visite culturelle.\n\nLa plage de Grand-Bassam, à quelques pas, complète parfaitement cette escapade historique et balnéaire.",
                'thumbnail'         => null,
                'entrance_fee'      => 'Accès libre au quartier — Musées : 1 000 FCFA',
                'latitude'          => 5.2012,
                'longitude'         => -3.7375,
                'departement'       => 'Sud-Comoé',
                'sous_prefecture'   => 'Grand-Bassam',
                'localite'          => 'Quartier France',
                'distance_centre_km'=> 40.0,
                'point_repere'      => 'À 40 km à l\'est d\'Abidjan par la route de Bingerville.',
                'acces_description' => "En voiture depuis Abidjan : route de Bingerville, environ 45 minutes.\nEn bus : départs fréquents depuis le terminal de Bassam (Adjamé, Abidjan).",
                'schedules'         => [
                    ['day' => 'Tous les jours', 'opens' => '08:00', 'closes' => '18:00', 'closed' => false],
                ],
                'practical_info'    => [
                    ['icon' => 'fas fa-walking',    'label' => 'Visite',    'value' => 'À pied, prévoir 2 à 3 heures'],
                    ['icon' => 'fas fa-umbrella-beach', 'label' => 'Plage', 'value' => 'Plage accessible à 5 minutes à pied'],
                    ['icon' => 'fas fa-utensils',   'label' => 'Restauration', 'value' => 'Nombreux restaurants et bars sur place'],
                ],
                'is_featured'  => 1,
                'is_active'    => 1,
                'sort_order'   => 1,
                'media'        => [],
            ],

            [
                'city'              => 'grand-bassam',
                'category'          => 'plages-lagunes',
                'name'              => 'Plage de Grand-Bassam',
                'slug'              => 'plage-grand-bassam',
                'short_description' => 'L\'une des plages les plus populaires de Côte d\'Ivoire, idéale pour la baignade, la détente et les sports nautiques.',
                'description'       => "La plage de Grand-Bassam est l'une des plus fréquentées et des plus appréciées de Côte d'Ivoire. Ses kilomètres de sable doré bordant l'Atlantique attirent chaque week-end des milliers d'Abidjanais venant se ressourcer.\n\nLa plage offre un cadre exceptionnel avec le contraste entre l'architecture coloniale du Quartier France et l'océan. Les pêcheurs artisanaux animent les lieux dès l'aube avec leurs pirogues colorées. De nombreuses paillotes et restaurants proposent poissons grillés, fruits de mer et cocktails.",
                'thumbnail'         => null,
                'entrance_fee'      => 'Gratuit — Transats en location sur place',
                'latitude'          => 5.1950,
                'longitude'         => -3.7410,
                'departement'       => 'Sud-Comoé',
                'sous_prefecture'   => 'Grand-Bassam',
                'localite'          => 'Front de mer, Grand-Bassam',
                'distance_centre_km'=> 41.0,
                'point_repere'      => 'En face du Quartier France, bord de mer.',
                'acces_description' => "Depuis le Quartier France : 5 minutes à pied.\nParking voiture disponible à proximité.",
                'schedules'         => [
                    ['day' => 'Tous les jours', 'opens' => '06:00', 'closes' => '20:00', 'closed' => false],
                ],
                'practical_info'    => [
                    ['icon' => 'fas fa-umbrella-beach', 'label' => 'Transats',   'value' => '500 FCFA / transat à la journée'],
                    ['icon' => 'fas fa-fish',           'label' => 'Restauration','value' => 'Nombreuses paillotes de poisson braisé'],
                    ['icon' => 'fas fa-water',          'label' => 'Courants',   'value' => 'Baignade surveillée le week-end uniquement'],
                    ['icon' => 'fas fa-anchor',         'label' => 'Nautisme',   'value' => 'Location jet-ski et kayak disponible'],
                ],
                'is_featured'  => 1,
                'is_active'    => 1,
                'sort_order'   => 2,
                'media'        => [],
            ],

            // ── MAN ───────────────────────────────────────────────────────────

            [
                'city'              => 'man',
                'category'          => 'parcs-espaces-naturels',
                'name'              => 'Cascades de Man',
                'slug'              => 'cascades-man',
                'short_description' => 'Magnifiques cascades naturelles dans la forêt de l\'ouest ivoirien, à quelques kilomètres de la ville de Man.',
                'description'       => "Les cascades de Man sont parmi les plus belles chutes d'eau de Côte d'Ivoire. Situées dans un cadre forestier verdoyant à quelques kilomètres de la ville, elles se composent de plusieurs chutes successives créant des bassins naturels propices à la baignade.\n\nLe sentier de randonnée qui y mène offre des vues panoramiques sur les montagnes environnantes et permet d'observer la faune et la flore locales. Les populations Dan (Yacouba) de la région considèrent certaines cascades comme des sites sacrés.",
                'thumbnail'         => null,
                'entrance_fee'      => '500 FCFA',
                'latitude'          => 7.4325,
                'longitude'         => -7.5634,
                'departement'       => 'Tonkpi',
                'sous_prefecture'   => 'Man',
                'localite'          => 'Périphérie de Man',
                'altitude_m'        => 420,
                'distance_centre_km'=> 5.0,
                'point_repere'      => 'Route de Danané, suivre les panneaux "Cascades".',
                'acces_description' => "En voiture depuis Man : 15 minutes sur la route de Danané.\nEn moto-taxi depuis le centre de Man : environ 10 minutes.",
                'schedules'         => [
                    ['day' => 'Tous les jours', 'opens' => '08:00', 'closes' => '17:00', 'closed' => false],
                ],
                'practical_info'    => [
                    ['icon' => 'fas fa-shoe-prints','label' => 'Randonnée',  'value' => 'Prévoir chaussures de marche'],
                    ['icon' => 'fas fa-tshirt',    'label' => 'Baignade',   'value' => 'Maillot de bain recommandé'],
                    ['icon' => 'fas fa-droplet',   'label' => 'Eau',        'value' => 'Apporter de l\'eau potable'],
                ],
                'is_featured'  => 1,
                'is_active'    => 1,
                'sort_order'   => 1,
                'media'        => [],
            ],

            [
                'city'              => 'man',
                'category'          => 'artisanat-villages',
                'name'              => 'Village de Danané — Danseurs de Masques Guéré',
                'slug'              => 'village-masques-guere-danane',
                'short_description' => 'Village traditionnel Guéré (Wè) réputé pour ses cérémonies de masques sacrés, l\'une des plus spectaculaires d\'Afrique de l\'Ouest.',
                'description'       => "Danané, aux portes de la Guinée, est le berceau des masques Guéré (Wè), considérés parmi les plus impressionnants d'Afrique de l'Ouest. Ces masques zoomorphes en bois, ornés de clous, de plumes et de fourrure, sont portés lors de cérémonies rituelles liées aux forces de la nature.\n\nLes danses de masques Guéré sont inscrites au patrimoine culturel immatériel de Côte d'Ivoire. Des démonstrations sont organisées pour les visiteurs, accompagnées de musique traditionnelle (tam-tam, balafon). Les artisans du village fabriquent également des reproductions de masques vendus comme souvenirs d'art.",
                'thumbnail'         => null,
                'entrance_fee'      => '2 000 FCFA pour une démonstration de masques',
                'latitude'          => 7.2643,
                'longitude'         => -8.1561,
                'departement'       => 'Tonkpi',
                'sous_prefecture'   => 'Danané',
                'localite'          => 'Danané',
                'altitude_m'        => 380,
                'distance_centre_km'=> 52.0,
                'point_repere'      => 'À 52 km de Man sur la route de la frontière guinéenne.',
                'acces_description' => "En voiture depuis Man : 1 heure sur route asphaltée.\nEn bush-taxi : départs réguliers depuis la gare routière de Man.",
                'schedules'         => [
                    ['day' => 'Mardi au Dimanche', 'opens' => '09:00', 'closes' => '16:00', 'closed' => false],
                    ['day' => 'Lundi', 'opens' => null, 'closes' => null, 'closed' => true],
                ],
                'practical_info'    => [
                    ['icon' => 'fas fa-language',  'label' => 'Guide',         'value' => 'Guide local recommandé (réserver à l\'avance)'],
                    ['icon' => 'fas fa-camera',    'label' => 'Photos',        'value' => 'Demander l\'autorisation avant de photographier'],
                    ['icon' => 'fas fa-handshake', 'label' => 'Respect',       'value' => 'Respecter les coutumes locales'],
                ],
                'is_featured'  => 0,
                'is_active'    => 1,
                'sort_order'   => 2,
                'media'        => [],
            ],

            // ── ASSINIE ───────────────────────────────────────────────────────

            [
                'city'              => 'assinie',
                'category'          => 'plages-lagunes',
                'name'              => 'Plage d\'Assinie-Mafia',
                'slug'              => 'plage-assinie-mafia',
                'short_description' => 'Presqu\'île paradisiaque entre océan Atlantique et lagune Aby, l\'une des plus belles plages de Côte d\'Ivoire.',
                'description'       => "Assinie est considérée comme la station balnéaire la plus prisée de Côte d'Ivoire. La presqu'île d'Assinie-Mafia, coincée entre l'océan Atlantique et la vaste lagune Aby, offre un paysage de carte postale : des kilomètres de plage sauvage, des cocotiers penchés sur l'eau turquoise et des couchers de soleil spectaculaires.\n\nLa lagune Aby, l'une des plus grandes de Côte d'Ivoire, permet la pratique du ski nautique, du kayak et des promenades en pirogue vers les villages Anyi de la lagune. La faune aquatique y est exceptionnelle avec des lamantins observés occasionnellement.",
                'thumbnail'         => null,
                'entrance_fee'      => 'Gratuit',
                'latitude'          => 5.1333,
                'longitude'         => -3.4667,
                'departement'       => 'Sud-Comoé',
                'sous_prefecture'   => 'Assinie',
                'localite'          => 'Assinie-Mafia, bord de mer',
                'distance_centre_km'=> 90.0,
                'point_repere'      => 'À 90 km à l\'est d\'Abidjan, après Grand-Bassam et Bonoua.',
                'acces_description' => "En voiture depuis Abidjan : autoroute A100 puis route de Grand-Bassam, environ 1h30.\nEn bateau depuis Grand-Bassam : traversée en pirogue motor depuis la jetée.",
                'schedules'         => [
                    ['day' => 'Tous les jours', 'opens' => '06:00', 'closes' => '20:00', 'closed' => false],
                ],
                'practical_info'    => [
                    ['icon' => 'fas fa-anchor',        'label' => 'Activités',     'value' => 'Ski nautique, kayak, pirogue sur la lagune'],
                    ['icon' => 'fas fa-hotel',         'label' => 'Hébergement',   'value' => 'Nombreux hôtels et lodges sur l\'île'],
                    ['icon' => 'fas fa-fish',          'label' => 'Restauration',  'value' => 'Poisson et fruits de mer frais en abondance'],
                    ['icon' => 'fas fa-sun',           'label' => 'Meilleure saison','value' => 'Novembre à avril (saison sèche)'],
                ],
                'is_featured'  => 1,
                'is_active'    => 1,
                'sort_order'   => 1,
                'media'        => [],
            ],

            // ── KORHOGO ───────────────────────────────────────────────────────

            [
                'city'              => 'korhogo',
                'category'          => 'artisanat-villages',
                'name'              => 'Village de Koni — Tisserands Sénoufo',
                'slug'              => 'village-koni-tisserands-senoufo',
                'short_description' => 'Village artisanal Sénoufo réputé pour son textile traditionnel "korhogo" aux motifs géométriques noirs et blancs.',
                'description'       => "Le village de Koni, aux abords de Korhogo, est le centre de production du célèbre tissu de Korhogo. Ce textile de coton teint à la boue noire (bogolan) avec des motifs géométriques et animaliers est mondialement reconnu.\n\nLes tisserands, sculpteurs et peintres du village transmettent leur savoir-faire depuis des générations. On peut observer les étapes de fabrication : filage du coton, tissage sur métiers traditionnels, application des motifs à la boue ferrugineuse. Les pièces produites (tentures, vêtements, sacs) sont vendues sur place à des prix bien inférieurs à ceux d'Abidjan.",
                'thumbnail'         => null,
                'entrance_fee'      => 'Gratuit — Achats conseillés sur place',
                'latitude'          => 9.4200,
                'longitude'         => -5.6000,
                'departement'       => 'Poro',
                'sous_prefecture'   => 'Korhogo',
                'localite'          => 'Village de Koni, périphérie de Korhogo',
                'distance_centre_km'=> 6.0,
                'point_repere'      => 'À 6 km du centre de Korhogo sur la route de Boundiali.',
                'acces_description' => "En moto-taxi depuis Korhogo : environ 15 minutes.\nEn voiture : suivre la route de Boundiali, panneau \"Village artisanal de Koni\".",
                'schedules'         => [
                    ['day' => 'Lundi au Samedi', 'opens' => '08:00', 'closes' => '17:00', 'closed' => false],
                    ['day' => 'Dimanche',        'opens' => null,    'closes' => null,    'closed' => true],
                ],
                'practical_info'    => [
                    ['icon' => 'fas fa-shopping-bag', 'label' => 'Shopping',  'value' => 'Pagne Korhogo, statues, masques — prix négociables'],
                    ['icon' => 'fas fa-language',     'label' => 'Langue',    'value' => 'Sénoufo — guide francophone disponible'],
                    ['icon' => 'fas fa-camera',       'label' => 'Photos',    'value' => 'Autorisées avec accord des artisans'],
                ],
                'is_featured'  => 0,
                'is_active'    => 1,
                'sort_order'   => 1,
                'media'        => [],
            ],
        ];

        $created = 0;

        foreach ($sites as $data) {
            $cityId = $cities[$data['city']] ?? null;
            $catId  = $cats[$data['category']] ?? null;

            if (!$cityId || !$catId) {
                $this->command->warn("⚠ Ville ou catégorie introuvable pour : {$data['name']}");
                continue;
            }

            $mediaData = $data['media'] ?? [];
            unset($data['city'], $data['category'], $data['media']);

            $data['city_id']     = $cityId;
            $data['category_id'] = $catId;

            $site = TouristSite::updateOrCreate(['slug' => $data['slug']], $data);

            // Médias
            if ($site->wasRecentlyCreated && !empty($mediaData)) {
                foreach ($mediaData as $i => $m) {
                    TouristSiteMedia::updateOrCreate(
                        ['site_id' => $site->id, 'url' => $m['url']],
                        array_merge($m, ['site_id' => $site->id, 'sort_order' => $i])
                    );
                }
            }

            $created++;
        }

        $this->command->info("✓ {$created} sites touristiques insérés.");
    }
}
