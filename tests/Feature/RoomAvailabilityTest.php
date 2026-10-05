<?php

namespace Tests\Feature;

use App\Models\Accommodation;
use App\Models\Reservation;
use App\Models\TouristCity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Calendrier de disponibilité par chambre : l'endpoint public doit refléter
 * exactement les réservations actives, et la création de réservation doit
 * refuser toute période qui chevauche une réservation existante — pour LA
 * chambre visée précisément (par room_id), sans faux positif sur une autre
 * chambre du même hébergement ni faux négatif sur une réservation historique
 * sans room_id (repli par room_name).
 */
class RoomAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    private function makeAccommodation(): Accommodation
    {
        $city = TouristCity::create(['name' => 'Abidjan Dispo', 'slug' => 'abidjan-dispo-'.uniqid(), 'is_active' => true]);

        return Accommodation::create([
            'city_id' => $city->id,
            'name' => 'Hôtel Test Disponibilité',
            'slug' => 'hotel-test-dispo-'.uniqid(),
            'type' => 'hotel',
            'is_active' => true,
            'room_types' => [
                ['id' => 'room-a', 'name' => 'Chambre A', 'price_xof' => 40000, 'max_adults' => 2, 'max_children' => 0],
                ['id' => 'room-b', 'name' => 'Chambre B', 'price_xof' => 60000, 'max_adults' => 2, 'max_children' => 0],
            ],
        ]);
    }

    private function payload(Accommodation $a, string $roomId, string $roomName, string $checkIn, string $checkOut): array
    {
        return [
            'accommodation_id' => $a->id,
            'room_id' => $roomId,
            'room_name' => $roomName,
            'room_price_xof' => 1,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'rooms_count' => 1,
            'guests_count' => 2,
            'full_name' => 'Client Test',
            'email' => 'client-dispo@example.ci',
            'phone' => '0700000000',
        ];
    }

    public function test_availability_endpoint_reflects_active_reservations(): void
    {
        Mail::fake();
        Notification::fake();
        $a = $this->makeAccommodation();

        // Vide au départ.
        $this->getJson(route('reservations.availability', ['accommodation_id' => $a->id, 'room_id' => 'room-a']))
            ->assertOk()
            ->assertJson(['success' => true, 'blocked_ranges' => []]);

        $checkIn = now()->addDays(10)->toDateString();
        $checkOut = now()->addDays(13)->toDateString();

        $this->actingAs(User::factory()->create())
            ->postJson(route('reservations.store'), $this->payload($a, 'room-a', 'Chambre A', $checkIn, $checkOut))
            ->assertOk();

        $response = $this->getJson(route('reservations.availability', ['accommodation_id' => $a->id, 'room_id' => 'room-a']));
        $response->assertOk();
        $ranges = $response->json('blocked_ranges');
        $this->assertCount(1, $ranges);
        $this->assertSame($checkIn, $ranges[0]['check_in']);
        $this->assertSame($checkOut, $ranges[0]['check_out']);
    }

    public function test_availability_endpoint_does_not_leak_bookings_of_another_room(): void
    {
        Mail::fake();
        Notification::fake();
        $a = $this->makeAccommodation();
        $checkIn = now()->addDays(10)->toDateString();
        $checkOut = now()->addDays(13)->toDateString();

        $this->actingAs(User::factory()->create())
            ->postJson(route('reservations.store'), $this->payload($a, 'room-a', 'Chambre A', $checkIn, $checkOut))
            ->assertOk();

        // La chambre B, elle, doit rester entièrement disponible.
        $this->getJson(route('reservations.availability', ['accommodation_id' => $a->id, 'room_id' => 'room-b']))
            ->assertOk()
            ->assertJson(['success' => true, 'blocked_ranges' => []]);
    }

    public function test_overlapping_dates_on_the_same_room_are_rejected(): void
    {
        Mail::fake();
        Notification::fake();
        $a = $this->makeAccommodation();
        $checkIn = now()->addDays(10)->toDateString();
        $checkOut = now()->addDays(13)->toDateString();

        $this->actingAs(User::factory()->create())
            ->postJson(route('reservations.store'), $this->payload($a, 'room-a', 'Chambre A', $checkIn, $checkOut))
            ->assertOk();

        // Nouvelle tentative avec une période qui chevauche partiellement — même sans
        // tomber exactement sur les mêmes dates, elle doit être refusée.
        $overlapIn = now()->addDays(12)->toDateString();
        $overlapOut = now()->addDays(15)->toDateString();

        $this->actingAs(User::factory()->create())
            ->postJson(route('reservations.store'), $this->payload($a, 'room-a', 'Chambre A', $overlapIn, $overlapOut))
            ->assertStatus(422)
            ->assertJson(['success' => false]);

        $this->assertSame(1, Reservation::count());
    }

    public function test_overlapping_dates_on_a_different_room_are_accepted(): void
    {
        Mail::fake();
        Notification::fake();
        $a = $this->makeAccommodation();
        $checkIn = now()->addDays(10)->toDateString();
        $checkOut = now()->addDays(13)->toDateString();

        $this->actingAs(User::factory()->create())
            ->postJson(route('reservations.store'), $this->payload($a, 'room-a', 'Chambre A', $checkIn, $checkOut))
            ->assertOk();

        // Mêmes dates exactement, mais chambre B — aucun conflit, doit passer.
        $this->actingAs(User::factory()->create())
            ->postJson(route('reservations.store'), $this->payload($a, 'room-b', 'Chambre B', $checkIn, $checkOut))
            ->assertOk();

        $this->assertSame(2, Reservation::count());
    }

    /**
     * Repli historique : une réservation créée avant l'ajout de room_id (donc sans
     * room_id, identifiée par room_name uniquement) doit toujours être détectée comme
     * un conflit par une nouvelle tentative qui, elle, fournit room_id — sinon une
     * chambre renommée ou une ancienne réservation laisserait passer un double-booking.
     */
    public function test_legacy_reservation_without_room_id_still_blocks_new_attempts_by_name(): void
    {
        Mail::fake();
        Notification::fake();
        $a = $this->makeAccommodation();
        $checkIn = now()->addDays(10)->toDateString();
        $checkOut = now()->addDays(13)->toDateString();

        Reservation::create([
            'accommodation_id' => $a->id,
            'accommodation_name' => $a->name,
            'room_name' => 'Chambre A',
            'room_id' => null,
            'room_price_xof' => 40000,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'nights' => 3,
            'rooms_count' => 1,
            'guests_count' => 2,
            'total_xof' => 120000,
            'full_name' => 'Ancien Client',
            'email' => 'ancien@example.ci',
            'status' => Reservation::STATUS_CONFIRMED,
            'payment_status' => Reservation::PAYMENT_DEPOSIT_PAID,
        ]);

        $this->actingAs(User::factory()->create())
            ->postJson(route('reservations.store'), $this->payload($a, 'room-a', 'Chambre A', $checkIn, $checkOut))
            ->assertStatus(422);

        $this->assertSame(1, Reservation::count());
    }
}
