<?php

namespace Database\Seeders;

use App\Models\TouristCity;
use Database\Seeders\Concerns\SeedsMissingReferenceData;
use Database\Seeders\Data\IvorianSources as S;
use Illuminate\Database\Seeder;

/**
 * Régions touristiques : une ville de référence (le chef-lieu) pour chacune des 31 régions
 * administratives, plus quelques villes à fort intérêt touristique (Bingerville, Kong…).
 *
 * Découpage : décret n° 2011-263 du 28 septembre 2011 (12 districts, 2 districts autonomes)
 * et décret n° 2012-612 du 4 juillet 2012 (31e région : Moronou).
 * Les coordonnées sont celles, approximatives, du centre-ville. Aucune photo n'est fournie :
 * elles s'ajoutent depuis l'administration (Tourisme › Villes).
 *
 * N'écrase jamais une ville existante (Abidjan, Bouaké, Man… restent tels quels).
 */
class TouristRegionSeeder extends Seeder
{
    use SeedsMissingReferenceData;

    public function run(): void
    {
        $base = [S::DECRET_REGIONS, S::WIKI_REGIONS];

        $cities = [
            // ── District des Lacs ────────────────────────────────────────────
            $this->city('Dimbokro', 'Lacs', "N'Zi", 6.65, -4.71, "Chef-lieu du district des Lacs et de la région du N'Zi, Dimbokro est une ville baoulé du centre-est, sur les rives du fleuve N'Zi.", $base),
            $this->city('Daoukro', 'Lacs', 'Iffou', 7.06, -3.96, "Chef-lieu de la région de l'Iffou, en pays akan, Daoukro est une étape entre le centre et l'est du pays.", $base),
            $this->city('Toumodi', 'Lacs', 'Bélier', 6.56, -5.02, "Chef-lieu de la région du Bélier, Toumodi se trouve sur l'axe Abidjan–Yamoussoukro, en pays baoulé, à proximité de la réserve d'Abokouamékro.", $base),
            $this->city('Bongouanou', 'Lacs', 'Moronou', 6.65, -4.20, "Chef-lieu du Moronou, 31e région créée en 2012, Bongouanou est le cœur du pays agni-moronou, terre de cacao et de traditions royales.", [...$base, S::DECRET_MORONOU]),

            // ── District du Comoé ───────────────────────────────────────────
            $this->city('Abengourou', 'Comoé', 'Indénié-Djuablin', 6.73, -3.49, "Capitale du royaume agni de l'Indénié, Abengourou est le chef-lieu du district du Comoé. La cour royale y perpétue les traditions agni.", [...$base, S::wiki('Agnis')]),
            $this->city('Aboisso', 'Comoé', 'Sud-Comoé', 5.47, -3.21, "Chef-lieu du Sud-Comoé, Aboisso est proche de Krindjabo, capitale historique du royaume agni du Sanwi, et des lagunes d'Assinie.", $base),

            // ── District du Denguélé ────────────────────────────────────────
            $this->city('Odienné', 'Denguélé', 'Kabadougou', 9.51, -7.56, "Chef-lieu du Denguélé, Odienné est la grande ville malinké du nord-ouest. La région abrite la mosquée de Samatiguila, inscrite au patrimoine mondial.", [...$base, S::whc(1648, 'Mosquées de style soudanais du Nord ivoirien')]),
            $this->city('Minignan', 'Denguélé', 'Folon', 9.99, -7.84, "Chef-lieu de la région du Folon, à l'extrême nord-ouest, à proximité des frontières du Mali et de la Guinée.", $base),

            // ── Chefs-lieux déjà présents dans le référentiel de base (créés s'ils manquent) ──
            $this->city('Bouaké', 'Vallée du Bandama', 'Gbêkê', 7.69, -5.03, "Chef-lieu du district de la Vallée du Bandama, Bouaké est la deuxième ville du pays et le grand carrefour du pays baoulé.", [...$base, S::wiki('Baoulés')]),
            $this->city('Korhogo', 'Savanes', 'Poro', 9.46, -5.63, "Chef-lieu du district des Savanes, Korhogo est la capitale du pays sénoufo, réputée pour ses toiles peintes et ses villages d'artisans.", [...$base, S::wiki('Sénoufos')]),
            $this->city('Man', 'Montagnes', 'Tonkpi', 7.41, -7.55, "Chef-lieu du district des Montagnes, Man est la ville des dix-huit montagnes, en pays dan, entre cascades et forêts d'altitude.", [...$base, S::wiki('Dan_(peuple)', 'Dan (peuple)')]),
            $this->city('San-Pédro', 'Bas-Sassandra', 'San-Pédro', 4.75, -6.64, "Chef-lieu du district du Bas-Sassandra, San-Pédro est le deuxième port du pays et la porte d'entrée des plages du sud-ouest.", [...$base, S::wiki('San-Pédro')]),

            // ── District du Gôh-Djiboua ─────────────────────────────────────
            $this->city('Gagnoa', 'Gôh-Djiboua', 'Gôh', 6.13, -5.95, "Chef-lieu du district du Gôh-Djiboua, Gagnoa est la grande ville du pays bété, au cœur de la boucle du cacao.", [...$base, S::wiki('Bétés')]),
            $this->city('Divo', 'Gôh-Djiboua', 'Lôh-Djiboua', 5.84, -5.36, "Chef-lieu du Lôh-Djiboua, Divo est la principale ville du pays dida, réputé pour ses pagnes en raphia.", [...$base, S::wiki('Didas')]),

            // ── District des Lagunes ────────────────────────────────────────
            $this->city('Dabou', 'Lagunes', 'Grands-Ponts', 5.32, -4.38, "Chef-lieu du district des Lagunes et des Grands-Ponts, Dabou est la ville des Adjoukrou, sur la lagune Ébrié, à l'ouest d'Abidjan.", $base),
            $this->city('Agboville', 'Lagunes', 'Agnéby-Tiassa', 5.93, -4.22, "Chef-lieu de l'Agnéby-Tiassa, Agboville est le centre du pays abbey, dans une région de forêt et de plantations.", [...$base, S::wiki('Abés')]),
            $this->city('Adzopé', 'Lagunes', 'La Mé', 6.11, -3.86, "Chef-lieu de la région de La Mé, Adzopé est la principale ville du pays attié (akyé).", [...$base, S::wiki('Attié_(peuple)', 'Attié (peuple)')]),

            // ── District des Montagnes ──────────────────────────────────────
            $this->city('Guiglo', 'Montagnes', 'Cavally', 6.54, -7.49, "Chef-lieu du Cavally, Guiglo est la porte d'entrée du Parc national de Taï, forêt primaire inscrite au patrimoine mondial.", [...$base, S::whc(195, 'Parc national de Taï')]),
            $this->city('Duékoué', 'Montagnes', 'Guémon', 6.74, -7.35, "Chef-lieu du Guémon, Duékoué se situe au carrefour de l'ouest montagneux, non loin du Parc national du Mont Péko.", $base),
            $this->city('Danané', 'Montagnes', 'Tonkpi', 7.26, -8.15, "Ville du pays dan (yacouba) proche des frontières guinéenne et libérienne, Danané est connue pour les ponts de lianes des villages voisins et la proximité du mont Nimba.", [...$base, S::wiki('Dan_(peuple)', 'Dan (peuple)')]),

            // ── District du Sassandra-Marahoué ──────────────────────────────
            $this->city('Daloa', 'Sassandra-Marahoué', 'Haut-Sassandra', 6.88, -6.45, "Chef-lieu du district du Sassandra-Marahoué, Daloa est une grande ville du centre-ouest, carrefour du pays bété et de la boucle du cacao.", $base),
            $this->city('Bouaflé', 'Sassandra-Marahoué', 'Marahoué', 6.99, -5.74, "Chef-lieu de la Marahoué, Bouaflé est au cœur du pays gouro, berceau du Zaouli, et à proximité du Parc national de la Marahoué.", [...$base, S::wiki('Gouro_(peuple)', 'Gouro (peuple)')]),

            // ── District des Savanes ────────────────────────────────────────
            $this->city('Ferkessédougou', 'Savanes', 'Tchologo', 9.59, -5.20, "Chef-lieu du Tchologo, Ferkessédougou est une ville sénoufo du nord, entourée de plantations de canne à sucre. La région compte plusieurs mosquées soudanaises classées.", [...$base, S::whc(1648, 'Mosquées de style soudanais du Nord ivoirien')]),
            $this->city('Boundiali', 'Savanes', 'Bagoué', 9.52, -6.49, "Chef-lieu de la Bagoué, Boundiali est proche des mosquées soudanaises de Kouto et de Tengréla, inscrites au patrimoine mondial.", [...$base, S::whc(1648, 'Mosquées de style soudanais du Nord ivoirien')]),
            $this->city('Kong', 'Savanes', 'Tchologo', 9.15, -4.61, "Ancienne capitale de l'empire de Kong, grand centre commercial et religieux dioula, Kong conserve deux mosquées de style soudanais inscrites au patrimoine mondial.", [...$base, S::whc(1648, 'Mosquées de style soudanais du Nord ivoirien')]),

            // ── District du Bas-Sassandra ───────────────────────────────────
            $this->city('Sassandra', 'Bas-Sassandra', 'Gbôklé', 4.95, -6.09, "Ancien port colonial à l'embouchure du fleuve Sassandra, la ville est entourée de plages et de villages de pêcheurs.", $base),
            $this->city('Soubré', 'Bas-Sassandra', 'Nawa', 5.79, -6.61, "Chef-lieu de la Nawa, Soubré est la capitale du cacao ivoirien, sur le fleuve Sassandra, connue pour les chutes de Naoua et son barrage hydroélectrique.", $base),

            // ── District de la Vallée du Bandama ────────────────────────────
            $this->city('Katiola', 'Vallée du Bandama', 'Hambol', 8.14, -5.10, "Chef-lieu du Hambol, Katiola est réputée pour ses poteries traditionnelles. Le village voisin d'Afounkaha est le berceau du Gbofe, inscrit par l'UNESCO.", [...$base, S::ich('Le Gbofe d\'Afounkaha', 'le-gbofe-d-afounkaha-la-musique-des-trompes-traversieres-de-la-communaute-tagbana-00005')]),

            // ── District du Woroba ──────────────────────────────────────────
            $this->city('Séguéla', 'Woroba', 'Worodougou', 7.96, -6.67, "Chef-lieu du district du Woroba, Séguéla est une ville malinké du centre-nord, dans une région de savane et de diamants.", $base),
            $this->city('Mankono', 'Woroba', 'Béré', 8.06, -6.19, "Chef-lieu du Béré, Mankono est la ville des Koyaka, peuple mandé du centre-nord.", [...$base, S::wiki('Koyaka_(peuple)', 'Koyaka (peuple)')]),
            $this->city('Touba', 'Woroba', 'Bafing', 8.28, -7.68, "Chef-lieu du Bafing, Touba est la ville des Mahou, au pied des reliefs de l'ouest, non loin du Parc national du Mont Sangbé.", $base),

            // ── District du Zanzan ──────────────────────────────────────────
            $this->city('Bondoukou', 'Zanzan', 'Gontougo', 8.04, -2.80, "Chef-lieu du Zanzan, Bondoukou est surnommée « la ville aux mille mosquées ». Cité commerçante ancienne, elle est le siège du royaume abron du Gyaman.", [...$base, S::wiki('Abron_(peuple)', 'Abron (peuple)')]),
            $this->city('Bouna', 'Zanzan', 'Bounkani', 9.27, -3.00, "Chef-lieu du Bounkani, Bouna est la ville des Koulango et la porte d'entrée du Parc national de la Comoé, inscrit au patrimoine mondial.", [...$base, S::whc(227, 'Parc national de la Comoé'), S::wiki('Koulangos')]),

            // ── Villes touristiques hors chefs-lieux ────────────────────────
            $this->city('Bingerville', 'Lagunes', 'District autonome d\x27Abidjan', 5.36, -3.89, "Capitale de la colonie de 1900 à 1934, Bingerville conserve l'ancien palais du gouverneur, son jardin botanique et le musée Charles Combes.", [S::wiki('Bingerville')], 'District Autonome d\'Abidjan'),
            $this->city('Jacqueville', 'Lagunes', 'Grands-Ponts', 5.21, -4.42, "Ville du pays alladian, sur le cordon littoral entre lagune Ébrié et océan, appréciée pour ses plages et ses anciennes maisons coloniales.", $base),
            $this->city('Grand-Lahou', 'Lagunes', 'Grands-Ponts', 5.14, -5.02, "Ville du pays avikam à l'embouchure du Bandama, Grand-Lahou est voisine du Parc national d'Azagny.", $base),
        ];

        $this->report('Régions touristiques (villes)', $this->seedMissing(TouristCity::class, $cities, ['district', 'region_administrative', 'latitude', 'longitude']));
    }

    /** @param list<array{label:string,url:?string}> $sources */
    private function city(string $name, string $district, string $region, float $lat, float $lng, string $description, array $sources, ?string $districtLabel = null): array
    {
        static $order = 100;

        return [
            'name' => $name,
            'slug' => \Illuminate\Support\Str::slug($name),
            'district' => $districtLabel ?? 'District '.match (true) {
                in_array($district, ['Lacs', 'Montagnes', 'Savanes', 'Lagunes'], true) => 'des ',
                $district === 'Vallée du Bandama' => 'de la ',
                default => 'du ',
            }.$district,
            'region_administrative' => $region,
            'description' => $description,
            'latitude' => $lat,
            'longitude' => $lng,
            'is_featured' => false,
            'is_active' => true,
            'sort_order' => $order++,
            'sources' => array_values(array_unique($sources, SORT_REGULAR)),
        ];
    }
}
