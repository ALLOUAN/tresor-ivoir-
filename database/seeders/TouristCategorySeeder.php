<?php

namespace Database\Seeders;

use App\Models\TouristCategory;
use Illuminate\Database\Seeder;

class TouristCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [

            // ── NATURE & PAYSAGES ────────────────────────────────────────────

            [
                'name'        => 'Plages & Lagunes',
                'slug'        => 'plages-lagunes',
                'icon'        => 'fas fa-water',
                'color'       => '#0EA5E9',
                'description' => 'Plages de sable fin, lagunes côtières, presqu\'îles et rivages de l\'Atlantique. La Côte d\'Ivoire compte des centaines de kilomètres de côtes tropicales.',
                'sort_order'  => 1,
                'is_active'   => 1,
            ],
            [
                'name'        => 'Parcs & Réserves Naturelles',
                'slug'        => 'parcs-reserves-naturelles',
                'icon'        => 'fas fa-tree',
                'color'       => '#22C55E',
                'description' => 'Parcs nationaux, réserves de faune, forêts classées et espaces protégés. La Côte d\'Ivoire abrite plusieurs écosystèmes remarquables.',
                'sort_order'  => 2,
                'is_active'   => 1,
            ],
            [
                'name'        => 'Cascades & Montagnes',
                'slug'        => 'cascades-montagnes',
                'icon'        => 'fas fa-mountain',
                'color'       => '#6EE7B7',
                'description' => 'Chutes d\'eau spectaculaires, massifs montagneux de l\'ouest ivoirien, le Mont Nimba et les pics de Man.',
                'sort_order'  => 3,
                'is_active'   => 1,
            ],
            [
                'name'        => 'Lacs & Cours d\'eau',
                'slug'        => 'lacs-cours-eau',
                'icon'        => 'fas fa-droplet',
                'color'       => '#38BDF8',
                'description' => 'Grands lacs artificiels (lac de Kossou, lac d\'Ayamé), fleuves et rivières navigables traversant le pays.',
                'sort_order'  => 4,
                'is_active'   => 1,
            ],

            // ── PATRIMOINE & HISTOIRE ────────────────────────────────────────

            [
                'name'        => 'Monuments & Patrimoine',
                'slug'        => 'monuments-patrimoine',
                'icon'        => 'fas fa-landmark',
                'color'       => '#F59E0B',
                'description' => 'Édifices historiques, anciens forts coloniaux, sites classés UNESCO et patrimoine architectural de toutes époques.',
                'sort_order'  => 5,
                'is_active'   => 1,
            ],
            [
                'name'        => 'Architecture Sacrée',
                'slug'        => 'architecture-sacree',
                'icon'        => 'fas fa-place-of-worship',
                'color'       => '#C084FC',
                'description' => 'Basiliques, cathédrales, grandes mosquées, temples bouddhistes et lieux de culte traditionnels ivoiriens.',
                'sort_order'  => 6,
                'is_active'   => 1,
            ],
            [
                'name'        => 'Sites Historiques',
                'slug'        => 'sites-historiques',
                'icon'        => 'fas fa-scroll',
                'color'       => '#D97706',
                'description' => 'Lieux témoins de l\'histoire ivoirienne : anciens royaumes, comptoirs coloniaux, champs de bataille et mémoriaux.',
                'sort_order'  => 7,
                'is_active'   => 1,
            ],

            // ── CULTURE & TRADITIONS ─────────────────────────────────────────

            [
                'name'        => 'Musées & Galeries',
                'slug'        => 'musees-galeries',
                'icon'        => 'fas fa-building-columns',
                'color'       => '#EC4899',
                'description' => 'Musées nationaux et régionaux, galeries d\'art contemporain et espaces d\'exposition permanente ou temporaire.',
                'sort_order'  => 8,
                'is_active'   => 1,
            ],
            [
                'name'        => 'Sites Culturels',
                'slug'        => 'sites-culturels',
                'icon'        => 'fas fa-masks-theater',
                'color'       => '#A855F7',
                'description' => 'Centres culturels, maisons des arts, théâtres et espaces communautaires de création et de diffusion artistique.',
                'sort_order'  => 9,
                'is_active'   => 1,
            ],
            [
                'name'        => 'Villages & Ethnies',
                'slug'        => 'villages-ethnies',
                'icon'        => 'fas fa-people-roof',
                'color'       => '#FB923C',
                'description' => 'Villages traditionnels des 60 groupes ethniques ivoiriens : Baoulé, Bété, Dan, Sénoufo, Lobi, Agni, Dioula et bien d\'autres.',
                'sort_order'  => 10,
                'is_active'   => 1,
            ],
            [
                'name'        => 'Cérémonies & Rituels',
                'slug'        => 'ceremonies-rituels',
                'icon'        => 'fas fa-drum',
                'color'       => '#F43F5E',
                'description' => 'Danses de masques sacrés, cérémonies d\'initiation, fêtes traditionnelles et rituels ancestraux des peuples de Côte d\'Ivoire.',
                'sort_order'  => 11,
                'is_active'   => 1,
            ],

            // ── ARTISANAT & COMMERCE ─────────────────────────────────────────

            [
                'name'        => 'Artisanat & Marchés',
                'slug'        => 'artisanat-marches',
                'icon'        => 'fas fa-hands',
                'color'       => '#EAB308',
                'description' => 'Marchés artisanaux, ateliers de tissage wax, sculpture sur bois, poterie, bijouterie et teinture traditionnelle.',
                'sort_order'  => 12,
                'is_active'   => 1,
            ],
            [
                'name'        => 'Gastronomie & Maquis',
                'slug'        => 'gastronomie-maquis',
                'icon'        => 'fas fa-utensils',
                'color'       => '#F97316',
                'description' => 'Restaurants typiques, maquis ivoiriens, marchés alimentaires et expériences culinaires autour de l\'attiéké, du kedjenou et du foutou.',
                'sort_order'  => 13,
                'is_active'   => 1,
            ],

            // ── ACTIVITÉS & LOISIRS ──────────────────────────────────────────

            [
                'name'        => 'Sports & Aventure',
                'slug'        => 'sports-aventure',
                'icon'        => 'fas fa-person-hiking',
                'color'       => '#14B8A6',
                'description' => 'Randonnées pédestres, sports nautiques, escalade, VTT, safari photo et activités de plein air en pleine nature.',
                'sort_order'  => 14,
                'is_active'   => 1,
            ],
            [
                'name'        => 'Vie Nocturne & Divertissement',
                'slug'        => 'vie-nocturne-divertissement',
                'icon'        => 'fas fa-music',
                'color'       => '#8B5CF6',
                'description' => 'Clubs, bars, concerts, spectacles vivants et la scène musicale Coupé-Décalé et Zouglou d\'Abidjan.',
                'sort_order'  => 15,
                'is_active'   => 1,
            ],
            [
                'name'        => 'Parcs de Loisirs & Zoos',
                'slug'        => 'parcs-loisirs-zoos',
                'icon'        => 'fas fa-ferris-wheel',
                'color'       => '#10B981',
                'description' => 'Parcs de loisirs familiaux, zoos, jardins botaniques et espaces récréatifs pour petits et grands.',
                'sort_order'  => 16,
                'is_active'   => 1,
            ],
        ];

        foreach ($categories as $cat) {
            TouristCategory::updateOrCreate(['slug' => $cat['slug']], $cat);
        }

        // Désactiver les anciennes catégories remplacées
        TouristCategory::whereIn('slug', [
            'parcs-espaces-naturels',   // remplacée par parcs-reserves-naturelles
            'artisanat-villages',        // remplacée par artisanat-marches
            'gastronomie-marches',       // remplacée par gastronomie-maquis
        ])->update(['is_active' => 0]);

        $total = TouristCategory::count();
        $actif = TouristCategory::where('is_active', 1)->count();

        $this->command->info("✓ {$total} catégories touristiques ({$actif} actives).");
    }
}
