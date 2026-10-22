<?php

namespace Tests\Feature;

use App\Console\Commands\SyncIcalCalendars;
use App\Http\Controllers\IcalController;
use App\Models\Accommodation;
use App\Models\Provider;
use App\Models\ProviderCategory;
use App\Models\Reservation;
use App\Models\TouristCity;
use App\Models\User;
use App\Services\RoomRates;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/** Hôtels : tarifs saisonniers / week-end, remises, synchronisation iCal (export + import), arrivée par QR. */
class HotelRatesAndIcalTest extends TestCase
{
    use RefreshDatabase;

    private Accommodation $hotel;

    private array $room;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Notification::fake();
        $city = TouristCity::create(['name' => 'Assinie', 'slug' => 'assinie-'.uniqid(), 'is_active' => true]);
        $this->room = [
            'id' => 'dbl', 'name' => 'Double', 'price_xof' => 40000, 'max_adults' => 2,
            'weekend_price_xof' => 50000,
            'rate_periods' => [['from' => '2026-12-20', 'to' => '2027-01-02', 'price_xof' => 70000, 'label' => 'Fêtes']],
            'long_stay_min_nights' => 7, 'long_stay_percent' => 10,
            'last_minute_days' => 3, 'last_minute_percent' => 15,
        ];
        $this->hotel = Accommodation::create(['city_id' => $city->id, 'name' => 'Lagune Lodge', 'slug' => 'lagune-lodge', 'type' => 'hotel', 'is_active' => true, 'room_types' => [$this->room]]);
    }

    public function test_quote_applies_weekend_season_and_the_best_single_discount(): void
    {
        app()->setLocale('fr');
        $rates = new RoomRates;
        $today = CarbonImmutable::parse('2026-11-01');

        // Mer 4 → sam 7 novembre : mer, jeu (40 000) + ven (50 000) = 130 000, pas de remise.
        $q = $rates->quote($this->room, '2026-11-04', '2026-11-07', 1, CarbonImmutable::parse('2026-10-20'));
        $this->assertSame([3, 130000, 0, 130000], [$q['nights'], $q['subtotal'], $q['discount_xof'], $q['total']]);
        $this->assertTrue($q['variable']);

        // Période des fêtes prioritaire (même un samedi) : 2 nuits × 70 000 × 2 chambres.
        $this->assertSame(280000, $rates->quote($this->room, '2026-12-26', '2026-12-28', 2, $today)['total']);

        // 7 nuits → long séjour −10 %.
        $long = $rates->quote($this->room, '2026-11-09', '2026-11-16', 1, $today);
        $this->assertSame((int) round($long['subtotal'] * 0.9), $long['total']);

        // Arrivée dans 2 jours ET 7 nuits : seule la meilleure remise (−15 %) s'applique.
        $both = $rates->quote($this->room, '2026-11-03', '2026-11-10', 1, $today);
        $this->assertSame((int) round($both['subtotal'] * 0.85), $both['total']);
        $this->assertStringContainsString('Dernière minute', $both['discount_label']);
    }

    public function test_quote_endpoint_and_booking_use_the_real_price(): void
    {
        $in = now()->addDays(20)->startOfWeek()->addDays(4)->toDateString(); // un vendredi
        $out = now()->addDays(20)->startOfWeek()->addDays(6)->toDateString(); // dimanche
        $this->getJson(route('reservations.quote', ['accommodation_id' => $this->hotel->id, 'room_id' => 'dbl', 'check_in' => $in, 'check_out' => $out]))
            ->assertOk()->assertJson(['success' => true, 'nights' => 2, 'total' => 100000, 'deposit' => 30000]);

        $this->actingAs(User::factory()->create())->postJson(route('reservations.store'), [
            'accommodation_id' => $this->hotel->id, 'room_id' => 'dbl', 'room_name' => 'Double', 'check_in' => $in, 'check_out' => $out,
            'rooms_count' => 1, 'guests_count' => 2, 'full_name' => 'Client', 'email' => 'c@example.ci',
        ])->assertOk();
        $this->assertSame(100000, (int) Reservation::firstOrFail()->total_xof, 'Week-end au prix week-end, pas au prix de base.');
    }

    public function test_ical_export_lists_bookings_and_needs_the_secret_token(): void
    {
        Reservation::create([
            'accommodation_id' => $this->hotel->id, 'accommodation_name' => 'Lagune Lodge', 'room_name' => 'Double', 'room_id' => 'dbl',
            'check_in' => '2026-11-20', 'check_out' => '2026-11-23', 'nights' => 3, 'rooms_count' => 1, 'guests_count' => 2,
            'full_name' => 'A', 'email' => 'a@example.ci', 'status' => Reservation::STATUS_CONFIRMED, 'payment_status' => Reservation::PAYMENT_DEPOSIT_PAID,
        ]);
        $url = IcalController::exportUrl($this->hotel, $this->room);

        $ics = $this->get($url)->assertOk()->assertHeader('Content-Type', 'text/calendar; charset=utf-8')->getContent();
        $this->assertStringContainsString('DTSTART;VALUE=DATE:20261120', $ics);
        $this->assertStringContainsString('DTEND;VALUE=DATE:20261123', $ics);

        $this->get(route('ical.export', [$this->hotel->id, 'dbl', str_repeat('0', 32)]))->assertNotFound();
    }

    public function test_ical_import_blocks_dates_booked_elsewhere(): void
    {
        $in = now()->addDays(30)->toDateString();
        $out = now()->addDays(33)->toDateString();
        $types = $this->hotel->room_types;
        $types[0]['ical_url'] = 'https://admin.booking.com/ical/export.ics';
        $this->hotel->update(['room_types' => $types]);
        Http::fake(['admin.booking.com/*' => Http::response("BEGIN:VCALENDAR\r\nBEGIN:VEVENT\r\nDTSTART;VALUE=DATE:".str_replace('-', '', $in)."\r\nDTEND;VALUE=DATE:".str_replace('-', '', $out)."\r\nSUMMARY:CLOSED - Not available\r\nEND:VEVENT\r\nEND:VCALENDAR\r\n")]);

        $this->artisan('ical:sync')->assertSuccessful();

        $room = $this->hotel->fresh()->room_types[0];
        $this->assertSame([['from' => $in, 'to' => $out]], array_map(fn ($b) => ['from' => $b['from'], 'to' => $b['to']], $room['external_blocks']));
        $this->assertTrue(Reservation::roomClosedDuring($room, $in, now()->addDays(31)->toDateString()));
        $this->assertFalse(Reservation::roomClosedDuring($room, $out, now()->addDays(35)->toDateString()), 'Le jour de départ reste libre.');

        $this->actingAs(User::factory()->create())->postJson(route('reservations.store'), [
            'accommodation_id' => $this->hotel->id, 'room_id' => 'dbl', 'room_name' => 'Double', 'check_in' => $in, 'check_out' => $out,
            'rooms_count' => 1, 'guests_count' => 2, 'full_name' => 'Client', 'email' => 'c@example.ci',
        ])->assertStatus(422);
    }

    public function test_ical_parser_handles_folded_lines_and_skips_past_events(): void
    {
        $ics = "BEGIN:VCALENDAR\r\nBEGIN:VEVENT\r\nDTSTART;VALUE=DATE:20200101\r\nDTEND;VALUE=DATE:20200105\r\nEND:VEVENT\r\nBEGIN:VEVENT\r\nDTSTART:20991210T140000Z\r\nDTEND:2099\r\n 1212T100000Z\r\nEND:VEVENT\r\nEND:VCALENDAR";

        $this->assertSame([['from' => '2099-12-10', 'to' => '2099-12-12']], SyncIcalCalendars::parse($ics));
    }

    public function test_provider_sets_rates_and_sees_the_express_check_in_qr(): void
    {
        $user = User::factory()->provider()->create();
        $cat = ProviderCategory::create(['slug' => 'hotels', 'name_fr' => 'Hôtels', 'name_en' => 'Hotels', 'is_active' => true]);
        $provider = Provider::create(['user_id' => $user->id, 'category_id' => $cat->id, 'name' => 'Lagune Lodge', 'slug' => 'lagune-lodge-p', 'status' => 'active']);
        $this->hotel->update(['provider_id' => $provider->id]);

        $this->actingAs($user)->put(route('provider.accommodation.rooms.update', 'dbl'), [
            'name' => 'Double', 'price_xof' => 40000, 'max_adults' => 2, 'weekend_price_xof' => 55000,
            'rate_from' => ['2026-12-20'], 'rate_to' => ['2027-01-02'], 'rate_price' => [75000], 'rate_label' => ['Fêtes'],
            'long_stay_min_nights' => 5, 'long_stay_percent' => 8, 'ical_url' => 'https://www.airbnb.com/calendar/ical/1.ics',
        ])->assertRedirect();

        $room = $this->hotel->fresh()->findRoomById('dbl');
        $this->assertSame(55000, $room['weekend_price_xof']);
        $this->assertSame(75000, $room['rate_periods'][0]['price_xof']);
        $this->assertSame('https://www.airbnb.com/calendar/ical/1.ics', $room['ical_url']);
        $this->actingAs($user)->get(route('provider.accommodation.rooms.edit', 'dbl'))->assertOk()->assertSee('/ical/'.$this->hotel->id.'/dbl/', false);

        $r = Reservation::create([
            'accommodation_id' => $this->hotel->id, 'provider_id' => $provider->id, 'accommodation_name' => 'Lagune Lodge', 'room_name' => 'Double',
            'check_in' => now()->addDays(5)->toDateString(), 'check_out' => now()->addDays(7)->toDateString(), 'nights' => 2, 'rooms_count' => 1, 'guests_count' => 2,
            'full_name' => 'A', 'email' => 'a@example.ci', 'status' => Reservation::STATUS_CONFIRMED, 'payment_status' => Reservation::PAYMENT_DEPOSIT_PAID,
        ]);
        $this->actingAs($user)->get(route('provider.reservations.show', $r))->assertOk()->assertSee('Arrivée express')->assertSee('data:image/png;base64', false);
    }
}
