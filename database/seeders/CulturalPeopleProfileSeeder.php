<?php

namespace Database\Seeders;

use App\Models\CulturalDomain;
use App\Models\CulturalElement;
use App\Models\CulturalPeople;
use Database\Seeders\Concerns\SeedsMissingReferenceData;
use Database\Seeders\Data\IvorianSources as S;
use Illuminate\Database\Seeder;

/**
 * Complète les quatre onglets de chaque fiche peuple (Présentation, Éléments culturels,
 * Symboles, Histoire & sources) :
 *  - Symboles : fiche d'identité factuelle (grand groupe, langue, ville de référence) et repères
 *    documentés (fête, art, patrimoine UNESCO…), seulement si l'onglet est vide ;
 *  - Sources : ajoutées aux peuples qui n'en ont pas ;
 *  - Éléments culturels : nouveaux éléments partagés (classes d'âge lagunaires, pirogues du
 *    littoral, initiation du djoro) et rattachement de peuples à des éléments existants
 *    (attiéké, Abissa, confrérie des Donzo, tissage du pagne) — ajout seulement, jamais de retrait.
 *
 * À lancer après CulturalPeopleCompletionSeeder et CulturalElementCompletionSeeder.
 */
class CulturalPeopleProfileSeeder extends Seeder
{
    use SeedsMissingReferenceData;

    /** Repères documentés propres à chaque peuple (en plus de la fiche d'identité). */
    private const REPERES = [
        'baoule' => [['Fondatrice selon la tradition', 'La reine Abla Pokou'], ['Arts emblématiques', 'Masques Mblo et Goli, statuettes Blolo'], ['Grande fête', 'Paquinou (Pâques)']],
        'abbey-abe' => [['Organisation sociale', 'Classes d\'âge (générations)']],
        'aboure' => [['Organisation sociale', 'Classes d\'âge (générations)'], ['Grande fête', 'Popo Carnaval de Bonoua']],
        'abidji' => [['Organisation sociale', 'Classes d\'âge (générations)'], ['Grande fête', 'Le Dipri, à Gomon']],
        'alladian' => [['Cadre de vie', 'Cordon littoral entre lagune et océan'], ['Activité traditionnelle', 'Pêche en pirogue']],
        'avikam' => [['Cadre de vie', 'Embouchure du Bandama, lagune Tagba'], ['Activité traditionnelle', 'Pêche en pirogue']],
        'ebrie-tchaman' => [['Organisation sociale', 'Classes d\'âge (générations)'], ['Territoire', 'Rives de la lagune Ébrié (Abidjan)']],
        'eotile-ehotile' => [['Lieu sacré', 'Îles Ehotilé (lagune Aby)'], ['Organisation sociale', 'Classes d\'âge (générations)']],
        'nzima-appolo' => [['Grande fête', 'L\'Abissa (Grand-Bassam)'], ['Institution', 'Royauté n\'zima']],
        'dida' => [['Artisanat', 'Pagnes en raphia teints par réserve']],
        'godie' => [['Région', 'Entre Lakota, Fresco et Sassandra']],
        'neyo' => [['Activité traditionnelle', 'Pêche en mer et en pirogue']],
        'kroumen' => [['Tradition', 'Navigation et pêche en mer (« Krou boys »)']],
        'bakwe' => [['Région', 'Forêts du Bas-Sassandra (Soubré, San-Pédro)']],
        'gouro' => [['Patrimoine UNESCO', 'Le Zaouli (2017)'], ['Masques', 'Zaouli, Zamblé et Gu']],
        'yaoure' => [['Art emblématique', 'Masques à visage humain à bordure dentelée']],
        'gban-gagou' => [['Tradition orale', 'Parmi les premiers occupants du centre-ouest']],
        'toura' => [['Nom propre', 'Win-minh'], ['Grande fête', 'Le Yabreu (ignames nouvelles)'], ['Masques', 'Gôh-oh']],
        'koyaka' => [['Région', 'Le Béré (Mankono)']],
        'mahou' => [['Religion majoritaire', 'Islam'], ['Centre historique', 'Touba']],
        'koulango' => [['Institution', 'Royaume de Bouna'], ['Tradition', 'Premiers occupants du nord-est']],
        'tagbana' => [['Patrimoine UNESCO', 'Le Gbofe d\'Afounkaha (2008)'], ['Ensemble culturel', 'Sénoufo']],
        'djimini' => [['Ensemble culturel', 'Sénoufo'], ['Histoire', 'Région marquée par les campagnes de Samory Touré']],
        'nafana' => [['Ensemble culturel', 'Sénoufo'], ['Masques', 'Bedu (masques-planches)']],
        'lorhon' => [['Parents culturels', 'Koulango et Lobi']],
        'birifor' => [['Initiation', 'Le djoro, partagé avec les Lobi'], ['Habitat', 'Maisons en terre dispersées']],
    ];

