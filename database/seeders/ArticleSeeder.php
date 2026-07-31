<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        $editor = User::where('role', 'editor')->first();
        $destCategory = ArticleCategory::where('slug', 'destination-du-mois')->first();
        $cultCategory = ArticleCategory::where('slug', 'culture-traditions')->first();
        $natCategory = ArticleCategory::where('slug', 'nature-aventure')->first();
        $gastCategory = ArticleCategory::where('slug', 'gastronomie')->first();
        $portraitsCategory = ArticleCategory::where('slug', 'portraits')->first();
        $agendaCategory = ArticleCategory::where('slug', 'agenda')->first();
        $pratiqueCategory = ArticleCategory::where('slug', 'pratique')->first();
        $artDeVivreCategory = ArticleCategory::where('slug', 'art-de-vivre')->first();

        $articles = [
            [
                'data' => [
                    'uuid' => (string) Str::uuid(),
                    'category_id' => $destCategory->id,
                    'author_id' => $editor->id,
                    'title_fr' => 'Grand-Bassam : l\'âme coloniale de la Côte d\'Ivoire',
                    'title_en' => 'Grand-Bassam: the colonial soul of Ivory Coast',
                    'slug_fr' => 'grand-bassam-ame-coloniale-cote-ivoire',
                    'slug_en' => 'grand-bassam-colonial-soul-ivory-coast',
                    'excerpt_fr' => 'Première capitale de la Côte d\'Ivoire et seul site ivoirien classé au Patrimoine mondial de l\'UNESCO, Grand-Bassam dévoile ses ruelles coloniales et ses plages dorées à une heure d\'Abidjan.',
                    'excerpt_en' => 'First capital of Ivory Coast and the only Ivorian site listed as a UNESCO World Heritage Site, Grand-Bassam reveals its colonial streets and golden beaches one hour from Abidjan.',
                    'content_fr' => '<p>Grand-Bassam, nichée entre l\'Océan Atlantique et la lagune Ébrié, est bien plus qu\'une escapade de week-end. C\'est une ville qui respire l\'histoire à chaque coin de rue, avec ses bâtiments coloniaux aux façades ocre et ses anciens entrepôts reconvertis en galeries d\'art.</p><p>La ville se divise en deux quartiers distincts : le Quartier France, cœur historique classé UNESCO en 2012, et le Quartier Impérial, animé par les pêcheurs Apolloniens dont les pirogues colorées longent la plage chaque matin à l\'aube.</p><h2>Que faire à Grand-Bassam ?</h2><p>Commencez par une promenade dans le Quartier France, où le Musée National du Costume présente 500 ans de textiles africains. À deux pas, le Musée de la maison coloniale retrace l\'époque où la ville était le centre névralgique de la colonie.</p>',
                    'reading_time' => 6,
                    'word_count' => 980,
                    'is_featured' => true,
                    'is_destination' => true,
                    'status' => 'published',
                    'meta_title_fr' => 'Grand-Bassam : site UNESCO & destination incontournable',
                    'meta_desc_fr' => 'Découvrez Grand-Bassam, première capitale de la Côte d\'Ivoire, classée au Patrimoine mondial UNESCO. Plages, quartier colonial, musées et gastronomie.',
                    'published_at' => now()->subDays(3),
                ],
                'tags' => ['grand-bassam', 'patrimoine', 'plage', 'coup-de-coeur'],
            ],
            [
                'data' => [
                    'uuid' => (string) Str::uuid(),
                    'category_id' => $cultCategory->id,
                    'author_id' => $editor->id,
                    'title_fr' => 'Les masques sacrés de l\'Ouest ivoirien : entre art et spiritualité',
                    'slug_fr' => 'masques-sacres-ouest-ivoirien-art-spiritualite',
                    'excerpt_fr' => 'Chez les Dan, Wé, Toura et Guéré, le masque n\'est pas un objet d\'art mais un être vivant, un intermédiaire entre le monde des vivants et celui des ancêtres. Voyage au cœur d\'une tradition millénaire.',
                    'content_fr' => '<p>Dans les forêts denses de l\'Ouest ivoirien, la tradition des masques occupe une place centrale dans la vie sociale et spirituelle des communautés. Pour les peuples Dan, Wé, Toura et Guéré, le masque — ou <em>ge</em> en langue dan — n\'est pas une simple pièce d\'artisanat.</p><p>C\'est une entité spirituelle à part entière, habitée par une force surnaturelle, qui intervient lors des cérémonies d\'initiation, des litiges villageois et des fêtes de récolte.</p><h2>Les différents types de masques</h2><p>Le masque de course (ge gla) est le plus rapide et le plus craint. Il parcourt les villages en courant, annonçant les décisions importantes. Le masque de fête (zakpei) au contraire symbolise la joie et la prospérité avec ses traits doux et ses ornements colorés.</p>',
                    'reading_time' => 8,
                    'word_count' => 1250,
                    'is_featured' => false,
                    'status' => 'published',
                    'meta_title_fr' => 'Masques sacrés ivoiriens — Art, culture et spiritualité',
                    'meta_desc_fr' => 'Plongez dans l\'univers des masques sacrés de l\'Ouest ivoirien. Histoire, signification et où les voir lors des cérémonies traditionnelles.',
                    'published_at' => now()->subDays(10),
                ],
                'tags' => ['masques', 'man', 'patrimoine', 'culture-traditions'],
            ],
            [
                'data' => [
                    'uuid' => (string) Str::uuid(),
                    'category_id' => $natCategory->id,
                    'author_id' => $editor->id,
                    'title_fr' => 'Parc National de Taï : sur les traces des chimpanzés',
                    'slug_fr' => 'parc-national-tai-traces-chimpanzes',
                    'excerpt_fr' => 'Au cœur de la dernière grande forêt primaire d\'Afrique de l\'Ouest, les chimpanzés du Parc de Taï utilisent des outils — une rare prouesse observée par les chercheurs depuis 40 ans. Comment les voir ?',
                    'content_fr' => '<p>Il faut marcher deux heures dans l\'humidité étouffante avant d\'entendre les premiers cris. Puis soudain, à travers l\'enchevêtrement de lianes, surgit une silhouette noire : un chimpanzé adulte, outil en main, en train de casser des noix de coula sur une enclume en bois.</p><p>Cette scène, unique au monde, se déroule chaque jour dans les 536 000 hectares du Parc National de Taï, classé au Patrimoine mondial de l\'UNESCO depuis 1982.</p>',
                    'reading_time' => 7,
                    'word_count' => 1100,
                    'is_featured' => true,
                    'status' => 'published',
                    'meta_title_fr' => 'Parc National de Taï : voir les chimpanzés en Côte d\'Ivoire',
                    'meta_desc_fr' => 'Guide complet pour visiter le Parc National de Taï, forêt UNESCO et refuge des chimpanzés. Accès, prix, hébergement et conseils.',
                    'published_at' => now()->subDays(7),
                ],
                'tags' => ['safari', 'nature', 'patrimoine', 'eco-tourisme'],
            ],
            [
                'data' => [
                    'uuid' => (string) Str::uuid(),
                    'category_id' => $gastCategory->id,
                    'author_id' => $editor->id,
                    'title_fr' => 'Le kedjenou : le plat national qui se cuisine à feu doux',
                    'slug_fr' => 'kedjenou-plat-national-cote-ivoire',
                    'excerpt_fr' => 'Poulet ou pintade, légumes du jardin, piment et gingembre mijotés dans une canari fermée sans eau ni matière grasse — le kedjenou est bien plus qu\'une recette, c\'est un art de vivre.',
                    'content_fr' => '<p>Le kedjenou est peut-être le plat ivoirien le plus emblématique. Son nom vient du dioula et signifie littéralement "secouer" — référence au geste traditionnel qui consiste à agiter la canari en terre cuite pendant la cuisson pour éviter que les aliments ne collent.</p><p>Ce plat est né dans les régions du centre et de l\'est du pays, chez les peuples Baoulé et Agni, avant de conquérir toute la Côte d\'Ivoire et sa diaspora mondiale.</p>',
                    'reading_time' => 5,
                    'word_count' => 820,
                    'is_featured' => false,
                    'status' => 'published',
                    'meta_title_fr' => 'Recette du Kedjenou ivoirien — Histoire & conseils de chef',
                    'meta_desc_fr' => 'Tout sur le kedjenou, plat emblématique de Côte d\'Ivoire. Histoire, recette authentique, variantes régionales et où le déguster à Abidjan.',
                    'published_at' => now()->subDays(15),
                ],
                'tags' => ['abidjan', 'coup-de-coeur', 'famille'],
            ],
            [
                'data' => [
                    'uuid' => (string) Str::uuid(),
                    'category_id' => $destCategory->id,
                    'author_id' => $editor->id,
                    'title_fr' => 'Yamoussoukro : la Basilique Notre-Dame de la Paix, plus grande église du monde',
                    'title_en' => 'Yamoussoukro: the Basilica of Our Lady of Peace, the world\'s largest church',
                    'slug_fr' => 'yamoussoukro-basilique-notre-dame-paix',
                    'slug_en' => 'yamoussoukro-basilica-our-lady-peace',
                    'excerpt_fr' => 'Voulue par Félix Houphouët-Boigny à l\'image de Saint-Pierre de Rome, la Basilique de Yamoussoukro impressionne par ses dimensions et son dôme visible à des kilomètres à la ronde.',
                    'excerpt_en' => 'Commissioned by Félix Houphouët-Boigny in the image of St Peter\'s in Rome, the Yamoussoukro Basilica impresses with its scale and its dome visible for miles around.',
                    'content_fr' => '<p>Surgissant au milieu de la savane, la Basilique Notre-Dame de la Paix de Yamoussoukro défie toutes les échelles. Achevée en 1989 et consacrée par le pape Jean-Paul II en 1990, elle occupe une superficie de 30 000 m² et sa croix culmine à 158 mètres, faisant d\'elle l\'édifice religieux chrétien le plus vaste au monde.</p><p>Son architecture reprend les codes de la Renaissance italienne tout en intégrant des vitraux représentant des scènes bibliques où figure, discrètement, le visage du président fondateur en berger parmi la foule. Autour de l\'édifice, une immense esplanade capable d\'accueillir 300 000 fidèles rappelle l\'ambition originelle de faire de Yamoussoukro une capitale spirituelle.</p><h2>Visiter la basilique</h2><p>Des visites guidées permettent de monter jusqu\'au dôme pour une vue panoramique sur la ville et les jardins environnants, plantés d\'après les plans du Vatican.</p>',
                    'reading_time' => 6,
                    'word_count' => 940,
                    'is_featured' => true,
                    'is_destination' => true,
                    'status' => 'published',
                    'meta_title_fr' => 'Basilique de Yamoussoukro : guide de visite',
                    'meta_desc_fr' => 'Découvrez la Basilique Notre-Dame de la Paix de Yamoussoukro, plus grand édifice religieux chrétien au monde. Histoire, architecture et conseils de visite.',
                    'published_at' => now()->subDays(20),
                ],
                'tags' => ['yamoussoukro', 'patrimoine', 'coup-de-coeur'],
            ],
            [
                'data' => [
                    'uuid' => (string) Str::uuid(),
                    'category_id' => $agendaCategory->id,
                    'author_id' => $editor->id,
                    'title_fr' => 'Festival des Masques de Man : quand la forêt s\'anime',
                    'slug_fr' => 'festival-masques-man-ouest-ivoirien',
                    'excerpt_fr' => 'Chaque année, la ville de Man réunit les plus grands danseurs de masques de la région des 18 Montagnes pour un concours haut en couleur, entre acrobaties et rites ancestraux.',
                    'content_fr' => '<p>Nichée parmi les 18 Montagnes, la ville de Man devient chaque année le théâtre d\'un rassemblement unique : le Festival des Masques. Danseurs échassiers, masques à échasses vertigineux et percussions endiablées se succèdent devant un public venu de toute la sous-région.</p><p>Au-delà du spectacle, l\'événement perpétue une tradition où chaque village défend l\'honneur de son masque à travers des figures acrobatiques transmises de génération en génération.</p>',
                    'reading_time' => 4,
                    'word_count' => 650,
                    'is_featured' => false,
                    'status' => 'published',
                    'meta_title_fr' => 'Festival des Masques de Man — dates et programme',
                    'meta_desc_fr' => 'Tout savoir sur le Festival des Masques de Man, rendez-vous incontournable de la culture dan dans l\'Ouest ivoirien.',
                    'published_at' => now()->subDays(25),
                ],
                'tags' => ['man', 'musique', 'patrimoine'],
            ],
            [
                'data' => [
                    'uuid' => (string) Str::uuid(),
                    'category_id' => $portraitsCategory->id,
                    'author_id' => $editor->id,
                    'title_fr' => 'Dobet Gnahoré, la voix ivoirienne qui rayonne à l\'international',
                    'slug_fr' => 'dobet-gnahore-voix-ivoirienne-international',
                    'excerpt_fr' => 'Née à Abidjan, formée dans le village-école de Ki-Yi Mbock, Dobet Gnahoré a conquis les scènes du monde entier avec un afro-jazz métissé de traditions ivoiriennes.',
                    'content_fr' => '<p>Fille du percussionniste Boni Gnahoré, Dobet Gnahoré a grandi au sein du village Ki-Yi M\'bock à Abidjan, véritable pépinière d\'artistes fondée par Werewere Liking. C\'est là qu\'elle apprend le chant, la danse et les percussions traditionnelles avant de se lancer dans une carrière solo remarquée.</p><p>Récompensée par un Grammy Award, elle continue de porter haut les couleurs de la musique ivoirienne à travers le monde, tout en revenant régulièrement se produire à Abidjan.</p>',
                    'reading_time' => 5,
                    'word_count' => 780,
                    'is_featured' => false,
                    'status' => 'published',
                    'meta_title_fr' => 'Portrait : Dobet Gnahoré, artiste ivoirienne primée aux Grammy',
                    'meta_desc_fr' => 'Portrait de Dobet Gnahoré, chanteuse ivoirienne formée à Ki-Yi M\'bock, aujourd\'hui reconnue sur les scènes internationales.',
                    'published_at' => now()->subDays(12),
                ],
                'tags' => ['musique', 'abidjan'],
            ],
            [
                'data' => [
                    'uuid' => (string) Str::uuid(),
                    'category_id' => $gastCategory->id,
                    'author_id' => $editor->id,
                    'title_fr' => 'L\'attiéké : la semoule de manioc qui accompagne tout à Abidjan',
                    'slug_fr' => 'attieke-semoule-manioc-abidjan',
                    'excerpt_fr' => 'Fermentée puis séchée, la semoule de manioc s\'invite sur toutes les tables ivoiriennes, du maquis de quartier au restaurant gastronomique. Focus sur un produit devenu symbole national.',
                    'content_fr' => '<p>Impossible d\'évoquer la cuisine ivoirienne sans parler d\'attiéké. Cette semoule obtenue à partir de manioc fermenté, râpé puis séché à la vapeur, accompagne aussi bien le poisson braisé que l\'alloco ou le poulet kedjenou.</p><p>Sa fabrication artisanale, essentiellement portée par des femmes dans des villages comme Dabou, en fait un pilier économique autant que culinaire, aujourd\'hui exporté dans toute la diaspora ivoirienne.</p>',
                    'reading_time' => 4,
                    'word_count' => 610,
                    'is_featured' => false,
                    'status' => 'published',
                    'meta_title_fr' => 'Attiéké : origine et secrets de la semoule ivoirienne',
                    'meta_desc_fr' => 'Découvrez l\'attiéké, semoule de manioc emblématique de la Côte d\'Ivoire : fabrication, origines et meilleures adresses pour en déguster à Abidjan.',
                    'published_at' => now()->subDays(18),
                ],
                'tags' => ['abidjan', 'famille', 'coup-de-coeur'],
            ],
            [
                'data' => [
                    'uuid' => (string) Str::uuid(),
                    'category_id' => $pratiqueCategory->id,
                    'author_id' => $editor->id,
                    'title_fr' => 'Partir en Côte d\'Ivoire : visa, santé et budget, le guide complet',
                    'slug_fr' => 'guide-pratique-visa-sante-budget-cote-ivoire',
                    'excerpt_fr' => 'Visa électronique, vaccin contre la fièvre jaune obligatoire, budget quotidien moyen : tout ce qu\'il faut préparer avant d\'atterrir à Abidjan.',
                    'content_fr' => '<p>Le visa électronique ivoirien se demande en ligne quelques jours avant le départ et permet un séjour touristique de 90 jours. La vaccination contre la fièvre jaune est obligatoire pour tout voyageur et doit figurer sur le carnet de vaccination international.</p><p>Côté budget, comptez un hébergement correct dès 15 000 FCFA la nuit à Abidjan, et prévoyez une marge pour les déplacements en taxi ou en woro-woro, très pratiques pour explorer la ville.</p>',
                    'reading_time' => 5,
                    'word_count' => 720,
                    'is_featured' => false,
                    'status' => 'published',
                    'meta_title_fr' => 'Guide pratique : visa, santé et budget pour la Côte d\'Ivoire',
                    'meta_desc_fr' => 'Guide pratique complet pour préparer son voyage en Côte d\'Ivoire : formalités de visa, vaccination et budget moyen sur place.',
                    'published_at' => now()->subDays(30),
                ],
                'tags' => ['famille', 'coup-de-coeur'],
            ],
            [
                'data' => [
                    'uuid' => (string) Str::uuid(),
                    'category_id' => $artDeVivreCategory->id,
                    'author_id' => $editor->id,
                    'title_fr' => 'Le pagne baoulé, entre tradition tissée et haute couture ivoirienne',
                    'slug_fr' => 'pagne-baoule-tradition-haute-couture',
                    'excerpt_fr' => 'Tissé à la main sur des métiers traditionnels, le pagne baoulé habille aujourd\'hui aussi bien les cérémonies villageoises que les podiums des créateurs ivoiriens.',
                    'content_fr' => '<p>Dans la région de Bouaké, le tissage du pagne baoulé se transmet depuis des générations, chaque motif racontant une histoire, un statut social ou un événement familial. Les bandes de coton tissées à la main sont ensuite assemblées pour former de larges étoffes.</p><p>Longtemps réservé aux grandes cérémonies, le pagne baoulé inspire désormais une nouvelle génération de designers ivoiriens qui le réinventent sur les podiums de la Fashion Week d\'Abidjan.</p>',
                    'reading_time' => 5,
                    'word_count' => 690,
                    'is_featured' => false,
                    'status' => 'published',
                    'meta_title_fr' => 'Pagne baoulé : histoire et renaissance d\'un tissu ivoirien',
                    'meta_desc_fr' => 'Le pagne baoulé, textile traditionnel de Côte d\'Ivoire, entre savoir-faire ancestral et haute couture contemporaine.',
                    'published_at' => now()->subDays(8),
                ],
                'tags' => ['patrimoine', 'luxe'],
            ],
        ];

        foreach ($articles as $entry) {
            $article = Article::withTrashed()->updateOrCreate(['slug_fr' => $entry['data']['slug_fr']], $entry['data']);
            if ($article->trashed()) {
                $article->restore();
            }

            $tagIds = Tag::whereIn('slug', $entry['tags'])->pluck('id');
            $article->tags()->sync($tagIds);
        }
    }
}
