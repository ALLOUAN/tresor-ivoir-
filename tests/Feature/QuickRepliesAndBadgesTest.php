<?php

namespace Tests\Feature;

use App\Models\BookingRequest;
use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\Provider;
use App\Models\ProviderCategory;
use App\Models\ProviderMember;
use App\Models\Restaurant;
use App\Models\TouristCity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/** Messagerie : réponses rapides (y compris pour l'équipe) ; badge de délai de réponse. */
class QuickRepliesAndBadgesTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Provider $provider;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Notification::fake();
        $this->owner = User::factory()->provider()->create();
        $cat = ProviderCategory::firstOrCreate(['slug' => 'restaurants'], ['name_fr' => 'Restaurants', 'name_en' => 'Restaurants', 'is_active' => true]);
        $this->provider = Provider::create(['user_id' => $this->owner->id, 'category_id' => $cat->id, 'name' => 'Le Ponton', 'slug' => 'le-ponton', 'status' => 'active']);
        $city = TouristCity::create(['name' => 'Assinie', 'slug' => 'assinie-'.uniqid(), 'is_active' => true]);
        Restaurant::create(['city_id' => $city->id, 'provider_id' => $this->provider->id, 'name' => 'Le Ponton', 'slug' => 'le-ponton', 'is_active' => true]);
    }

    public function test_team_members_answer_messages_with_quick_replies(): void
    {
        $client = User::factory()->create(['first_name' => 'Awa']);
        $conv = Conversation::create(['client_id' => $client->id, 'provider_id' => $this->provider->id, 'subject' => 'Table samedi']);
        ConversationMessage::create(['conversation_id' => $conv->id, 'sender_id' => $client->id, 'body' => 'Avez-vous une table samedi ?']);

        $staff = User::factory()->provider()->create();
        ProviderMember::create(['provider_id' => $this->provider->id, 'user_id' => $staff->id]);

        $this->actingAs($staff)->get(route('provider.client-conversations.show', $conv))->assertOk()
            ->assertSee('Réponses rapides')->assertSee('data-client="Awa"', false);

        $this->actingAs($this->owner)->post(route('provider.client-conversations.quick-replies'), ['quick_replies' => "Bonjour {client} !\n\n  Oui, il reste de la place.  \nBonjour {client} !"])->assertSessionHas('success');
        $this->assertSame(['Bonjour {client} !', 'Oui, il reste de la place.'], $this->provider->fresh()->quick_replies);
        $this->actingAs($staff)->get(route('provider.client-conversations.show', $conv))->assertSee('Oui, il reste de la place.');

        $this->actingAs($this->owner)->post(route('provider.client-conversations.quick-replies'), ['quick_replies' => ''])->assertSessionHas('success');
        $this->assertSame(Provider::DEFAULT_QUICK_REPLIES, $this->provider->fresh()->quickReplies());
    }

    private function request(?int $answeredAfterMinutes = null, int $hoursAgo = 72): void
    {
        $created = now()->subHours($hoursAgo);
        $b = BookingRequest::create([
            'provider_id' => $this->provider->id, 'bookable_type' => 'restaurant', 'bookable_id' => 1, 'bookable_name' => 'Le Ponton',
            'booking_date' => now()->addDays(5)->toDateString(), 'party_size' => 2, 'full_name' => 'Awa', 'email' => 'awa@example.ci',
            'status' => $answeredAfterMinutes === null ? BookingRequest::STATUS_NEW : BookingRequest::STATUS_CONFIRMED,
            'answered_at' => $answeredAfterMinutes === null ? null : $created->copy()->addMinutes($answeredAfterMinutes),
        ]);
        $b->forceFill(['created_at' => $created])->saveQuietly();
    }

    public function test_fast_responders_get_a_badge_unless_they_ignore_requests(): void
    {
        foreach ([10, 25, 50] as $m) {
            $this->request($m);
        }
        $this->artisan('providers:response-stats')->assertSuccessful();
        $this->assertSame([25, 100], [$this->provider->fresh()->response_minutes, $this->provider->fresh()->response_rate]);
        $this->get(route('restaurant.show', 'le-ponton'))->assertSee('Répond en moins d&#039;1 h', false);

        // Trois demandes restées sans réponse : 50 % de réponse, plus de badge.
        foreach (range(1, 3) as $i) {
            $this->request(null);
        }
        $this->artisan('providers:response-stats');
        $this->assertSame(50, $this->provider->fresh()->response_rate);
        $this->assertNull($this->provider->fresh()->responseBadge());
    }
}
