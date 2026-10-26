<?php

namespace Tests\Feature;

use App\Models\Provider;
use App\Models\ProviderCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Menu prestataire organisé par tâche : groupes attendus, offre du secteur, aucun lien en double. */
class ProviderMenuTest extends TestCase
{
    use RefreshDatabase;

    private function sidebarFor(string $categorySlug): string
    {
        $owner = User::factory()->provider()->create();
        $cat = ProviderCategory::create(['slug' => $categorySlug, 'name_fr' => ucfirst($categorySlug), 'name_en' => ucfirst($categorySlug), 'is_active' => true]);
        Provider::create(['user_id' => $owner->id, 'category_id' => $cat->id, 'name' => 'Établissement test', 'slug' => 'etab-'.$categorySlug, 'status' => 'active']);

        $html = $this->actingAs($owner)->get(route('provider.dashboard'))->assertOk()->getContent();
        preg_match('~<aside id="sidebar".*?</aside>~s', $html, $m);

        return $m[0] ?? '';
    }

    public function test_hotel_menu_is_grouped_by_task_without_duplicates(): void
    {
        $aside = $this->sidebarFor('hotels');

        foreach (['Pilotage', 'Réservations', 'Mon hébergement', 'Clients', 'Visibilité &amp; ventes', 'Finances', 'Compte'] as $group) {
            $this->assertStringContainsString('>'.$group.'</p>', $aside, "Groupe manquant : $group");
        }
        $this->assertStringContainsString(route('provider.accommodation.rooms.index'), $aside);
        $this->assertStringContainsString(route('provider.analytics'), $aside);
        $this->assertStringContainsString(route('provider.notifications.index'), $aside);

        preg_match_all('~href="([^"]+/provider/[^"]+)"~', $aside, $links);
        $this->assertSame(array_unique($links[1]), $links[1], 'Un lien du menu apparaît deux fois');
        $this->assertSame(1, substr_count($aside, 'href="'.route('provider.wallet.index').'"'));
    }

    public function test_tourist_site_menu_exposes_visit_types_and_slots(): void
    {
        $aside = $this->sidebarFor('sites-touristiques');

        $this->assertStringContainsString(route('provider.tourist-experience.visit-types.index'), $aside);
        $this->assertStringContainsString(route('provider.tourist-experience.sessions.index'), $aside);
        $this->assertStringContainsString(route('provider.tourist-visits.index'), $aside);
        $this->assertStringNotContainsString(route('provider.accommodation.rooms.index'), $aside);
    }
}
