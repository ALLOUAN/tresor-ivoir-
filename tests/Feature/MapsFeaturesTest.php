<?php

namespace Tests\Feature;

use App\Models\Accommodation;
use App\Models\Provider;
use App\Models\ProviderCategory;
use App\Models\ProviderHour;
use App\Models\Restaurant;
use App\Models\TouristCity;
use App\Models\TouristSite;
use App\Models\TripPlan;
use App\Services\Ai\Catalog;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

/** Google Maps : « Autour de moi », trajet optimisé d'une journée, assistant ancré sur Google Maps. */
class MapsFeaturesTest extends TestCase
{
    use RefreshDatabase;

    private TouristCity $city;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->city = TouristCity::create(['name' => 'Abidjan', 'slug' => 'abidjan', 'is_active' => true, 'latitude' => 5.345, 'longitude' => -4.024]);
    }

    private function seedPlaces(): void
    {
        $category = ProviderCategory::create(['slug' => 'resto-'.uniqid(), 'name_fr' => 'Restaurants', 'name_en' => 'Restaurants', 'is_active' => true]);
        $provider = Provider::create(['user_id' => \App\Models\User::factory()->provider()->create()->id, 'category_id' => $category->id, 'name' => 'Maquis du Plateau', 'slug' => 'maquis-plateau', 'status' => 'active']);
        Restaurant::create(['city_id' => $this->city->id, 'provider_id' => $provider->id, 'name' => 'Maquis du Plateau', 'slug' => 'maquis-du-plateau', 'is_active' => true, 'latitude' => 5.3250, 'longitude' => -4.0200]);
        foreach (range(0, 6) as $dow) {
            ProviderHour::create(['provider_id' => $provider->id, 'day_of_week' => $dow, 'open_time' => '11:00', 'close_time' => '23:00', 'is_closed' => false]);
        }
        $siteCat = \App\Models\TouristCategory::create(['name' => 'Musées', 'slug' => 'musees-'.uniqid()]);
        TouristSite::create(['city_id' => $this->city->id, 'category_id' => $siteCat->id, 'name' => 'Musée des Civilisations', 'slug' => 'musee-civilisations', 'is_active' => true,
            'latitude' => 5.3300, 'longitude' => -4.0180,
            'schedules' => array_map(fn ($d) => ['day' => $d, 'opens' => '09:00', 'closes' => '17:00', 'closed' => false], ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'])]);
        Accommodation::create(['city_id' => $this->city->id, 'name' => 'Hôtel Lagune', 'slug' => 'hotel-lagune', 'type' => 'hotel', 'is_active' => true, 'latitude' => 5.3400, 'longitude' => -4.0100]);
        // Sans coordonnées propres : jamais proposé dans « Autour de moi » (distance trompeuse).
        Accommodation::create(['city_id' => $this->city->id, 'name' => 'Résidence Sans GPS', 'slug' => 'residence-sans-gps', 'type' => 'residence', 'is_active' => true]);
        Catalog::forget();
    }

    private function nearby(array $q = [])
    {
        return $this->getJson(route('nearby.places', $q + ['lat' => 5.33, 'lng' => -4.02, 'radius' => 5, 'filter' => 'all', 'open_now' => 0]))->assertOk()->json();
    }

    public function test_nearby_ranks_open_places_first_and_respects_opening_hours(): void
    {
        $this->seedPlaces();
        Carbon::setTestNow(Carbon::parse('2026-10-14 20:00', 'Africa/Abidjan'));

        $data = $this->nearby();
        $byTitle = collect($data['places'])->keyBy('title');
        $this->assertSame('open', $byTitle['Maquis du Plateau']['open']);
        $this->assertSame('23:00', $byTitle['Maquis du Plateau']['until']);
        $this->assertSame('closed', $byTitle['Musée des Civilisations']['open'], 'Le musée ferme à 17 h.');
        $this->assertSame('open', $byTitle['Hôtel Lagune']['open']);
        $this->assertArrayNotHasKey('Résidence Sans GPS', $byTitle->all());
        $this->assertSame('closed', end($data['places'])['open'], 'Les lieux fermés passent en dernier.');

        $openOnly = collect($this->nearby(['open_now' => 1])['places'])->pluck('title');
        $this->assertNotContains('Musée des Civilisations', $openOnly);

        $eat = collect($this->nearby(['filter' => 'eat'])['places'])->pluck('title')->all();
        $this->assertSame(['Maquis du Plateau'], $eat);

        Carbon::setTestNow(Carbon::parse('2026-10-14 08:00', 'Africa/Abidjan'));
        Cache::flush();
        $musee = collect($this->nearby()['places'])->firstWhere('title', 'Musée des Civilisations');
        $this->assertSame('closed', $musee['open']);
        $this->assertSame('09:00', $musee['until'], 'Fermé pour l\'instant : indique l\'heure d\'ouverture.');

        $this->get(route('nearby.index'))->assertOk()->assertSee('Autour de moi, maintenant');
    }

    public function test_nearby_respects_the_radius(): void
    {
        $this->seedPlaces();
        $far = $this->nearby(['lat' => 6.82, 'lng' => -5.28, 'radius' => 5]); // Yamoussoukro
        $this->assertSame(0, $far['count']);
    }

    private function trip(): TripPlan
    {
        $stop = fn ($title, $lat, $lng) => ['slot' => 'Matin', 'kind' => 'activity', 'title' => $title, 'item' => ['title' => $title, 'url' => '/x', 'lat' => $lat, 'lng' => $lng]];

        return TripPlan::create(['uuid' => (string) Str::uuid(), 'params' => [], 'engine' => 'template', 'plan' => ['days' => [
            ['index' => 1, 'date' => '2026-10-20', 'city' => 'Abidjan', 'km' => 10, 'cost' => 0, 'transfer' => null,
                'lodging' => ['title' => 'Hôtel Lagune', 'item' => ['title' => 'Hôtel Lagune', 'lat' => 5.34, 'lng' => -4.01]],
                'stops' => [$stop('Musée', 5.33, -4.018), $stop('Maquis', 5.325, -4.02)]],
        ]]]);
    }

    public function test_day_route_uses_google_optimised_order_and_real_times(): void
    {
        config(['services.google_maps.server_key' => 'k']);
        Http::fake(['routes.googleapis.com/*' => Http::response(['routes' => [[
            'distanceMeters' => 9000, 'duration' => '1500s', 'polyline' => ['encodedPolyline' => 'abc'],
            'optimizedIntermediateWaypointIndex' => [1, 0],
            'legs' => [['distanceMeters' => 2000, 'duration' => '300s'], ['distanceMeters' => 3000, 'duration' => '600s'], ['distanceMeters' => 4000, 'duration' => '600s']],
        ]]])]);
        $trip = $this->trip();

        $data = $this->getJson(route('trip.day-route', [$trip->uuid, 1]))->assertOk()->json();

        $this->assertSame('google', $data['engine']);
        $this->assertSame(['Maquis', 'Musée'], array_column($data['stops'], 'title'));
        $this->assertTrue($data['reordered']);
        $this->assertSame(['km' => 9, 'minutes' => 25], ['km' => (int) $data['total']['km'], 'minutes' => $data['total']['minutes']]);
        $this->assertSame('Hôtel Lagune', $data['home']['title']);
        $this->assertStringContainsString('google.com/maps/dir', $data['maps_url']);
        Http::assertSent(fn ($r) => $r['optimizeWaypointOrder'] === true && count($r['intermediates']) === 2);

        // Second affichage : servi par le cache, aucun nouvel appel facturé.
        $this->getJson(route('trip.day-route', [$trip->uuid, 1]))->assertOk();
        Http::assertSentCount(1);
    }

    public function test_day_route_falls_back_to_estimates_without_google(): void
    {
        config(['services.google_maps.server_key' => null]);
        Http::fake();
        $data = $this->getJson(route('trip.day-route', [$this->trip()->uuid, 1]))->assertOk()->json();

        $this->assertSame('estimate', $data['engine']);
        $this->assertSame(['Musée', 'Maquis'], array_column($data['stops'], 'title'));
        $this->assertCount(3, $data['legs']);
        Http::assertNothingSent();
    }

    public function test_assistant_falls_back_to_google_maps_only_when_the_site_has_no_answer(): void
    {
        config(['ai.maps_grounding' => ['enabled' => true, 'api_key' => 'g', 'model' => 'gemini-2.5-flash']]);
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response(['candidates' => [[
            'content' => ['parts' => [['text' => "Essayez **Chez Tantie Rose**, à 5 minutes."]]],
            'groundingMetadata' => [
                'groundingChunks' => [['maps' => ['uri' => 'https://maps.google.com/?cid=1', 'title' => 'Chez Tantie Rose']]],
                'googleMapsWidgetContextToken' => 'widget-123',
            ],
        ]]])]);

        $r = $this->postJson(route('ai.chat.ask'), ['message' => 'Un bon garage pour réparer un pneu près du stade ?', 'lat' => 5.3, 'lng' => -4.0])->assertOk()->json();

        $this->assertSame('answered', $r['status']);
        $this->assertSame('gemini_maps', $r['engine']);
        $this->assertSame('Essayez Chez Tantie Rose, à 5 minutes.', $r['answer']);
        $this->assertSame('widget-123', $r['maps_widget']);
        $this->assertSame('Google Maps', $r['sources'][0]['type']);
        Http::assertSent(fn ($req) => isset($req['tools'][0]['googleMaps']) && $req['toolConfig']['retrievalConfig']['latLng']['latitude'] == 5.3);
    }

    public function test_assistant_without_google_maps_keeps_saying_it_does_not_know(): void
    {
        config(['ai.maps_grounding' => ['enabled' => false, 'api_key' => null, 'model' => 'x']]);
        Http::fake();

        $r = $this->postJson(route('ai.chat.ask'), ['message' => 'Un bon garage pour réparer un pneu près du stade ?'])->assertOk()->json();

        $this->assertSame('no_source', $r['status']);
        Http::assertNothingSent();
    }
}
