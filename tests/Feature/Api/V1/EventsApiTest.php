<?php

namespace Tests\Feature\Api\V1;

use App\Models\Event;
use App\Models\EventCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Tests\TestCase;

/** API mobile — événements : mêmes règles que la page /evenements du site. */
class EventsApiTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function event(string $title, string $starts, ?string $ends = null, array $extra = []): Event
    {
        // Catégorie et auteur sont obligatoires en base.
        $category = EventCategory::firstOrCreate(['slug' => 'divers'], ['name_fr' => 'Divers', 'sort_order' => 99]);
        $author = \App\Models\User::firstOrCreate(['email' => 'redaction@exemple.ci'], [
            'first_name' => 'Rédaction', 'last_name' => 'TI', 'password_hash' => bcrypt('x'), 'role' => 'editor', 'is_active' => true,
        ]);

        return Event::create($extra + [
            'category_id' => $category->id, 'created_by' => $author->id,
            'title_fr' => $title, 'slug' => Str::slug($title), 'description_fr' => '<p>Texte</p>',
            'starts_at' => $starts, 'ends_at' => $ends, 'status' => 'published', 'city' => 'Abidjan',
            'is_free' => true, 'price' => 0, 'published_at' => now(), 'cover_url' => '/storage/events/cover.jpg',
        ]);
    }

    public function test_list_shows_upcoming_and_ongoing_with_pagination(): void
    {
        Carbon::setTestNow('2026-10-27 15:00:00');
        $this->event('Concert à venir', '2026-11-02 20:00:00');
        $this->event('Festival en cours', '2026-10-25 09:00:00', '2026-10-29 18:00:00');
        $this->event('Salon terminé', '2026-10-10 09:00:00', '2026-10-12 18:00:00');
        $this->event('Brouillon', '2026-11-05 10:00:00', null, ['status' => 'draft']);

        $res = $this->getJson('/api/v1/events')->assertOk()
            ->assertJsonStructure(['data' => [['id', 'slug', 'title', 'cover' => ['url'], 'starts_at', 'is_ongoing', 'web_url']], 'links', 'meta' => ['current_page', 'total']])
            ->assertJsonPath('meta.total', 2);

        $this->assertSame(['Festival en cours', 'Concert à venir'], array_column($res->json('data'), 'title'));
        $this->assertTrue($res->json('data.0.is_ongoing'));
        $this->assertStringStartsWith('http', $res->json('data.0.cover.url'));

        $this->getJson('/api/v1/events?periode=past')->assertOk()->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.title', 'Salon terminé');
    }

    public function test_filters_by_category_city_and_search(): void
    {
        Carbon::setTestNow('2026-10-27 15:00:00');
        $music = EventCategory::create(['slug' => 'musique', 'name_fr' => 'Musique', 'sort_order' => 1]);
        $this->event('Concert Zouglou', '2026-11-02 20:00:00', null, ['category_id' => $music->id]);
        $this->event('Fête des Ignames', '2026-11-08 09:00:00', null, ['city' => 'Bondoukou']);

        $this->getJson('/api/v1/events?categorie=musique')->assertJsonPath('meta.total', 1)->assertJsonPath('data.0.category.slug', 'musique');
        $this->getJson('/api/v1/events?ville=Bondoukou')->assertJsonPath('meta.total', 1);
        $this->getJson('/api/v1/events?q=Zouglou')->assertJsonPath('meta.total', 1);
        $this->getJson('/api/v1/events?periode=demain')->assertStatus(422);

        $this->getJson('/api/v1/event-categories')->assertOk()->assertJsonPath('data.0.events_count', 1);
    }

    public function test_detail_and_unknown_or_draft_event_return_404(): void
    {
        Carbon::setTestNow('2026-10-27 15:00:00');
        $this->event('Festival des Masques', '2026-11-10 09:00:00', '2026-11-12 20:00:00', ['organizer_name' => 'Ville de Man', 'latitude' => 7.41, 'longitude' => -7.55]);
        $this->event('Brouillon', '2026-11-05 10:00:00', null, ['status' => 'draft']);

        $this->getJson('/api/v1/events/festival-des-masques')->assertOk()
            ->assertJsonPath('data.organizer.name', 'Ville de Man')
            ->assertJsonPath('data.latitude', 7.41)
            ->assertJsonStructure(['data' => ['description_html', 'photos', 'videos', 'program']]);

        $this->getJson('/api/v1/events/brouillon')->assertStatus(404)->assertJsonPath('message', 'Ressource introuvable.');
        $this->getJson('/api/v1/events/inconnu')->assertStatus(404);
    }
}
