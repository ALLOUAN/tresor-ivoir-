<?php

namespace Tests\Feature;

use App\Models\Artwork;
use App\Models\ArtworkCategory;
use App\Models\ArtworkOrder;
use App\Models\BookingRequest;
use App\Models\Provider;
use App\Models\ProviderCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/** Art & Créations : commandes sur mesure et certificats d'authenticité. */
class ArtCommissionAndCertificateTest extends TestCase
{
    use RefreshDatabase;

    private Provider $artist;

    private Provider $restaurant;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Notification::fake();
        $art = ProviderCategory::firstOrCreate(['slug' => 'art-creations'], ['name_fr' => 'Art & Créations', 'name_en' => 'Art', 'is_active' => true]);
        $food = ProviderCategory::firstOrCreate(['slug' => 'restaurants'], ['name_fr' => 'Restaurants', 'name_en' => 'Restaurants', 'is_active' => true]);
        $this->artist = Provider::create(['user_id' => User::factory()->provider()->create()->id, 'category_id' => $art->id, 'name' => 'Atelier Koffi', 'slug' => 'atelier-koffi', 'status' => 'active']);
        $this->restaurant = Provider::create(['user_id' => User::factory()->provider()->create()->id, 'category_id' => $food->id, 'name' => 'Maquis', 'slug' => 'maquis', 'status' => 'active']);
    }

    private function commission(Provider $target, array $o = [])
    {
        return $this->actingAs(User::factory()->create())->post(route('booking-requests.store'), $o + [
            'bookable_type' => 'art', 'bookable_id' => $target->id,
            'booking_date' => now()->addMonth()->toDateString(), 'party_size' => 1,
            'full_name' => 'Awa', 'email' => 'awa@example.ci',
            'message' => 'Un portrait de famille, 80×60 cm, acrylique sur toile.',
        ]);
    }

    public function test_only_artists_take_commissions_and_a_description_is_required(): void
    {
        $this->get(route('providers.show', 'atelier-koffi'))->assertOk()->assertSee('Commander une œuvre sur mesure');

        $this->commission($this->artist, ['message' => ''])->assertSessionHasErrors('message', null, 'booking');
        $this->commission($this->restaurant)->assertSessionHasErrors('bookable_id', null, 'booking');
        $this->commission($this->artist)->assertSessionHas('booking_success');

        $b = BookingRequest::firstOrFail();
        $this->assertSame([BookingRequest::TYPE_ART, $this->artist->id], [$b->bookable_type, (int) $b->provider_id]);
    }

    public function test_certificate_is_available_once_paid_and_cannot_be_guessed(): void
    {
        $cat = ArtworkCategory::firstOrCreate(['slug' => 'peinture'], ['name_fr' => 'Peinture', 'name_en' => 'Painting', 'is_active' => true]);
        $artwork = Artwork::create(['provider_id' => $this->artist->id, 'category_id' => $cat->id, 'title' => 'Lagune au crépuscule', 'slug' => 'lagune', 'medium' => 'Huile sur toile', 'dimensions' => '100 × 70 cm', 'year_created' => 2025, 'price_xof' => 300000, 'stock_quantity' => 1, 'images' => [], 'status' => Artwork::STATUS_PUBLISHED]);
        $order = ArtworkOrder::create([
            'artwork_id' => $artwork->id, 'provider_id' => $this->artist->id, 'buyer_user_id' => User::factory()->create()->id,
            'unit_price_xof' => 300000, 'amount_total_xof' => 300000, 'commission_percent' => 15, 'commission_amount_xof' => 45000,
            'artist_net_amount_xof' => 255000, 'currency' => 'XOF', 'status' => ArtworkOrder::STATUS_PENDING_PAYMENT,
            'gateway' => 'cinetpay', 'buyer_name' => 'Mariam', 'buyer_email' => 'mariam@example.ci',
        ]);

        $this->get($order->certificatePdfUrl())->assertNotFound();

        $order->forceFill(['status' => ArtworkOrder::STATUS_PAID, 'paid_at' => now()])->save();
        $this->get($order->certificateVerifyUrl())->assertOk()->assertSee('Certificat authentique')->assertSee('Lagune au crépuscule')->assertSee('TI-ART-');
        $pdf = $this->get($order->certificatePdfUrl());
        $pdf->assertOk();
        $this->assertStringStartsWith('%PDF', $pdf->getContent());

        $this->get(route('art.certificate.verify', [$order->id, 'ABCDEF123456']))->assertNotFound();
    }
}
