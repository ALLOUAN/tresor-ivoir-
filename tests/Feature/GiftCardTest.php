<?php

namespace Tests\Feature;

use App\Mail\GiftCardMail;
use App\Models\GiftCard;
use App\Models\Provider;
use App\Models\ProviderCategory;
use App\Models\Restaurant;
use App\Models\TouristCity;
use App\Models\User;
use App\Models\Wallet;
use App\Services\CinetPayService;
use App\Services\GiftCardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/** Bons cadeaux : achat (solde / CinetPay), envoi, validation à la caisse, expiration, argent. */
class GiftCardTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Provider $provider;

    private User $buyer;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Notification::fake();
        $this->owner = User::factory()->provider()->create();
        $cat = ProviderCategory::create(['slug' => 'restaurants', 'name_fr' => 'Restaurants', 'name_en' => 'Restaurants', 'is_active' => true]);
        $this->provider = Provider::create(['user_id' => $this->owner->id, 'category_id' => $cat->id, 'name' => 'Chez Tantie', 'slug' => 'chez-tantie', 'status' => 'active', 'gift_cards_enabled' => true]);
        $city = TouristCity::create(['name' => 'Abidjan', 'slug' => 'abidjan-'.uniqid(), 'is_active' => true]);
        Restaurant::create(['city_id' => $city->id, 'provider_id' => $this->provider->id, 'name' => 'Chez Tantie', 'slug' => 'chez-tantie', 'is_active' => true]);
        $this->buyer = User::factory()->create(['email' => 'buyer@example.ci']);
    }

    private function buy(string $method, int $amount = 25000)
    {
        return $this->actingAs($this->buyer)->post(route('gift-cards.store', 'chez-tantie'), [
            'amount_xof' => $amount, 'recipient_name' => 'Mariam', 'recipient_email' => 'mariam@example.ci',
            'message' => 'Joyeux anniversaire !', 'payment_method' => $method,
        ]);
    }

    public function test_buying_with_the_wallet_sends_the_card_and_keeps_the_provider_share_pending(): void
    {
        $this->get(route('restaurant.show', 'chez-tantie'))->assertSee('Offrir un bon cadeau');
        Wallet::forUser($this->buyer)->forceFill(['balance_available_xof' => 40000])->save();

        $this->buy('wallet')->assertSessionHas('success');

        $card = GiftCard::firstOrFail();
        $this->assertSame(GiftCard::STATUS_ACTIVE, $card->status);
        $this->assertTrue($card->expires_at->isSameDay(now()->addYear()));
        Mail::assertQueued(GiftCardMail::class, fn ($m) => $m->hasTo('mariam@example.ci') && $m->audience === 'recipient');
        Mail::assertQueued(GiftCardMail::class, fn ($m) => $m->hasTo('buyer@example.ci') && $m->audience === 'buyer');
        $this->assertSame(15000, (int) Wallet::forUser($this->buyer)->fresh()->balance_available_xof);
        $this->assertSame(22500, (int) Wallet::forProvider($this->provider)->fresh()->balance_pending_xof, '25 000 − 10 %');
    }

    public function test_redeeming_at_the_counter_releases_the_money_once(): void
    {
        Wallet::forUser($this->buyer)->forceFill(['balance_available_xof' => 25000])->save();
        $this->buy('wallet');
        $card = GiftCard::firstOrFail();

        $this->actingAs($this->owner)->get(route('provider.gift-cards.index', ['code' => $card->formattedCode()]))->assertOk()->assertSee('Mariam')->assertSee('Valider l');
        $this->actingAs($this->owner)->post(route('provider.gift-cards.redeem', $card))->assertSessionHas('success');
        $this->actingAs($this->owner)->post(route('provider.gift-cards.redeem', $card))->assertSessionHas('error');

        $w = Wallet::forProvider($this->provider)->fresh();
        $this->assertSame([22500, 0], [(int) $w->balance_available_xof, (int) $w->balance_pending_xof]);
        $this->assertSame(GiftCard::STATUS_REDEEMED, $card->fresh()->status);
    }

    public function test_cinetpay_purchase_is_activated_by_the_webhook_only_once(): void
    {
        $this->mock(CinetPayService::class, function ($m) {
            $m->shouldReceive('isConfigured')->andReturn(true);
            $m->shouldReceive('initPayment')->andReturn(['success' => true, 'merchant_transaction_id' => 'GC-1', 'payment_url' => 'https://pay.example/GC-1']);
            $m->shouldReceive('checkPaymentStatus')->andReturn(['success' => true]);
        });

        $this->buy('cinetpay', 50000)->assertRedirect('https://pay.example/GC-1');
        $this->assertSame(GiftCard::STATUS_PENDING, GiftCard::firstOrFail()->status);

        $this->post(route('gift-cards.payment.webhook'), ['merchant_transaction_id' => 'GC-1'])->assertOk();
        $this->actingAs($this->buyer)->get(route('gift-cards.payment.return', ['merchant_transaction_id' => 'GC-1']))->assertRedirect();

        $this->assertSame(GiftCard::STATUS_ACTIVE, GiftCard::firstOrFail()->status);
        Mail::assertQueued(GiftCardMail::class, 2);
        $this->assertSame(45000, (int) Wallet::forProvider($this->provider)->fresh()->balance_pending_xof);
    }

    public function test_expired_cards_go_to_the_provider_and_disabled_providers_hide_the_page(): void
    {
        Wallet::forUser($this->buyer)->forceFill(['balance_available_xof' => 25000])->save();
        $this->buy('wallet');
        $this->travel(13)->months();

        app(GiftCardService::class)->expireOverdue();

        $this->assertSame(GiftCard::STATUS_EXPIRED, GiftCard::firstOrFail()->status);
        $this->assertSame(22500, (int) Wallet::forProvider($this->provider)->fresh()->balance_available_xof);

        $this->actingAs($this->owner)->post(route('provider.gift-cards.toggle'));
        $this->get(route('gift-cards.show', 'chez-tantie'))->assertNotFound();
    }
}
