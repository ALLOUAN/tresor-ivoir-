<?php

namespace Database\Seeders;

use App\Models\CulturalDomain;
use Illuminate\Database\Seeder;

class CulturalDomainSeeder extends Seeder
{
    public function run(): void
    {
        // ── Domaines racines ─────────────────────────────────────────────────
        $roots = [

            [
                'slug'        => 'musique-chant',
                'name'        => 'Musique & Chant',
                'icon'        => 'fas fa-music',
                'color'       => '#8B5CF6',
                'description' => 'Traditions musicales vocales et instrumentales des peuples ivoiriens : percussions, cordes, vents et polyphonies.',
                'sort_order'  => 1,
            ],
            [
                'slug'        => 'danse-spectacle',
                'name'        => 'Danse & Spectacle',
                'icon'        => 'fas fa-person-dancing',
                'color'       => '#EC4899',
                'description' => 'Danses cérémonielles, danses des masques, acrobaties, danses guerrières et expressions corporelles rituelles.',
                'sort_order'  => 2,
            ],
            [
                'slug'        => 'masques-arts-sacres',
                'name'        => 'Masques & Arts Sacrés',
                'icon'        => 'fas fa-mask',
                'color'       => '#F43F5E',
                'description' => 'Masques rituels, fétiches, objets de culte et arts liés aux sociétés secrètes et aux croyances traditionnelles.',
                'sort_order'  => 3,
            ],
            [
                'slug'        => 'gastronomie',
                'name'        => 'Gastronomie',
                'icon'        => 'fas fa-utensils',
                'color'       => '#F97316',
                'description' => 'Cuisine traditionnelle ivoirienne, plats emblématiques, techniques culinaires ancestrales et boissons rituelles.',
                'sort_order'  => 4,
            ],
            [
                'slug'        => 'artisanat-arts-visuels',
                'name'        => 'Artisanat & Arts Visuels',
                'icon'        => 'fas fa-hands',
                'color'       => '#EAB308',
                'description' => 'Sculpture, tissage, poterie, peinture, orfèvrerie et toutes les formes d\'expression plastique et artisanale.',
                'sort_order'  => 5,
            ],
            [
                'slug'        => 'langue-litterature-orale',
                'name'        => 'Langue & Littérature Orale',
                'icon'        => 'fas fa-comments',
                'color'       => '#14B8A6',
                'description' => 'Contes, légendes, proverbes, épopées, devinettes et toute la richesse de la parole transmise oralement de génération en génération.',
                'sort_order'  => 6,
            ],
            [
                'slug'        => 'ceremonies-rites',
                'name'        => 'Cérémonies & Rites',
                'icon'        => 'fas fa-star-and-crescent',
                'color'       => '#D97706',
                'description' => 'Rites de passage, funérailles, initiations, fêtes de récolte, cérémonies de purification et rituels du calendrier traditionnel.',
                'sort_order'  => 7,
            ],
            [
                'slug'        => 'tenues-parures',
                'name'        => 'Tenues & Parures',
                'icon'        => 'fas fa-gem',
                'color'       => '#A855F7',
                'description' => 'Vêtements cérémoniels, pagnes traditionnels, bijoux, coiffures et parures corporelles à valeur symbolique et identitaire.',
                'sort_order'  => 8,
            ],
            [
                'slug'        => 'architecture-traditionnelle',
                'name'        => 'Architecture Traditionnelle',
                'icon'        => 'fas fa-house',
                'color'       => '#F59E0B',
                'description' => 'Habitations en terre, greniers à mil, cases rondes, soukalas fortifiées et techniques de construction ancestrales.',
                'sort_order'  => 9,
            ],

        ];

        $rootIds = [];
        foreach ($roots as $root) {
            $domain = CulturalDomain::updateOrCreate(
                ['slug' => $root['slug']],
                array_merge($root, ['parent_id' => null, 'is_active' => 1])
            );
            $rootIds[$root['slug']] = $domain->id;
        }

        // ── Sous-domaines ────────────────────────────────────────────────────
        $children = [

            // Musique & Chant
            [
                'parent_slug' => 'musique-chant',
                'slug'        => 'musique-traditionnelle',
                'name'        => 'Musique Traditionnelle',
                'icon'        => 'fas fa-drum',
                'color'       => '#7C3AED',
                'description' => 'Balafon, djembé, tam-tam, kora, trompe en ivoire — instruments et répertoires rituels des ethnies ivoiriennes.',
                'sort_order'  => 1,
            ],
            [
                'parent_slug' => 'musique-chant',
                'slug'        => 'musique-contemporaine',
                'name'        => 'Musique Contemporaine',
                'icon'        => 'fas fa-record-vinyl',
                'color'       => '#6D28D9',
                'description' => 'Coupé-Décalé, Zouglou, Afrobeats ivoirien, Magic System — la Côte d\'Ivoire au cœur de la musique africaine mondiale.',
                'sort_order'  => 2,
            ],
            [
                'parent_slug' => 'musique-chant',
                'slug'        => 'chants-polyphoniques',
                'name'        => 'Chants & Polyphonies',
                'icon'        => 'fas fa-microphone',
                'color'       => '#5B21B6',
                'description' => 'Chants de griots, polyphonies féminines, hymnes rituels et chants de travail des communautés rurales.',
                'sort_order'  => 3,
            ],

            // Danse & Spectacle
            [
                'parent_slug' => 'danse-spectacle',
                'slug'        => 'danse-des-masques',
                'name'        => 'Danse des Masques',
                'icon'        => 'fas fa-person-walking',
                'color'       => '#BE185D',
                'description' => 'Sorties rituelles des masques dansants : Zaouli baoulé, Goli, Djékaré, Dozo — spectacles sacrés à forte valeur symbolique.',
                'sort_order'  => 1,
            ],
            [
                'parent_slug' => 'danse-spectacle',
                'slug'        => 'danses-populaires',
                'name'        => 'Danses Populaires',
                'icon'        => 'fas fa-circle-dot',
                'color'       => '#9D174D',
                'description' => 'Danses sociales festives : Mapouka, Gbégbé, Kpanlogo, Adjo — expressions collectives de joie et de convivialité.',
                'sort_order'  => 2,
            ],
            [
                'parent_slug' => 'danse-spectacle',
                'slug'        => 'acrobaties-echasses',
                'name'        => 'Acrobaties & Échasses',
                'icon'        => 'fas fa-person-falling',
                'color'       => '#831843',
                'description' => 'Danseurs sur échasses Dan, acrobates de la région de Man — performances physiques à dimension rituelle et festive.',
                'sort_order'  => 3,
            ],

            // Masques & Arts Sacrés
            [
                'parent_slug' => 'masques-arts-sacres',
                'slug'        => 'masques-rituels',
                'name'        => 'Masques Rituels',
                'icon'        => 'fas fa-theater-masks',
                'color'       => '#BE123C',
                'description' => 'Masques de justice, de guerre, d\'initiation : Kpelié sénoufo, Tanglé guéré, Mblo baoulé, Koma dan.',
                'sort_order'  => 1,
            ],
            [
                'parent_slug' => 'masques-arts-sacres',
                'slug'        => 'societes-secretes',
                'name'        => 'Sociétés Secrètes & Initiations',
                'icon'        => 'fas fa-eye',
                'color'       => '#9F1239',
                'description' => 'Poro sénoufo, Bondo, Sandogo — sociétés d\'initiation masculine et féminine qui structurent les grandes transitions de la vie.',
                'sort_order'  => 2,
            ],

            // Gastronomie
            [
                'parent_slug' => 'gastronomie',
                'slug'        => 'plats-traditionnels',
                'name'        => 'Plats Traditionnels',
                'icon'        => 'fas fa-bowl-food',
                'color'       => '#C2410C',
                'description' => 'Attiéké, kedjenou, foutou banane, sauce graine, alloco, soupe kplala — la richesse culinaire de toutes les régions.',
                'sort_order'  => 1,
            ],
            [
                'parent_slug' => 'gastronomie',
                'slug'        => 'boissons-traditionnelles',
                'name'        => 'Boissons Traditionnelles',
                'icon'        => 'fas fa-cup-straw',
                'color'       => '#9A3412',
                'description' => 'Bangui (vin de palme), dolo (bière de mil), gnamankoudji (gingembre) — boissons fermentées et infusions rituelles.',
                'sort_order'  => 2,
            ],

            // Artisanat & Arts Visuels
            [
                'parent_slug' => 'artisanat-arts-visuels',
                'slug'        => 'sculpture-bois-bronze',
                'name'        => 'Sculpture (Bois & Bronze)',
                'icon'        => 'fas fa-shapes',
                'color'       => '#B45309',
                'description' => 'Statuettes ancestrales, figurines thil lobi, bronzes ashanti — art de la sculpture sur bois et du moulage en métal.',
                'sort_order'  => 1,
            ],
            [
                'parent_slug' => 'artisanat-arts-visuels',
                'slug'        => 'tissage-textile',
                'name'        => 'Tissage & Textile',
                'icon'        => 'fas fa-shirt',
                'color'       => '#92400E',
                'description' => 'Pagne sénoufo à bandes, tissu kita baoulé, bogolan dioula, teinture à l\'indigo — art textile des ethnies du nord et du centre.',
                'sort_order'  => 2,
            ],
            [
                'parent_slug' => 'artisanat-arts-visuels',
                'slug'        => 'orfevrerie-bijoux',
                'name'        => 'Orfèvrerie & Bijoux',
                'icon'        => 'fas fa-ring',
                'color'       => '#78350F',
                'description' => 'Orfèvrerie agni en or, bijoux sénoufo en bronze, amulettes lobi — maîtrise des métaux précieux et ornements identitaires.',
                'sort_order'  => 3,
            ],
            [
                'parent_slug' => 'artisanat-arts-visuels',
                'slug'        => 'poterie-ceramique',
                'name'        => 'Poterie & Céramique',
                'icon'        => 'fas fa-jug',
                'color'       => '#6B2D0E',
                'description' => 'Poteries utilitaires et rituelles, jarres funéraires, pipes en terre — savoir-faire féminin transmis de mère en fille.',
                'sort_order'  => 4,
            ],

            // Langue & Littérature Orale
            [
                'parent_slug' => 'langue-litterature-orale',
                'slug'        => 'contes-legendes',
                'name'        => 'Contes & Légendes',
                'icon'        => 'fas fa-book-open',
                'color'       => '#0F766E',
                'description' => 'Contes d\'Araignée (Ananse), légendes de fondation, récits cosmogoniques — imaginaire collectif des veillées villageoises.',
                'sort_order'  => 1,
            ],
            [
                'parent_slug' => 'langue-litterature-orale',
                'slug'        => 'epopees-griots',
                'name'        => 'Épopées & Griots',
                'icon'        => 'fas fa-scroll',
                'color'       => '#115E59',
                'description' => 'Épopée de Soundjata Keïta, récits des griots mandé (djeli), chants de louange des cours royales akan.',
                'sort_order'  => 2,
            ],
            [
                'parent_slug' => 'langue-litterature-orale',
                'slug'        => 'proverbes-sagesse',
                'name'        => 'Proverbes & Sagesse',
                'icon'        => 'fas fa-feather',
                'color'       => '#134E4A',
                'description' => 'Proverbes baoulé, bété, dioula — philosophie orale condensée transmettant les valeurs et l\'éthique communautaire.',
                'sort_order'  => 3,
            ],

            // Cérémonies & Rites
            [
                'parent_slug' => 'ceremonies-rites',
                'slug'        => 'rites-de-passage',
                'name'        => 'Rites de Passage',
                'icon'        => 'fas fa-arrow-right',
                'color'       => '#B45309',
                'description' => 'Excision, circoncision, initiations au Poro, premières règles, mariage coutumier — rites marquant les grandes étapes de la vie.',
                'sort_order'  => 1,
            ],
            [
                'parent_slug' => 'ceremonies-rites',
                'slug'        => 'fetes-traditionnelles',
                'name'        => 'Fêtes Traditionnelles',
                'icon'        => 'fas fa-calendar-days',
                'color'       => '#92400E',
                'description' => 'Dipri (Abidji), Abissa (Adjoukrou), Fête des ignames, Pâques de Bingerville — célébrations cycliques du calendrier villageois.',
                'sort_order'  => 2,
            ],
            [
                'parent_slug' => 'ceremonies-rites',
                'slug'        => 'funerailles-deuil',
                'name'        => 'Funérailles & Deuil',
                'icon'        => 'fas fa-cross',
                'color'       => '#78350F',
                'description' => 'Second deuil agni, veillées funèbres baoulé, cérémonies lobi — rites mortuaires parmi les plus élaborés d\'Afrique de l\'Ouest.',
                'sort_order'  => 3,
            ],

            // Tenues & Parures
            [
                'parent_slug' => 'tenues-parures',
                'slug'        => 'vetements-ceremoniels',
                'name'        => 'Vêtements Cérémoniels',
                'icon'        => 'fas fa-user-tie',
                'color'       => '#7E22CE',
                'description' => 'Toge des chefs baoulé, boubou sénoufo, grand boubou mandé, tunique de chasse Donzo — vêtements à forte charge symbolique.',
                'sort_order'  => 1,
            ],
            [
                'parent_slug' => 'tenues-parures',
                'slug'        => 'coiffures-scarifications',
                'name'        => 'Coiffures & Scarifications',
                'icon'        => 'fas fa-user',
                'color'       => '#6B21A8',
                'description' => 'Tresses sénoufo, coiffures de deuil, scarifications ethniques (Sénoufo, Lobi) — marqueurs identitaires corporels.',
                'sort_order'  => 2,
            ],

        ];

        foreach ($children as $child) {
            $parentId = $rootIds[$child['parent_slug']] ?? null;
            unset($child['parent_slug']);
            CulturalDomain::updateOrCreate(
                ['slug' => $child['slug']],
                array_merge($child, ['parent_id' => $parentId, 'is_active' => 1])
            );
        }

        $total  = CulturalDomain::count();
        $racines = CulturalDomain::whereNull('parent_id')->count();
        $subs   = $total - $racines;
        $this->command->info("✓ {$total} domaines culturels ({$racines} racines + {$subs} sous-domaines).");
    }
}
