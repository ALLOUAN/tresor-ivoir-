<?php

namespace Tests\Feature;

use App\Models\TouristCategory;
use App\Models\TouristCity;
use App\Models\TouristSite;
use Database\Seeders\TouristSiteByCitySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Seeder des sites par ville : sources présentes, relançable sans doublon, n'écrase jamais l'administration. */
class TouristSiteByCitySeederTest extends TestCase
{
    use RefreshDatabase;

    private const CITIES = ['abengourou', 'aboisso', 'agboville', 'bingerville', 'bouake', 'dabou', 'daloa', 'jacqueville', 'seguela', 'soubre', 'korhogo', 'abidjan', 'grand-bassam', 'assinie',
        'adzope', 'bongouanou', 'daoukro', 'dimbokro', 'divo', 'gagnoa', 'mankono', 'minignan', 'touba',
        'yamoussoukro', 'man', 'san-pedro', 'bondoukou', 'katiola', 'boundiali', 'sassandra', 'grand-lahou', 'ferkessedougou',
        'bouna', 'odienne', 'bouafle', 'duekoue', 'kong', 'toumodi', 'guiglo'];

    private const CATEGORIES = ['monuments-patrimoine', 'musees-galeries', 'ceremonies-rituels', 'sites-historiques', 'lacs-cours-eau', 'parcs-reserves-naturelles', 'architecture-sacree', 'villages-ethnies', 'plages-lagunes', 'cascades-montagnes', 'sites-culturels', 'artisanat-marches', 'sports-aventure', 'vie-nocturne-divertissement', 'gastronomie-maquis'];

    protected function setUp(): void
    {
        parent::setUp();
        foreach (self::CITIES as $slug) {
            TouristCity::create(['name' => ucfirst($slug), 'slug' => $slug, 'is_active' => true]);
        }
        foreach (self::CATEGORIES as $i => $slug) {
            TouristCategory::create(['name' => ucfirst($slug), 'slug' => $slug, 'is_active' => true, 'sort_order' => $i]);
        }
    }

    public function test_seeds_sourced_sites_for_each_city_and_is_idempotent(): void
    {
        $this->seed(TouristSiteByCitySeeder::class);
        $count = TouristSite::count();

        $this->assertSame(73, $count);
        TouristSite::all()->each(function (TouristSite $site) {
            $this->assertNotEmpty($site->sources, "Sources manquantes : {$site->name}");
            foreach ($site->sources as $source) {
                $this->assertStringStartsWith('https://', $source['url'], "Source sans lien : {$site->name}");
            }
            $this->assertNull($site->entrance_fee, 'Aucun tarif inventé');
        });
        // Chaque ville ciblée reçoit au moins un site.
        foreach (self::CITIES as $slug) {
            $this->assertTrue(TouristSite::whereHas('city', fn ($q) => $q->where('slug', $slug))->exists(), "Aucun site pour {$slug}");
        }

        $this->seed(TouristSiteByCitySeeder::class);
        $this->assertSame($count, TouristSite::count(), 'Relancer le seeder ne crée pas de doublon');
    }

    public function test_never_overwrites_a_site_edited_in_the_admin(): void
    {
        $this->seed(TouristSiteByCitySeeder::class);
        $site = TouristSite::where('slug', 'chutes-nawa-soubre')->firstOrFail();
        $site->update(['short_description' => 'Texte revu par l\'administration', 'is_active' => false]);

        $this->seed(TouristSiteByCitySeeder::class);
        $site->refresh();
        $this->assertSame('Texte revu par l\'administration', $site->short_description);
        $this->assertFalse($site->is_active);
    }
}