    public function run(): void
    {
        $general = [S::WIKI_DEMOGRAPHIE, S::WIKI_LANGUES, S::ETHNOLOGUE, S::INS_RGPH];
        $peoples = CulturalPeople::all()->keyBy('slug');

        // ── Symboles (onglet vide uniquement) ────────────────────────────────
        $symbolsAdded = 0;
        foreach ($peoples as $slug => $people) {
            if (! empty($people->symboles)) {
                continue;
            }
            $items = array_filter([
                $people->famille_linguistique ? ['label' => 'Grand groupe', 'valeur' => $people->famille_linguistique] : null,
                $people->langue_principale ? ['label' => 'Langue', 'valeur' => $people->langue_principale] : null,
                $people->capitale_culturelle ? ['label' => 'Ville de référence', 'valeur' => $people->capitale_culturelle] : null,
            ]);
            foreach (self::REPERES[$slug] ?? [] as [$label, $value]) {
                $items[] = ['label' => $label, 'valeur' => $value];
            }
            if ($items) {
                $people->forceFill(['symboles' => array_values($items)])->save();
                $symbolsAdded++;
            }
        }
        $this->command?->info("✓ Symboles : {$symbolsAdded} fiche(s) complétée(s).");

        // ── Sources des peuples qui n'en ont pas ─────────────────────────────
        $this->report('Sources des peuples', $this->seedMissing(CulturalPeople::class, $peoples->keys()
            ->map(fn ($slug) => ['slug' => $slug, 'sources' => $general])->values()->all()));

        // ── Éléments culturels partagés ──────────────────────────────────────
        $d = CulturalDomain::pluck('id', 'slug');
        $id = fn (string $slug) => $peoples[$slug]->id ?? null;
        $roles = fn (array $slugs, string $role = 'peuple d\'origine') => collect($slugs)->map($id)->filter()
            ->map(fn ($pid) => ['people_id' => $pid, 'role' => $role])->values()->all();

        $shared = [
            [
                'slug' => 'classes-age-peuples-lagunaires',
                'domain_id' => $d['rites-de-passage'] ?? null,
                'people_roles' => $roles(['ebrie-tchaman', 'abbey-abe', 'abidji', 'aboure', 'adjoukrou', 'attie', 'alladian', 'avikam', 'eotile-ehotile']),
                'name' => 'Les classes d\'âge des peuples lagunaires',
                'short_description' => 'Organisation de la société en générations successives qui se transmettent les responsabilités lors de grandes cérémonies.',
                'description' => "Chez les peuples lagunaires du sud (Ébrié, Abbey, Abidji, Abouré, Adjoukrou, Attié, Alladian, Avikam, Éotilé…), les hommes sont regroupés en classes d'âge, ou générations. Chaque génération assume à son tour des responsabilités dans la vie du village : défense, travaux collectifs, organisation des fêtes, gestion des affaires communes.\n\nLe passage d'une génération à la suivante donne lieu à de grandes cérémonies, avec danses, parures et rassemblements de toute la communauté. Ce système structure encore fortement la vie sociale des villages lagunaires, y compris au cœur de l'agglomération d'Abidjan.",
                'origine_historique' => 'Système ancien commun aux sociétés akan lagunaires, transmis de génération en génération et toujours vivant.',
                'niveau_risque' => 'stable',
                'practical_info' => [['icon' => 'fas fa-map-pin', 'label' => 'Région', 'value' => 'Lagunes et littoral sud']],
                'is_active' => true, 'sort_order' => 200,
                'sources' => [S::WIKI_DEMOGRAPHIE, S::wiki('Abés', 'Abés (Abbey)'), S::wiki('Abourés')],
            ],
            [
                'slug' => 'pirogues-peche-littoral',
                'domain_id' => $d['sculpture-bois-bronze'] ?? null,
                'people_roles' => $roles(['alladian', 'avikam', 'neyo', 'kroumen']),
                'name' => 'Les pirogues et la pêche du littoral',
                'short_description' => 'Pirogues taillées dans un seul tronc et savoir-faire de pêche en lagune et en mer des peuples du littoral.',
                'description' => "Le long du littoral ivoirien, les peuples côtiers et lagunaires (Alladian, Avikam, Néyo, Kroumen…) ont développé une culture de l'eau : pirogues monoxyles taillées dans un seul tronc, techniques de pêche en lagune et en haute mer, savoir-faire de navigation transmis de père en fils.\n\nChez les Kroumen, cette tradition maritime a fait la réputation des marins de la côte, longtemps recrutés sur les navires de commerce le long du golfe de Guinée.",
                'origine_historique' => 'Pratique ancienne des peuples du littoral, toujours vivante dans les villages de pêcheurs malgré la motorisation des embarcations.',
                'niveau_risque' => 'vulnerable',
                'practical_info' => [['icon' => 'fas fa-map-pin', 'label' => 'Où voir', 'value' => 'Jacqueville, Grand-Lahou, Sassandra, Tabou']],
                'is_active' => true, 'sort_order' => 201,
                'sources' => [S::wiki('Kroumen'), S::wiki('Néyo'), S::WIKI_DEMOGRAPHIE],
            ],
            [
                'slug' => 'initiation-djoro-lobi',
                'domain_id' => $d['rites-de-passage'] ?? null,
                'people_roles' => $roles(['lobi', 'birifor']),
                'name' => 'Le Djoro, initiation lobi',
                'short_description' => 'Grande initiation des Lobi et des Birifor, organisée à intervalles de plusieurs années, qui fait entrer les jeunes dans la communauté.',
                'description' => "Le Djoro (ou joro) est l'initiation majeure des Lobi et des peuples apparentés comme les Birifor. Organisée à intervalles de plusieurs années, elle conduit les jeunes gens et jeunes filles à travers une série d'épreuves et d'enseignements secrets, jusqu'à leur renaissance symbolique comme membres à part entière de la communauté.\n\nL'initiation, très encadrée, fait l'objet d'un strict secret : seuls ses aspects publics (départ et retour des initiés) peuvent être vus par les non-initiés.",
                'origine_historique' => 'Rite fondateur des sociétés lobi du Bounkani et des régions voisines du Burkina Faso et du Ghana.',
                'niveau_risque' => 'vulnerable',
                'practical_info' => [['icon' => 'fas fa-triangle-exclamation', 'label' => 'Respect', 'value' => 'Rite secret : ne pas photographier sans autorisation']],
                'is_active' => true, 'sort_order' => 202,
                'sources' => [S::wiki('Lobi_(peuple)', 'Lobi (peuple)'), S::WIKI_DEMOGRAPHIE],
            ],
            [
                'slug' => 'masques-bedu',
                'domain_id' => $d['danse-des-masques'] ?? null,
                'people_roles' => $roles(['nafana', 'koulango']),
                'name' => 'Les masques Bedu',
                'short_description' => 'Grands masques-planches de la région de Bondoukou, dansés en couple (mâle à cornes, femelle à disque) lors des fêtes de fin d\'année et des funérailles.',
                'description' => "Les masques Bedu comptent parmi les plus grands masques d'Afrique : de hautes planches sculptées et peintes, évoquant un animal mythique proche du buffle. Ils dansent en couple : le masque mâle porte des cornes, le masque femelle un grand disque.\n\nLeurs sorties accompagnent la grande fête de fin d'année, qui dure plusieurs semaines, ainsi que les récoltes et les funérailles. Les danses doivent purifier la communauté, éloigner la maladie et favoriser la fécondité et la bonne entente.",
                'origine_historique' => "Le culte Bedu serait apparu vers 1930 dans le village nafana d'Oulike, près de Bondoukou, en remplacement d'un culte masqué plus ancien interdit par l'administration coloniale. Il s'est ensuite diffusé chez les Koulango et d'autres peuples voisins.",
                'niveau_risque' => 'vulnerable',
                'practical_info' => [['icon' => 'fas fa-map-pin', 'label' => 'Région', 'value' => 'Bondoukou (Gontougo), nord-est']],
                'is_active' => true, 'sort_order' => 203,
                'sources' => [
                    ['label' => 'K. Arnaut — étude sur les masques Bedu de la région de Bondoukou (KU Leuven)', 'url' => 'https://lirias.kuleuven.be/retrieve/255283'],
                    ['label' => 'R. A. Bravmann — Open Frontiers: The Mobility of Art in Black Africa (University of Washington Press, 1973)', 'url' => null],
                ],
            ],
            [
                'slug' => 'yabreu-fete-ignames-toura',
                'domain_id' => $d['fetes-traditionnelles'] ?? null,
                'people_roles' => $roles(['toura']),
                'name' => 'Le Yabreu, fête des ignames toura',
                'short_description' => 'Fête annuelle des ignames nouvelles en pays toura, où sont célébrés les masques (gôh-oh), gardiens de l\'équilibre du village.',
                'description' => "Chaque année, pendant la saison des pluies, tous les villages toura célèbrent le Yabreu, la fête des ignames nouvelles. Au-delà de la récolte, ce sont les masques, appelés gôh-oh, qui sont honorés : considérés comme des intermédiaires entre le Créateur et les hommes, ils veillent à l'équilibre social et cosmique du village.\n\nLa fête rassemble les descendants de chaque village, venus parfois de loin, pour plusieurs jours de cérémonies, de danses et de retrouvailles.",
                'origine_historique' => "Les Toura, qui se nomment Win-minh, appartiennent au groupe dan de la région des Montagnes ; ils vivent dans la partie la plus montagneuse de la région, entre Man, Touba et Séguéla.",
                'niveau_risque' => 'vulnerable',
                'meilleure_periode' => ['Juillet', 'Août', 'Septembre'],
                'practical_info' => [['icon' => 'fas fa-map-pin', 'label' => 'Région', 'value' => 'Biankouma et villages toura (Tonkpi)']],
                'is_active' => true, 'sort_order' => 204,
                'sources' => [['label' => 'Abidjan.net (Le Mandat, 16 mars 2010) — « La fête des ignames chez les Toura, fidélité à une tradition »', 'url' => 'https://news.abidjan.net/articles/359376/dossier-la-fete-des-ignames-chez-les-toura-fidelite-a-une-tradition']],
            ],
        ];
        $shared = array_values(array_filter($shared, fn ($e) => $e['domain_id']));
        $this->report('Éléments partagés', $this->seedMissing(CulturalElement::class, $shared));

        // ── Rattachement de peuples à des éléments existants (ajout seulement) ──
        $links = [
            'attieke-national' => ['ebrie-tchaman', 'alladian', 'avikam', 'abbey-abe'],      // savoir-faire des peuples lagunaires (UNESCO 2024)
            'fete-abissa-adjoukrou' => ['nzima-appolo'],                                       // l'Abissa est la fête des N'Zima de Grand-Bassam
            'confrérie-donzo-malinke' => ['koyaka', 'mahou'],                                  // confrérie de chasseurs du monde mandé
            'tissage-pagne-cote-ivoire' => ['dioula-mandinka', 'malinke'],                     // traditions de tissage mandé
        ];
        $linked = 0;
        foreach ($links as $elementSlug => $slugs) {
            $element = CulturalElement::where('slug', $elementSlug)->first();
            if (! $element) {
                continue;
            }
            $current = collect($element->people_roles ?? []);
            $add = collect($roles($slugs, 'peuple porteur'))->reject(fn ($r) => $current->contains('people_id', $r['people_id']));
            if ($add->isNotEmpty()) {
                $element->forceFill(['people_roles' => $current->concat($add)->values()->all()])->save();
                $linked += $add->count();
            }
        }
        $this->command?->info("✓ Rattachements ajoutés : {$linked}.");

        // Corrections de rattachement (Zaouli → Gouro, Abissa → N'Zima…) : réappliquées ici, une fois
        // tous les peuples créés (sur une installation neuve, CulturalElementSeeder passe avant eux).
        foreach (\Database\Seeders\Data\CulturalCorrections::elements() as $slug => $fix) {
            if (isset($fix['peoples']) && ($element = CulturalElement::withTrashed()->where('slug', $slug)->first())) {
                $element->forceFill(\Database\Seeders\Data\CulturalCorrections::apply($slug, []))->save();
            }
        }
    }
}
