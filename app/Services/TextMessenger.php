<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Messages courts (SMS ou WhatsApp) aux clients et prestataires sur les événements clés.
 * Pilote : services.text_messages.driver = off (défaut) | log | twilio.
 * Ne bloque jamais le parcours : toute erreur d'envoi est journalisée puis ignorée.
 */
class TextMessenger
{
    public function enabled(): bool
    {
        return in_array(config('services.text_messages.driver'), ['log', 'twilio'], true);
    }

    /** Envoi différé après la réponse HTTP, pour ne pas ralentir la page. */
    public function sendLater(?string $phone, string $message): void
    {
        if (! $this->enabled() || ! $this->normalize($phone)) {
            return;
        }

        dispatch(fn () => app(self::class)->send($phone, $message))->afterResponse();
    }

    public function send(?string $phone, string $message): bool
    {
        $to = $this->normalize($phone);
        if (! $this->enabled() || ! $to) {
            return false;
        }

        $message = mb_substr(trim($message), 0, 600);

        try {
            if (config('services.text_messages.driver') === 'log') {
                Log::info('[SMS/WhatsApp] '.$to.' : '.$message);

                return true;
            }

            $sid = (string) config('services.text_messages.twilio_sid');
            $channel = config('services.text_messages.channel') === 'whatsapp' ? 'whatsapp:' : '';
            $response = Http::asForm()->timeout(15)
                ->withBasicAuth($sid, (string) config('services.text_messages.twilio_token'))
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                    'From' => $channel.config('services.text_messages.from'),
                    'To' => $channel.$to,
                    'Body' => $message,
                ]);

            if ($response->failed()) {
                Log::warning('Envoi SMS/WhatsApp refusé', ['to' => $to, 'status' => $response->status(), 'body' => mb_substr($response->body(), 0, 300)]);

                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::warning('Envoi SMS/WhatsApp impossible', ['to' => $to, 'error' => $e->getMessage()]);

            return false;
        }
    }

    /** Format international E.164 ; un numéro local ivoirien à 10 chiffres reçoit l'indicatif +225. */
    public function normalize(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);
        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }
        if (strlen($digits) === 10) {
            $digits = '225'.$digits;
        }

        return strlen($digits) >= 11 && strlen($digits) <= 15 ? '+'.$digits : null;
    }
}
