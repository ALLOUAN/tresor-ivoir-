<?php

namespace Tests\Feature;

use App\Models\Provider;
use App\Models\ProviderCategory;
use App\Models\TouristCity;
use App\Models\TouristExperience;
use App\Models\TouristVisit;
use App\Models\TouristVisitType;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Types de visite « marché » : créneaux à places limitées, tarifs par catégorie, annulation gratuite,
 * paiement sur place, fiche détaillée, tarif week-end / heures creuses, options payantes.
 */
class TouristVisitMarketFeaturesTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private TouristExperience $site;

    private User $client;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        $this->owner = User::factory()->provider()->create();
        $cat = ProviderCategory::firstOrCreate(['slug' => 'sites-touristiques'], ['name_fr' => 'Sites Touristiques', 'name_en' => 'Tourist Sites', 'is_active' => true]);
        $provider = Provider::create(['user_id' => $this->owner->id, 'category_id' => $cat->id, 'name' => 'Musée', 'slug' => 'musee-'.uniqid(), 'status' => 'active']);
        $city = TouristCity::create(['name' => 'Grand-Bassam', 'slug' => 'gb-'.uniqid(), 'is_active' => true]);
        $this->site = TouristExperience::create(['provider_id' => $provider->id, 'city_id' => $city->id, 'name' => 'Musée du Costume', 'slug' => 'musee-costume', 'is_active' => true]);
        $this->client = User::factory()->create();
        Wallet::forUser($this->client)->update(['balance_available_xof' => 200000]);
    }

    /** Prochain mardi (jour de semaine, sans tarif week-end). */
    private function weekday(): string
    {
        return Carbon::now('Africa/Abidjan')->next(Carbon::TUESDAY)->toDateString();
    }

    private function type(array $attrs = []): TouristVisitType
    {
        return $this->site->visitTypes()->create($attrs + [
            'name' => 'Visite guidée', 'price_xof' => 3000, 'is_active' => true,
            'price_tiers' => [['label' => 'Adulte', 'price_xof' => 3000], ['label' => 'Enfant', 'price_xof' => 1000], ['label' => 'Moins de 4 ans', 'price_xof' => 0]],
            'slot_times' => ['09:00', '10:00', '14:00'], 'slot_capacity' => 5,
        ]);
    }

    private function book(TouristVisitType $type, array $o)
    {
        return $this->actingAs($this->client)->postJson(route('tourist-visit.pay', $this->site->slug), $o + [
            'visit_type' => 'custom', 'visit_type_id' => $type->id, 'participants_count' => 1,
            'full_name' => 'Awa Koné', 'email' => 'awa@example.ci', 'payment_method' => 'wallet',
        ]);
    }

    public function test_slots_show_remaining_places_and_a_full_slot_is_refused(): void
    {
        $type = $this->type();
        $date = $this->weekday();

        $this->book($type, ['desired_date' => $date, 'visit_time' => '09:00', 'tiers' => [3, 1, 0]])->assertOk()->assertJson(['success' => true]);
        $visit = TouristVisit::sole();
        $this->assertSame(4, $visit->participants_count);
        $this->assertSame('09:00', $visit->visit_time);
        $this->assertSame(10000, $visit->amount_total_xof); // 3 × 3 000 + 1 × 1 000
        $this->assertSame('3 × Adulte, 1 × Enfant', $visit->tiersSummary());

        $slots = collect($this->getJson(route('tourist-visit.slots', [$this->site->slug, 'type' => $type->id, 'date' => $date]))->assertOk()->json('slots'))->keyBy('time');
        $this->assertSame(1, $slots['09:00']['remaining']);
        $this->assertSame(5, $slots['10:00']['remaining']);

        // 2 personnes sur un créneau à 1 place : refusé ; créneau inconnu ou absent : refusé.
        $this->book($type, ['desired_date' => $date, 'visit_time' => '09:00', 'tiers' => [2, 0, 0]])->assertStatus(422)->assertJsonFragment(['message' => 'Il ne reste que 1 place(s) sur ce créneau.']);
        $this->book($type, ['desired_date' => $date, 'visit_time' => '11:00', 'tiers' => [1, 0, 0]])->assertStatus(422);
        $this->book($type, ['desired_date' => $date, 'tiers' => [1, 0, 0]])->assertStatus(422);
        $this->book($type, ['visit_time' => '10:00', 'tiers' => [1, 0, 0]])->assertStatus(422);
        $this->book($type, ['desired_date' => $date, 'visit_time' => '10:00', 'tiers' => [0, 0, 0]])->assertStatus(422);

        // La dernière place part ; le créneau devient complet.
        $this->book($type, ['desired_date' => $date, 'visit_time' => '09:00', 'tiers' => [1, 0, 0]])->assertOk();
        $full = collect($this->getJson(route('tourist-visit.slots', [$this->site->slug, 'type' => $type->id, 'date' => $date]))->json('slots'))->firstWhere('time', '09:00');
        $this->assertSame(0, $full['remaining']);
        $this->assertFalse($full['available']);
    }

    public function test_a_cancelled_visit_frees_its_places(): void
    {
        $type = $this->type(['slot_capacity' => 2]);
        $date = $this->weekday();
        $this->book($type, ['desired_date' => $date, 'visit_time' => '14:00', 'tiers' => [2, 0, 0]])->assertOk();
        $this->book($type, ['desired_date' => $date, 'visit_time' => '14:00', 'tiers' => [1, 0, 0]])->assertStatus(422);

        TouristVisit::sole()->update(['status' => TouristVisit::STATUS_CANCELLED]);
        $this->book($type, ['desired_date' => $date, 'visit_time' => '14:00', 'tiers' => [1, 0, 0]])->assertOk();
    }

    public function test_weekend_and_off_peak_prices_and_paid_options(): void
    {
        $type = $this->type([
            'weekend_adjust_percent' => 20, 'offpeak_until' => '10:00', 'offpeak_discount_percent' => 10,
            'includes_guide' => true, 'guide_supplement_xof' => 2000,
            'addons' => [['label' => 'Audioguide', 'price_xof' => 1000, 'per' => 'person'], ['label' => 'Pack photos', 'price_xof' => 5000, 'per' => 'booking']],
        ]);
        $saturday = Carbon::now('Africa/Abidjan')->next(Carbon::SATURDAY)->toDateString();

        // Samedi 09:00 : +20 % week-end −10 % heures creuses = +10 % → adulte 3 300.
        $this->book($type, ['desired_date' => $saturday, 'visit_time' => '09:00', 'tiers' => [2, 0, 0], 'addons' => [0, 1]])->assertOk();
        $v = TouristVisit::sole();
        $this->assertSame(10, $v->price_adjust_percent);
        // 2 × 3 300 + audioguide 2 × 1 000 + photos 5 000 + guide 2 000
        $this->assertSame(6600 + 2000 + 5000 + 2000, $v->amount_total_xof);
        $this->assertSame(['Audioguide', 'Pack photos'], collect($v->addons)->pluck('label')->all());

        $slots = collect($this->getJson(route('tourist-visit.slots', [$this->site->slug, 'type' => $type->id, 'date' => $this->weekday()]))->json('slots'))->keyBy('time');
        $this->assertSame(-10, $slots['09:00']['adjust_percent']);
        $this->assertSame(0, $slots['14:00']['adjust_percent']);
    }

    public function test_pay_on_site_issues_a_ticket_that_is_cashed_at_check_in(): void
    {
        $type = $this->type(['allow_pay_on_site' => true]);
        $today = now('Africa/Abidjan')->toDateString();
        $type->update(['slot_times' => ['23:59']]);

        $this->book($type, ['desired_date' => $today, 'visit_time' => '23:59', 'tiers' => [1, 1, 0], 'payment_method' => 'on_site'])
            ->assertOk()->assertJson(['success' => true]);
        $v = TouristVisit::sole();
        $this->assertSame(TouristVisit::STATUS_BOOKED, $v->status);
        $this->assertTrue($v->pay_on_site);
        $this->assertSame(200000, Wallet::forUser($this->client)->fresh()->balance_available_xof, 'Rien n\'est débité.');
        $this->assertNotNull($v->ticketCode());
        $this->get($v->confirmationUrl())->assertOk()->assertSee('À régler sur place');

        // Au scan : billet valable « à encaisser », l'entrée vaut paiement, sans commission plateforme.
        $this->actingAs($this->owner)->get(route('provider.tickets.show', $v->ticket_code))->assertOk()->assertSee('à encaisser');
        $this->actingAs($this->owner)->post(route('provider.tickets.check-in', $v->ticket_code))->assertRedirect();
        $v->refresh();
        $this->assertSame(TouristVisit::STATUS_PAID, $v->status);
        $this->assertSame('on_site', $v->gateway);
        $this->assertSame(0, $v->commission_amount_xof);
        $this->assertNotNull($v->checked_in_at);

        // Type sans paiement sur place : refusé.
        $other = $this->type(['name' => 'Sans sur place']);
        $this->book($other, ['desired_date' => $this->weekday(), 'visit_time' => '10:00', 'tiers' => [1, 0, 0], 'payment_method' => 'on_site'])->assertStatus(422);
    }

    public function test_free_cancellation_refunds_to_the_wallet_until_the_deadline(): void
    {
        $type = $this->type(['free_cancellation_hours' => 24]);
        $date = Carbon::now('Africa/Abidjan')->addDays(5)->toDateString();
        $this->book($type, ['desired_date' => $date, 'visit_time' => '10:00', 'tiers' => [2, 0, 0]])->assertOk();
        $v = TouristVisit::sole();
        $this->assertSame(194000, Wallet::forUser($this->client)->fresh()->balance_available_xof);
        $this->assertTrue($v->isCancellableByClient());
        $this->assertSame(Carbon::parse($date.' 10:00', 'Africa/Abidjan')->subDay()->utc()->toDateTimeString(), $v->cancellable_until->utc()->toDateTimeString());

        $this->actingAs($this->client)->get(route('visitor.tourist-visits.index'))->assertOk()->assertSee('Annuler gratuitement')->assertSee('Musée du Costume');
        $this->actingAs($this->client)->post(route('visitor.tourist-visits.cancel', $v))->assertRedirect()->assertSessionHas('success');
        $this->assertSame(TouristVisit::STATUS_REFUNDED, $v->fresh()->status);
        $this->assertSame(200000, Wallet::forUser($this->client)->fresh()->balance_available_xof);

        // Une autre personne ne peut pas annuler ; délai dépassé : refusé.
        $this->book($type, ['desired_date' => $date, 'visit_time' => '14:00', 'tiers' => [1, 0, 0]])->assertOk();
        $late = TouristVisit::latest('id')->first();
        $this->actingAs(User::factory()->create())->post(route('visitor.tourist-visits.cancel', $late))->assertForbidden();
        $late->update(['cancellable_until' => now()->subMinute()]);
        $this->actingAs($this->client)->post(route('visitor.tourist-visits.cancel', $late))->assertSessionHas('error');
        $this->assertSame(TouristVisit::STATUS_PAID, $late->fresh()->status);
    }

    public function test_without_free_cancellation_the_visit_cannot_be_cancelled_online(): void
    {
        $type = $this->type(); // non remboursable
        $this->book($type, ['desired_date' => $this->weekday(), 'visit_time' => '10:00', 'tiers' => [1, 0, 0]])->assertOk();
        $v = TouristVisit::sole();
        $this->assertNull($v->cancellable_until);
        $this->assertFalse($v->isCancellableByClient());
    }

    public function test_language_must_be_offered_and_details_are_shown(): void
    {
        $type = $this->type([
            'languages' => ['Français', 'English'], 'meeting_point' => 'Devant la billetterie',
            'included' => "Entrée\nGuide", 'to_bring' => 'Chapeau', 'free_cancellation_hours' => 24, 'allow_pay_on_site' => true,
        ]);
        $this->book($type, ['desired_date' => $this->weekday(), 'visit_time' => '10:00', 'tiers' => [1, 0, 0], 'language' => 'Deutsch'])->assertStatus(422);
        $this->book($type, ['desired_date' => $this->weekday(), 'visit_time' => '10:00', 'tiers' => [1, 0, 0], 'language' => 'English'])->assertOk();
        $this->assertSame('English', TouristVisit::sole()->language);

        $this->actingAs($this->client)->get(route('tourist-experience.show', $this->site->slug))->assertOk()
            ->assertSee('Annulation gratuite jusqu\'à 24 h avant')->assertSee('Payer sur place possible')
            ->assertSee('Devant la billetterie')->assertSee('dès 1 000 FCFA')
            ->assertSee('id="tvp-slots"', false);
        $this->get(TouristVisit::sole()->confirmationUrl())->assertSee('Devant la billetterie')->assertSee('English');
    }

    public function test_type_without_grid_keeps_the_site_child_and_resident_rates(): void
    {
        $this->site->update(['visit_child_price_xof' => 500, 'visit_resident_price_xof' => 1500]);
        $type = $this->site->visitTypes()->create(['name' => 'Entrée libre', 'price_xof' => 2000, 'is_active' => true]);
        $this->assertSame(['Adulte', 'Enfant', 'Résident'], collect($type->tiers())->pluck('label')->all());

        $this->book($type, ['desired_date' => $this->weekday(), 'tiers' => [1, 2, 1]])->assertOk();
        $this->assertSame(2000 + 1000 + 1500, TouristVisit::sole()->amount_total_xof);
    }

    public function test_provider_saves_all_market_settings_from_the_form(): void
    {
        $this->actingAs($this->owner)->post(route('provider.tourist-experience.visit-types.store'), [
            'name' => 'Visite VIP', 'price_xof' => 10000, 'is_active' => 1,
            'tiers' => [['label' => 'Adulte', 'price_xof' => 10000], ['label' => '', 'price_xof' => ''], ['label' => 'Enfant', 'price_xof' => 4000]],
            'slot_times_text' => '14h, 09:00 10:30, 9:00', 'slot_capacity' => 12,
            'free_cancellation_hours' => 24, 'allow_pay_on_site' => 1,
            'languages' => ['Français', 'English'], 'languages_other' => 'Baoulé, Bété',
            'included' => "Entrée\nGuide", 'meeting_point' => 'Billetterie', 'accessibility' => 'Fauteuil roulant', 'to_bring' => 'Eau',
            'weekend_adjust_percent' => 15, 'offpeak_until' => '11:00', 'offpeak_discount_percent' => 20,
            'addons' => [['label' => 'Audioguide', 'price_xof' => 1000, 'per' => 'person'], ['label' => '', 'price_xof' => '']],
        ])->assertRedirect()->assertSessionHasNoErrors();

        $t = TouristVisitType::sole();
        $this->assertEquals([['label' => 'Adulte', 'price_xof' => 10000], ['label' => 'Enfant', 'price_xof' => 4000]], $t->price_tiers);
        $this->assertSame(['09:00', '10:30', '14:00'], $t->slot_times);
        $this->assertSame(['Français', 'English', 'Baoulé', 'Bété'], $t->languages);
        $this->assertSame(24, $t->free_cancellation_hours);
        $this->assertTrue($t->allow_pay_on_site);
        $this->assertEquals([['label' => 'Audioguide', 'price_xof' => 1000, 'per' => 'person']], $t->addons);
        $this->assertSame(20, $t->offpeak_discount_percent);

        $this->actingAs($this->owner)->get(route('provider.tourist-experience.visit-types.edit', $t))->assertOk()
            ->assertSee('09:00, 10:30, 14:00')->assertSee('Audioguide')->assertSee('Bété');

        // Créneau mal saisi, tarif sans prix : refusés.
        $this->actingAs($this->owner)->put(route('provider.tourist-experience.visit-types.update', $t), ['name' => 'X', 'slot_times_text' => '25:00'])->assertSessionHasErrors('slot_times_text');
        $this->actingAs($this->owner)->put(route('provider.tourist-experience.visit-types.update', $t), ['name' => 'X', 'tiers' => [['label' => 'Senior', 'price_xof' => '']]])->assertSessionHasErrors('tiers');
    }
}
