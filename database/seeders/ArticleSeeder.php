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
        ];

        foreach ($articles as $entry) {
            $article = Article::updateOrCreate(['slug_fr' => $entry['data']['slug_fr']], $entry['data']);

            $tagIds = Tag::whereIn('slug', $entry['tags'])->pluck('id');
            $article->tags()->sync($tagIds);
        }
    }
}
