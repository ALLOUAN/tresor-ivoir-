<?php

namespace Database\Seeders;

use App\Models\TouristCity;
use Illuminate\Database\Seeder;

class TouristCitySeeder extends Seeder
{
    public function run(): void
    {
        $cities = [

            [
                'name'                  => 'Abidjan',
                'slug'                  => 'abidjan',
                'district'              => 'District Autonome d\'Abidjan',
                'region_administrative' => 'Lagunes',
                'description'           => 'Capitale économique et plus grande ville de Côte d\'Ivoire, Abidjan est une métropole vibrante surnommée "la perle des lagunes". Elle concentre culture, affaires, plages urbaines et une gastronomie riche.',
                'thumbnail'             => 'https://upload.wikimedia.org/wikipedia/commons/thumb/8/8d/Abidjan_Plateau_2.jpg/640px-Abidjan_Plateau_2.jpg',
                'cover_image'           => 'https://upload.wikimedia.org/wikipedia/commons/thumb/8/8d/Abidjan_Plateau_2.jpg/1280px-Abidjan_Plateau_2.jpg',
                'latitude'              => 5.3599517,
                'longitude'             => -4.0082563,
                'is_featured'           => 1,
                'is_active'             => 1,
                'sort_order'            => 1,
            ],

            [
                'name'                  => 'Yamoussoukro',
                'slug'                  => 'yamoussoukro',
                'district'              => 'District Autonome de Yamoussoukro',
                'region_administrative' => 'Lacs',
                'description'           => 'Capitale politique de la Côte d\'Ivoire et ville natale du président Félix Houphouët-Boigny. Elle abrite la Basilique Notre-Dame de la Paix, plus grande église du monde, et ses célèbres lacs aux crocodiles sacrés.',
                'thumbnail'             => 'https://upload.wikimedia.org/wikipedia/commons/thumb/5/57/Yamoussoukro_basilica.jpg/640px-Yamoussoukro_basilica.jpg',
                'cover_image'           => 'https://upload.wikimedia.org/wikipedia/commons/thumb/5/57/Yamoussoukro_basilica.jpg/1280px-Yamoussoukro_basilica.jpg',
                'latitude'              => 6.8276238,
                'longitude'             => -5.2893433,
                'is_featured'           => 1,
                'is_active'             => 1,
                'sort_order'            => 2,
            ],

            [
                'name'                  => 'Grand-Bassam',
                'slug'                  => 'grand-bassam',
                'district'              => 'District des Lagunes',
                'region_administrative' => 'Sud-Comoé',
                'description'           => 'Première capitale coloniale de Côte d\'Ivoire et site classé au patrimoine mondial de l\'UNESCO. Le quartier France conserve une architecture coloniale unique et ses plages sont parmi les plus prisées du pays.',
                'thumbnail'             => 'https://upload.wikimedia.org/wikipedia/commons/thumb/0/07/Grand-Bassam%2C_C%C3%B4te_d%27Ivoire.jpg/640px-Grand-Bassam%2C_C%C3%B4te_d%27Ivoire.jpg',
                'cover_image'           => 'https://upload.wikimedia.org/wikipedia/commons/thumb/0/07/Grand-Bassam%2C_C%C3%B4te_d%27Ivoire.jpg/1280px-Grand-Bassam%2C_C%C3%B4te_d%27Ivoire.jpg',
                'latitude'              => 5.2012,
                'longitude'             => -3.7375,
                'is_featured'           => 1,
                'is_active'             => 1,
                'sort_order'            => 3,
            ],

            [
                'name'                  => 'Man',
                'slug'                  => 'man',
                'district'              => 'District des Montagnes',
                'region_administrative' => 'Tonkpi',
                'description'           => 'Surnommée "la cité des 18 montagnes", Man est la porte d\'entrée des paysages montagneux de l\'ouest ivoirien. Cascades, ponts de lianes et danses de masques Guéré font sa renommée.',
                'thumbnail'             => 'https://upload.wikimedia.org/wikipedia/commons/thumb/e/e8/Man_Cote_Ivoire.jpg/640px-Man_Cote_Ivoire.jpg',
                'cover_image'           => 'https://upload.wikimedia.org/wikipedia/commons/thumb/e/e8/Man_Cote_Ivoire.jpg/1280px-Man_Cote_Ivoire.jpg',
                'latitude'              => 7.4125,
                'longitude'             => -7.5501,
                'is_featured'           => 0,
                'is_active'             => 1,
                'sort_order'            => 4,
            ],

            [
                'name'                  => 'San-Pédro',
                'slug'                  => 'san-pedro',
                'district'              => 'District du Bas-Sassandra',
                'region_administrative' => 'San-Pédro',
                'description'           => 'Deuxième port de Côte d\'Ivoire, San-Pédro offre de magnifiques plages sauvages, des forêts tropicales et une position idéale pour explorer le Parc National de Taï.',
                'thumbnail'             => 'https://upload.wikimedia.org/wikipedia/commons/thumb/b/bd/San_Pedro_Cote_Ivoire_Port.jpg/640px-San_Pedro_Cote_Ivoire_Port.jpg',
                'cover_image'           => 'https://upload.wikimedia.org/wikipedia/commons/thumb/b/bd/San_Pedro_Cote_Ivoire_Port.jpg/1280px-San_Pedro_Cote_Ivoire_Port.jpg',
                'latitude'              => 4.7485,
                'longitude'             => -6.6363,
                'is_featured'           => 0,
                'is_active'             => 1,
                'sort_order'            => 5,
            ],

            [
                'name'                  => 'Korhogo',
                'slug'                  => 'korhogo',
                'district'              => 'District des Savanes',
                'region_administrative' => 'Poro',
                'description'           => 'Capitale du nord et fief de la culture Sénoufo, Korhogo est réputée pour ses tisserands, ses sculpteurs, ses danseurs de masques et ses villages artisanaux traditionnels.',
                'thumbnail'             => 'https://upload.wikimedia.org/wikipedia/commons/thumb/6/6e/Korhogo_market.jpg/640px-Korhogo_market.jpg',
                'cover_image'           => 'https://upload.wikimedia.org/wikipedia/commons/thumb/6/6e/Korhogo_market.jpg/1280px-Korhogo_market.jpg',
                'latitude'              => 9.4578,
                'longitude'             => -5.6291,
                'is_featured'           => 0,
                'is_active'             => 1,
                'sort_order'            => 6,
            ],

            [
                'name'                  => 'Bouaké',
                'slug'                  => 'bouake',
                'district'              => 'District de la Vallée du Bandama',
                'region_administrative' => 'Gbêkê',
                'description'           => 'Deuxième ville de Côte d\'Ivoire, carrefour commercial central. Elle est connue pour son marché central animé, le Festival des Masques et ses fêtes traditionnelles Baoulé.',
                'thumbnail'             => 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/a8/Bouake_city_center.jpg/640px-Bouake_city_center.jpg',
                'cover_image'           => 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/a8/Bouake_city_center.jpg/1280px-Bouake_city_center.jpg',
                'latitude'              => 7.6906,
                'longitude'             => -5.0301,
                'is_featured'           => 0,
                'is_active'             => 1,
                'sort_order'            => 7,
            ],

            [
                'name'                  => 'Assinie',
                'slug'                  => 'assinie',
                'district'              => 'District des Lagunes',
                'region_administrative' => 'Sud-Comoé',
                'description'           => 'Station balnéaire par excellence de la Côte d\'Ivoire, Assinie est une presqu\'île entre l\'océan Atlantique et la lagune Aby. Ses plages immaculées en font une destination de villégiature incontournable.',
                'thumbnail'             => 'https://upload.wikimedia.org/wikipedia/commons/thumb/9/9d/Assinie_beach_Cote_Ivoire.jpg/640px-Assinie_beach_Cote_Ivoire.jpg',
                'cover_image'           => 'https://upload.wikimedia.org/wikipedia/commons/thumb/9/9d/Assinie_beach_Cote_Ivoire.jpg/1280px-Assinie_beach_Cote_Ivoire.jpg',
                'latitude'              => 5.1333,
                'longitude'             => -3.4667,
                'is_featured'           => 1,
                'is_active'             => 1,
                'sort_order'            => 8,
            ],

        ];

        foreach ($cities as $city) {
            TouristCity::updateOrCreate(['slug' => $city['slug']], $city);
        }

        $this->command->info('✓ ' . count($cities) . ' villes touristiques mises à jour avec bannières.');
    }
}
