<?php

namespace Tests\Feature;

use App\Services\TextMessenger;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/** SMS / WhatsApp : désactivé par défaut, format des numéros, envoi Twilio, jamais bloquant. */
class TextMessengerTest extends TestCase
{
    public function test_disabled_by_default_and_numbers_are_normalized(): void
    {
        Http::fake();
        $sms = app(TextMessenger::class);

        $this->assertFalse($sms->send('0707070707', 'test'));
        Http::assertNothingSent();

        $this->assertSame('+2250707070707', $sms->normalize('07 07 07 07 07'));
        $this->assertSame('+2250707070707', $sms->normalize('+225 07 07 07 07 07'));
        $this->assertSame('+33612345678', $sms->normalize('0033612345678'));
        $this->assertNull($sms->normalize('123'));
    }

    public function test_whatsapp_through_twilio_and_failures_never_throw(): void
    {
        config(['services.text_messages' => [
            'driver' => 'twilio', 'channel' => 'whatsapp', 'from' => '+14155238886', 'twilio_sid' => 'AC1', 'twilio_token' => 't',
        ]]);
        Http::fake(['api.twilio.com/*' => Http::sequence()->push(['sid' => 'SM1'], 201)->push(['message' => 'err'], 400)]);
        $sms = app(TextMessenger::class);

        $this->assertTrue($sms->send('0707070707', 'Réservation confirmée'));
        Http::assertSent(fn ($r) => $r['To'] === 'whatsapp:+2250707070707' && $r['From'] === 'whatsapp:+14155238886');

        $this->assertFalse($sms->send('0707070707', 'Deuxième'));
    }
}
