<?php

namespace Tests\Feature;

use App\Models\CulturalElement;
use App\Models\CulturalPeople;
use App\Models\TouristCity;
use App\Models\TouristSite;
use Database\Seeders\CulturalDomainSeeder;
use Database\Seeders\IvorianReferenceSeeder;
use Database\Seeders\TouristCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Le référentiel ivoirien se charge, porte ses sources et n'écrase jamais l'existant. */
class IvorianReferenceSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_reference_data_is_seeded_with_sources_and_is_idempotent(): void
    {
        $this->seed([TouristCategorySeeder::class, CulturalDomainSeeder::class]);

        // Une ville déjà modifiée dans l'administration ne doit pas être écrasée.
        TouristCity::create(['name' => 'Odienné', 'slug' => 'odienne', 'description' => 'Texte saisi par l\'admin', 'is_active' => true]);

        $this->seed(IvorianReferenceSeeder::class);

        // Les 31 régions officielles (décrets 2011-263 et 2012-612) ont chacune leur ville de référence.
        $regions = ['Agnéby-Tiassa', 'Bafing', 'Bagoué', 'Bélier', 'Béré', 'Bounkani', 'Cavally', 'Folon', 'Gbêkê', 'Gbôklé', 'Gôh', 'Gontougo', 'Grands-Ponts', 'Guémon', 'Hambol', 'Haut-Sassandra', 'Iffou', 'Indénié-Djuablin', 'Kabadougou', 'La Mé', 'Lôh-Djiboua', 'Marahoué', 'Moronou', 'Nawa', "N'Zi", 'Poro', 'San-Pédro', 'Sud-Comoé', 'Tchologo', 'Tonkpi', 'Worodougou'];
        $this->assertCount(31, $regions);
        $this->assertSame([], array_values(array_diff($regions, TouristCity::pluck('region_administrative')->unique()->all())));
        $this->assertSame('Texte saisi par l\'admin', TouristCity::where('slug', 'odienne')->value('description'));
        $this->assertNotEmpty(TouristCity::where('slug', 'odienne')->first()->sources);

        $tai = TouristSite::where('slug', 'parc-national-tai')->firstOrFail();
        $this->assertSame('https://whc.unesco.org/fr/list/195', $tai->sources[0]['url']);
        // Les 8 mosquées du bien UNESCO : 6 fiches + Kong (grande et petite mosquée sur une même fiche).
        // Liste explicite : d'autres mosquées (hors bien UNESCO, ex. Ganhoué à Touba) ont aussi un slug « mosquee-… ».
        $this->assertSame(6, TouristSite::whereIn('slug', ['mosquee-kaouara', 'mosquee-kouto', 'mosquee-tengrela', 'mosquee-samatiguila', 'mosquee-nambira', 'mosquee-sorobango'])->count());
        $this->assertTrue(TouristSite::where('slug', 'grande-mosquee-kong')->exists());

        $gouro = CulturalPeople::where('slug', 'gouro')->firstOrFail();
        $this->assertSame('Mandé du Sud', $gouro->famille_linguistique);
        $this->assertNotEmpty($gouro->sources);

        $gbofe = CulturalElement::where('slug', 'gbofe-afounkaha')->firstOrFail();
        $this->assertSame([CulturalPeople::where('slug', 'tagbana')->value('id')], array_column($gbofe->people_roles, 'people_id'));

        // Deuxième passage : aucun doublon.
        $counts = [TouristCity::count(), TouristSite::count(), CulturalPeople::count(), CulturalElement::count()];
        $this->seed(IvorianReferenceSeeder::class);
        $this->assertSame($counts, [TouristCity::count(), TouristSite::count(), CulturalPeople::count(), CulturalElement::count()]);

        // Les sources s'affichent sur les fiches publiques.
        $this->get(route('tourist.site', $tai->slug))->assertOk()->assertSee('whc.unesco.org/fr/list/195', false);
        $this->get(route('cultural.people', 'gouro'))->assertOk()->assertSee('Sources');
    }
}
