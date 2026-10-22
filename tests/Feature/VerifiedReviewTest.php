<?php

namespace Tests\Feature;

use App\Models\Provider;
use App\Models\ProviderCategory;
use App\Models\Reservation;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Un avis est marqué « séjour vérifié » seulement si son auteur a réellement séjourné. */
class VerifiedReviewTest extends TestCase
{
    use RefreshDatabase;

    private Provider $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $category = ProviderCategory::create(['slug' => 'hotels-'.uniqid(), 'name_fr' => 'Hôtels', 'name_en' => 'Hotels', 'is_active' => true]);
        $this->provider = Provider::create([
            'user_id' => User::factory()->provider()->create()->id, 'category_id' => $category->id,
            'name' => 'Hôtel Avis', 'slug' => 'hotel-avis-'.uniqid(), 'status' => 'active',
        ]);
    }

    private function review(User $user)
    {
        return $this->actingAs($user)->post(route('reviews.store', $this->provider), [
            'rating' => 5, 'comment' => 'Très bel accueil, chambre propre et calme, je recommande.',
        ]);
    }

    public function test_a_guest_who_stayed_gets_a_verified_review_linked_to_the_reservation(): void
    {
        $guest = User::factory()->create();
        $stay = Reservation::create([
            'user_id' => $guest->id, 'provider_id' => $this->provider->id, 'accommodation_name' => 'Hôtel Avis',
            'room_name' => 'Standard', 'check_in' => now()->subDays(4)->toDateString(), 'check_out' => now()->subDays(2)->toDateString(),
            'nights' => 2, 'rooms_count' => 1, 'guests_count' => 1, 'room_price_xof' => 30000, 'total_xof' => 60000,
            'deposit_amount_xof' => 18000, 'full_name' => 'Client', 'email' => $guest->email,
            'status' => Reservation::STATUS_CONFIRMED, 'payment_status' => Reservation::PAYMENT_DEPOSIT_PAID,
        ]);

        $this->review($guest)->assertRedirect();

        $review = Review::first();
        $this->assertTrue($review->is_verified);
        $this->assertSame($stay->id, $review->reservation_id);
    }

    public function test_a_review_without_a_stay_is_accepted_but_not_verified(): void
    {
        $this->review(User::factory()->create())->assertRedirect();

        $this->assertFalse(Review::first()->is_verified);
        $this->assertNull(Review::first()->reservation_id);
    }

    public function test_review_posting_is_rate_limited(): void
    {
        $user = User::factory()->create();
        for ($i = 0; $i < 5; $i++) {
            $this->review($user);
        }

        $this->review($user)->assertStatus(429);
    }
}
