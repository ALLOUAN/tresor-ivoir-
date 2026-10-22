<?php

namespace Tests\Feature;

use App\Models\CulturalDomain;
use App\Models\CulturalElement;
use App\Models\CulturalPeople;
use Database\Seeders\CulturalDomainSeeder;
use Database\Seeders\CulturalElementSeeder;
use Database\Seeders\CulturalPeopleSeeder;
use Database\Seeders\IvorianReferenceSeeder;
use Database\Seeders\TouristCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Une installation neuve ne réintroduit pas les erreurs corrigées (UNESCO, peuples d'origine). */
class CulturalCorrectionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_fresh_seeding_produces_corrected_cultural_data(): void
    {
        $this->seed([TouristCategorySeeder::class, CulturalPeopleSeeder::class, CulturalDomainSeeder::class, CulturalElementSeeder::class, IvorianReferenceSeeder::class]);

        $people = fn (string $slug) => collect(CulturalElement::where('slug', $slug)->value('people_roles'))
            ->map(fn ($r) => CulturalPeople::find($r['people_id'])->slug)->sort()->values()->all();

        $this->assertSame(['gouro'], $people('danse-zaouli-baoule'));
        $this->assertSame(['nzima-appolo'], $people('fete-abissa-adjoukrou'));
        $this->assertSame(['abron-brong', 'agni-anyi', 'baoule'], $people('contes-araignee-dan'));
        $this->assertNotContains('dioula-mandinka', $people('attieke-national'));

        // Seuls les éléments réellement inscrits portent une mention UNESCO.
        $this->assertEqualsCanonicalizing(
            ['danse-zaouli-baoule', 'attieke-national', 'balafon-senoufo', 'gbofe-afounkaha', 'tissage-pagne-cote-ivoire'],
            CulturalElement::whereNotNull('unesco_status')->pluck('slug')->all()
        );
        $this->assertStringNotContainsString('UNESCO', CulturalPeople::where('slug', 'dan-yacouba')->value('description'));
        $this->assertSame('Dabou', CulturalPeople::where('slug', 'adjoukrou')->value('capitale_culturelle'));

        // Chaque élément actif a au moins une source.
        $this->assertSame([], CulturalElement::where('is_active', 1)->get()->filter(fn ($e) => empty($e->sources))->pluck('slug')->all());
    }
}
