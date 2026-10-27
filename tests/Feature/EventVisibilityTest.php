<?php

namespace Tests\Feature;

use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/** Un événement publié, à venir ou en cours, doit apparaître sur la page Événements et l'accueil. */
class EventVisibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function event(string $title, string $starts, ?string $ends = null, string $status = 'published'): Event
    {
        // Catégorie et auteur sont obligatoires en base.
        $category = \App\Models\EventCategory::firstOrCreate(['slug' => 'divers'], ['name_fr' => 'Divers', 'sort_order' => 99]);
        $author = \App\Models\User::firstOrCreate(['email' => 'redaction@exemple.ci'], [
            'first_name' => 'Rédaction', 'last_name' => 'TI', 'password_hash' => bcrypt('x'), 'role' => 'editor', 'is_active' => true,
        ]);

        return Event::create([
            'category_id' => $category->id, 'created_by' => $author->id,
            'title_fr' => $title, 'slug' => \Illuminate\Support\Str::slug($title), 'description_fr' => '<p>Texte</p>',
            'starts_at' => $starts, 'ends_at' => $ends, 'status' => $status, 'city' => 'Abidjan',
            'is_free' => true, 'price' => 0, 'published_at' => now(),
        ]);
    }

    public function test_upcoming_and_ongoing_events_are_listed_finished_ones_are_past(): void
    {
        Carbon::setTestNow('2026-10-27 15:00:00');
        $this->event('Concert à venir', '2026-11-02 20:00:00');
        $this->event('Festival en cours', '2026-10-25 09:00:00', '2026-10-29 18:00:00');
        $this->event('Atelier ce matin', '2026-10-27 09:00:00');
        $this->event('Salon terminé', '2026-10-10 09:00:00', '2026-10-12 18:00:00');
        $this->event('Brouillon caché', '2026-11-05 10:00:00', null, 'draft');

        $this->get(route('events.index'))->assertOk()
            ->assertSee('Concert à venir')->assertSee('Festival en cours')->assertSee('Atelier ce matin')
            ->assertDontSee('Salon terminé')->assertDontSee('Brouillon caché');

        $this->get(route('events.index', ['periode' => 'past']))->assertOk()
            ->assertSee('Salon terminé')->assertDontSee('Festival en cours');

        $this->assertSame(3, Event::notFinished()->where('status', 'published')->count());
        $this->assertSame(1, Event::finished()->where('status', 'published')->count());
    }

    public function test_homepage_shows_ongoing_event(): void
    {
        Carbon::setTestNow('2026-10-27 15:00:00');
        $this->event('Festival en cours', '2026-10-25 09:00:00', '2026-10-29 18:00:00');

        $this->get(route('home'))->assertOk()->assertSee('Festival en cours');
    }
}
