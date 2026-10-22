<?php

namespace Tests\Feature;

use App\Models\Accommodation;
use App\Models\TouristCity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Les fiches détaillées ont un aperçu de partage et figurent dans le sitemap. */
class ShareMetaAndSitemapTest extends TestCase
{
    use RefreshDatabase;

    public function test_accommodation_page_has_share_meta_and_is_listed_in_the_sitemap(): void
    {
        $city = TouristCity::create(['name' => 'Grand-Bassam', 'slug' => 'grand-bassam', 'is_active' => true]);
        $a = Accommodation::create([
            'city_id' => $city->id, 'name' => 'Villa Lagune', 'slug' => 'villa-lagune', 'type' => 'villa', 'is_active' => true,
            'short_description' => 'Une villa les pieds dans l\'eau.', 'room_types' => [],
        ]);
        Accommodation::create(['city_id' => $city->id, 'name' => 'Fermé', 'slug' => 'hotel-ferme', 'type' => 'hotel', 'is_active' => false]);

        $this->get(route('accommodations.show', $a))
            ->assertOk()
            ->assertSee('<meta property="og:title" content="Villa Lagune">', false)
            ->assertSee('<meta property="og:description" content="Une villa les pieds dans l&#039;eau.">', false)
            ->assertSee('<link rel="canonical" href="'.route('accommodations.show', $a).'">', false);

        $this->get(route('tourist.city', $city->slug))->assertOk()->assertSee('og:title', false);

        $this->get(route('sitemap'))
            ->assertOk()
            ->assertSee(route('accommodations.show', 'villa-lagune'), false)
            ->assertSee(route('tourist.city', 'grand-bassam'), false)
            ->assertDontSee('hotel-ferme', false);
    }
}
