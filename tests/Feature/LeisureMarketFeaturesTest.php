<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\ActivityBooking;
use App\Models\ActivityCategory;
use App\Models\ActivityWaitlist;
use App\Models\LeisurePackage;
use App\Models\LeisurePass;
use App\Models\LeisurePassPurchase;
use App\Models\LeisureResource;
use App\Models\LeisureVenue;
use App\Models\Provider;
use App\Models\ProviderCategory;
use App\Models\TouristCity;
use App\Models\User;
use App\Models\Wallet;
use App\Services\LeisureBookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Loisirs « marché » : créneaux et billet QR, ressources partagées, formules avec acompte, tarifs et
 * options, conditions et décharge, cartes multi-séances, liste d'attente, annulation et report.
 */
class LeisureMarketFeaturesTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Provider $provider;

    private Activity $activity;

    private User $client;

    private string $date;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        $this->owner = User::factory()->provider()->create();
        $cat = ProviderCategory::firstOrCreate(['slug' => 'loisirs-culture'], ['name_fr' => 'Loisirs & Culture', 'name_en' => 'Leisure', 'is_active' => true]);
        $this->provider = Provider::create(['user_id' => $this->owner->id, 'category_id' => $cat->id, 'name' => 'Fun Park', 'slug' => 'fun-park', 'status' => 'active']);
        $city = TouristCity::create(['name' => 'Abidjan', 'slug' => 'abj-'.uniqid(), 'is_active' => true]);
        LeisureVenue::create(['provider_id' => $this->provider->id, 'city_id' => $city->id, 'name' => 'Fun Park', 'slug' => 'fun-park', 'is_active' => true]);
        $ac = ActivityCategory::firstOrCreate(['slug' => 'sport'], ['name_fr' => 'Sport', 'name_en' => 'Sport', 'is_active' => true]);
        $this->activity = Activity::create([
            'provider_id' => $this->provider->id, 'category_id' => $ac->id, 'name' => 'Kayak sur la lagune', 'price_xof' => 5000,
            'duration_minutes' => 90, 'max_participants' => 10, 'is_available' => true,
            'slot_times' => ['09:00', '11:00', '15:00'], 'slot_capacity' => 4, 'instant_booking' => true,
            'price_tiers' => [['label' => 'Adulte', 'price_xof' => 5000], ['label' => 'Enfant', 'price_xof' => 3000]],
            'addons' => [['label' => 'Gilet photo', 'price_xof' => 1000, 'per' => 'person'], ['label' => 'Pack photos', 'price_xof' => 4000, 'per' => 'booking']],
            'offpeak_until' => '10:00', 'offpeak_discount_percent' => 20, 'free_cancellation_hours' => 24,
            'min_age' => 8, 'requirements' => 'Savoir nager', 'to_bring' => 'Maillot',
        ]);
        $this->client = User::factory()->create(['role' => 'visitor']);
        Wallet::forUser($this->client)->update(['balance_available_xof' => 500000]);
        $this->date = Carbon::now('Africa/Abidjan')->next(Carbon::TUESDAY)->toDateString();
    }

    private function book(array $o = [], ?User $as = null)
    {
        return $this->actingAs($as ?? $this->client)->post(route('activity.book', $this->activity->slug), $o + [
            'date' => $this->date, 'time' => '15:00', 'tiers' => [2, 0], 'payment_method' => 'wallet',
        ]);
    }

    private function wallet(?User $u = null): int
    {
        return Wallet::forUser($u ?? $this->client)->fresh()->balance_available_xof;
    }

    public function test_page_shows_conditions_and_slots_with_remaining_places(): void
    {
        $this->assertSame('kayak-sur-la-lagune-'.$this->activity->id, $this->activity->slug);
        $this->actingAs($this->client)->get(route('activity.show', $this->activity->slug))->assertOk()
            ->assertSee('Savoir nager')->assertSee('À partir de 8 ans')->assertSee('Maillot')->assertSee('id="lx-form"', false)
            ->assertSee('Annulation et report gratuits jusqu\'à 24 h avant');
        $this->book()->assertRedirect();
        $slots = collect($this->getJson(route('activity.slots', [$this->activity->slug, 'date' => $this->date]))->json('slots'))->keyBy('time');
        $this->assertSame(2, $slots['15:00']['remaining']);
        $this->assertSame(4, $slots['09:00']['remaining']);
        $this->assertSame(-20, $slots['09:00']['adjust_percent']);
        $this->get(route('leisure.show', 'fun-park'))->assertSee(route('activity.show', $this->activity->slug), false);
    }

    public function test_instant_booking_with_tiers_addons_offpeak_and_qr_ticket(): void
    {
        // 09:00 heures creuses −20 % : adulte 4 000, enfant 2 400 ; gilet 2 × 1 000 ; photos 4 000.
        $this->book(['time' => '09:00', 'tiers' => [1, 1], 'addons' => [0, 1]])->assertRedirect();
        $b = ActivityBooking::sole();
        $this->assertSame(ActivityBooking::STATUS_PAID, $b->status);
        $this->assertSame(4000 + 2400 + 2000 + 4000, $b->amount_total_xof);
        $this->assertSame(500000 - 12400, $this->wallet());
        $this->assertSame(12400 - (int) round(12400 * 0.1), Wallet::forProvider($this->provider)->fresh()->balance_available_xof);
        $this->assertNotNull($b->ticketCode());
        $this->get($b->ticketUrl())->assertOk()->assertSee('Votre billet électronique')->assertSee('Savoir nager');

        $this->actingAs($this->owner)->get(route('provider.tickets.show', $b->ticket_code))->assertOk()->assertSee('pas encore valable');
        $b->update(['booking_date' => now('Africa/Abidjan')->toDateString()]);
        $this->actingAs($this->owner)->get(route('provider.tickets.show', $b->ticket_code))->assertOk()->assertSee('Billet valable')->assertSee('Kayak sur la lagune');
        $this->actingAs($this->owner)->post(route('provider.tickets.check-in', $b->ticket_code))->assertSessionHas('success');
        $this->assertNotNull($b->fresh()->checked_in_at);
        $this->actingAs($this->client)->post(route('visitor.activity-bookings.cancel', $b))->assertSessionHas('error');
    }

    public function test_full_slot_is_refused_then_waitlist_is_notified_when_a_place_frees_up(): void
    {
        $this->book(['tiers' => [4, 0]])->assertRedirect();
        $other = User::factory()->create(['role' => 'visitor']);
        Wallet::forUser($other)->update(['balance_available_xof' => 100000]);
        $this->book(['tiers' => [1, 0]], $other)->assertSessionHasErrors('booking', null, 'leisure');
        $this->assertSame(1, ActivityBooking::count());

        $this->actingAs($other)->post(route('activity.waitlist', $this->activity->slug), ['date' => $this->date, 'time' => '15:00', 'participants' => 2])->assertSessionHas('waitlist_ok');
        $this->actingAs($this->client)->post(route('visitor.activity-bookings.cancel', ActivityBooking::sole()))->assertSessionHas('success');
        $w = ActivityWaitlist::sole();
        $this->assertSame(ActivityWaitlist::STATUS_NOTIFIED, $w->status);
        $this->assertNotNull($w->expires_at);

        // Elle réserve : son inscription passe à « réservée ».
        $this->book(['tiers' => [2, 0]], $other)->assertRedirect();
        $this->assertSame(ActivityWaitlist::STATUS_BOOKED, $w->fresh()->status);
    }

    public function test_waitlist_moves_on_when_the_hold_expires(): void
    {
        $this->book(['tiers' => [4, 0]]);
        [$u1, $u2] = [User::factory()->create(['role' => 'visitor']), User::factory()->create(['role' => 'visitor'])];
        $svc = app(LeisureBookingService::class);
        $svc->joinWaitlist($this->activity, $u1, $this->date, '15:00', 1);
        $this->travelTo(now()->addSecond());
        $svc->joinWaitlist($this->activity, $u2, $this->date, '15:00', 1);
        $this->actingAs($this->client)->post(route('visitor.activity-bookings.cancel', ActivityBooking::sole()));
        $this->assertSame([ActivityWaitlist::STATUS_NOTIFIED, ActivityWaitlist::STATUS_WAITING], ActivityWaitlist::orderBy('id')->pluck('status')->all());
        $this->travelTo(now()->addHours(3));
        $this->assertSame(1, $svc->processWaitlist());
        $this->assertSame([ActivityWaitlist::STATUS_EXPIRED, ActivityWaitlist::STATUS_NOTIFIED], ActivityWaitlist::orderBy('id')->pluck('status')->all());
    }

    public function test_free_cancellation_refunds_and_reschedule_moves_the_booking(): void
    {
        $this->book()->assertRedirect();
        $b = ActivityBooking::sole();
        $next = Carbon::parse($this->date)->addDay()->toDateString();
        $this->actingAs($this->client)->post(route('visitor.activity-bookings.reschedule', $b), ['date' => $next, 'time' => '11:00'])->assertSessionHas('success');
        $b->refresh();
        $this->assertSame($next, $b->booking_date->toDateString());
        $this->assertSame('11:00', $b->booking_time);
        $this->assertSame(1, $b->reschedule_count);

        $this->actingAs($this->client)->post(route('visitor.activity-bookings.cancel', $b))->assertSessionHas('success');
        $this->assertSame(ActivityBooking::STATUS_REFUNDED, $b->fresh()->status);
        $this->assertSame(500000, $this->wallet());
        $this->assertSame(0, Wallet::forProvider($this->provider)->fresh()->balance_available_xof);

        // Délai dépassé : refusé.
        $this->book()->assertRedirect();
        $late = ActivityBooking::latest('id')->first();
        $late->update(['cancellable_until' => now()->subMinute()]);
        $this->actingAs($this->client)->post(route('visitor.activity-bookings.cancel', $late))->assertSessionHas('error');
    }

    public function test_shared_resource_blocks_other_activities_on_overlapping_times(): void
    {
        $room = LeisureResource::create(['provider_id' => $this->provider->id, 'name' => 'Ponton', 'units' => 6]);
        $this->activity->resources()->attach($room->id, ['units' => 1, 'per_participant' => true]);
        $this->activity->update(['slot_capacity' => null]);
        $paddle = Activity::create(['provider_id' => $this->provider->id, 'category_id' => $this->activity->category_id, 'name' => 'Paddle', 'price_xof' => 4000,
            'duration_minutes' => 60, 'is_available' => true, 'slot_times' => ['15:30'], 'instant_booking' => true]);
        $paddle->resources()->attach($room->id, ['units' => 1, 'per_participant' => true]);

        $this->book(['tiers' => [5, 0]])->assertRedirect(); // kayak 15:00–16:30, 5 places du ponton
        $slots = collect($this->getJson(route('activity.slots', [$paddle->slug, 'date' => $this->date]))->json('slots'))->keyBy('time');
        $this->assertSame(1, $slots['15:30']['remaining'], 'Le paddle de 15:30 chevauche le kayak : 1 place de ponton restante.');
        $this->actingAs($this->client)->post(route('activity.book', $paddle->slug), ['date' => $this->date, 'time' => '15:30', 'tiers' => [2], 'payment_method' => 'wallet'])
            ->assertSessionHasErrors('booking', null, 'leisure');
    }

    public function test_waiver_is_required_and_recorded(): void
    {
        $this->activity->update(['requires_waiver' => true, 'waiver_text' => 'Je reconnais les risques.']);
        $this->book()->assertSessionHasErrors('booking', null, 'leisure');
        $this->book(['waiver_names' => ['Aya', 'Koffi'], 'waiver_accept' => 1])->assertRedirect();
        $b = ActivityBooking::sole();
        $this->assertSame(['Aya', 'Koffi'], $b->waiver['names']);
        $this->assertNotNull($b->waiver['accepted_at']);
        $this->assertFalse($b->needsWaiver());
    }

    public function test_pass_is_bought_then_used_and_sessions_are_restored_on_cancel(): void
    {
        $pass = LeisurePass::create(['provider_id' => $this->provider->id, 'name' => 'Carte 5 sorties', 'sessions' => 5, 'validity_days' => 90, 'price_xof' => 20000, 'is_active' => true]);
        $this->actingAs($this->client)->post(route('leisure-pass.buy', $pass), ['payment_method' => 'wallet'])->assertRedirect(route('visitor.activity-bookings.index'));
        $purchase = LeisurePassPurchase::sole();
        $this->assertSame(LeisurePassPurchase::STATUS_ACTIVE, $purchase->status);
        $this->assertSame(500000 - 20000, $this->wallet());
        $this->assertSame(now('Africa/Abidjan')->addDays(90)->toDateString(), $purchase->expires_on->toDateString());

        $this->book(['payment_method' => 'pass', 'pass_id' => $purchase->id, 'tiers' => [2, 0]])->assertRedirect();
        $b = ActivityBooking::sole();
        $this->assertSame('pass', $b->gateway);
        $this->assertSame(0, $b->amount_total_xof);
        $this->assertSame(3, $purchase->fresh()->remaining());
        $this->assertSame(480000, $this->wallet(), 'Rien de plus débité.');

        $this->book(['payment_method' => 'pass', 'pass_id' => $purchase->id, 'tiers' => [4, 0], 'time' => '11:00'])->assertSessionHasErrors('booking', null, 'leisure');
        $this->actingAs($this->client)->post(route('visitor.activity-bookings.cancel', $b));
        $this->assertSame(5, $purchase->fresh()->remaining());
        $this->actingAs($this->client)->get(route('visitor.activity-bookings.index'))->assertSee('Carte 5 sorties')->assertSee('5 séances restantes');
    }

    public function test_package_with_deposit_privatises_the_room(): void
    {
        $room = LeisureResource::create(['provider_id' => $this->provider->id, 'name' => 'Salle des fêtes', 'units' => 1]);
        $pk = LeisurePackage::create(['provider_id' => $this->provider->id, 'name' => 'Anniversaire Fun', 'kind' => 'birthday',
            'weekday_price_xof' => 60000, 'weekend_price_xof' => 80000, 'included_participants' => 10, 'extra_participant_xof' => 5000,
            'min_participants' => 6, 'max_participants' => 20, 'duration_minutes' => 120, 'deposit_xof' => 20000,
            'slot_times' => ['14:00', '16:30'], 'leisure_resource_id' => $room->id, 'free_cancellation_days' => 7, 'is_active' => true]);
        $saturday = Carbon::now('Africa/Abidjan')->next(Carbon::SATURDAY)->addWeek()->toDateString();

        $this->get(route('leisure-package.show', $pk->slug))->assertOk()->assertSee('Anniversaire Fun');
        $this->actingAs($this->client)->post(route('leisure-package.book', $pk->slug), ['date' => $saturday, 'time' => '14:00', 'participants' => 12,
            'celebrant_name' => 'Ama', 'payment_method' => 'wallet', 'phone' => '0707'])->assertRedirect();
        $b = ActivityBooking::sole();
        $this->assertSame(80000 + 2 * 5000, $b->amount_total_xof);
        $this->assertSame(20000, $b->amount_paid_xof);
        $this->assertSame(70000, $b->balanceOnSite());
        $this->assertSame(500000 - 20000, $this->wallet());

        $slots = collect($this->getJson(route('leisure-package.slots', [$pk->slug, 'date' => $saturday]))->json('slots'))->keyBy('time');
        $this->assertFalse($slots['14:00']['available']);
        $this->assertTrue($slots['16:30']['available']);

        // La salle privatisée bloque une activité qui l'utilise sur le même horaire.
        $this->activity->resources()->attach($room->id, ['units' => 1, 'per_participant' => false]);
        $this->activity->update(['slot_capacity' => null, 'weekdays' => null]);
        $act = collect($this->getJson(route('activity.slots', [$this->activity->slug, 'date' => $saturday]))->json('slots'))->keyBy('time');
        $this->assertSame(0, $act['15:00']['remaining']);
    }

    public function test_provider_manages_activity_market_fields_and_offers(): void
    {
        $this->actingAs($this->owner)->put(route('provider.activities.update', $this->activity), [
            'name' => 'Kayak', 'category_id' => $this->activity->category_id, 'price_xof' => 5000, 'duration_minutes' => 60,
            'slot_times_text' => '16:00 08:30, 8h30', 'slot_capacity' => 6, 'weekdays' => [6, 7],
            'tiers' => [['label' => 'Adulte', 'price_xof' => 5000]], 'free_cancellation_hours' => 24,
            'requires_waiver' => 1, 'waiver_text' => 'Risques acceptés.', 'min_age' => 10,
        ])->assertRedirect()->assertSessionHasNoErrors();
        $a = $this->activity->fresh();
        $this->assertSame(['08:30', '16:00'], $a->slot_times);
        $this->assertSame([6, 7], $a->weekdays);
        $this->assertTrue($a->requires_waiver);

        $this->actingAs($this->owner)->post(route('provider.leisure-resources.store'), ['name' => 'Salle', 'units' => 1])->assertSessionHas('success');
        $this->actingAs($this->owner)->post(route('provider.leisure-packages.store'), ['name' => 'Team building', 'kind' => 'corporate', 'weekday_price_xof' => 150000,
            'included_participants' => 15, 'min_participants' => 8, 'duration_minutes' => 180, 'slot_times_text' => '09:00, 14:00', 'free_cancellation_days' => 14])->assertSessionHasNoErrors();
        $this->actingAs($this->owner)->post(route('provider.leisure-passes.store'), ['name' => 'Abonnement mensuel', 'validity_days' => 30, 'price_xof' => 25000])->assertSessionHasNoErrors();
        $this->assertSame(1, LeisurePackage::count());
        $this->assertNull(LeisurePass::sole()->sessions);
        foreach (['provider.activity-bookings.index', 'provider.leisure-resources.index', 'provider.leisure-packages.index', 'provider.leisure-passes.index'] as $r) {
            $this->actingAs($this->owner)->get(route($r))->assertOk();
        }
        $this->actingAs($this->owner)->post(route('provider.leisure-packages.store'), ['name' => 'X', 'kind' => 'group', 'weekday_price_xof' => 1, 'included_participants' => 1,
            'min_participants' => 1, 'duration_minutes' => 60, 'slot_times_text' => '25:00', 'free_cancellation_days' => 1])->assertSessionHasErrors('slot_times_text');
    }
}
