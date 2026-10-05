<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Même bug que ArticleCoverEditBugTest, côté événements : la règle 'url' seule
 * rejetait le chemin relatif /storage/... déjà en place sur un événement ayant
 * une couverture uploadée, faisant échouer toute modification de l'événement.
 */
class EventCoverEditBugTest extends TestCase
{
    use RefreshDatabase;

    public function test_editing_an_event_with_an_existing_relative_cover_url_does_not_fail_validation(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = EventCategory::create(['slug' => 'cat-evt-test', 'name_fr' => 'Cat', 'name_en' => 'Cat', 'is_active' => true]);

        $event = Event::create([
            'title_fr' => 'Événement test',
            'slug' => 'evenement-test',
            'category_id' => $category->id,
            'cover_url' => '/storage/events/covers/exemple.webp',
            'starts_at' => now()->addDays(5),
            'is_free' => true,
            'price' => 0,
            'status' => 'draft',
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->put(route('editor.events.update', $event), [
            'title_fr' => 'Événement test modifié',
            'category_id' => $category->id,
            'cover_url' => $event->cover_url, // valeur pré-remplie par le formulaire, non modifiée
            'starts_at' => $event->starts_at->toDateTimeString(),
            'is_free' => '1',
            'status' => 'draft',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('editor.events.index'));
        $this->assertSame('Événement test modifié', $event->fresh()->title_fr);
        $this->assertSame($event->cover_url, $event->fresh()->cover_url);
    }
}
