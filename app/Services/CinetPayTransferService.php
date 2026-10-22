<?php

namespace App\Services;

use App\Models\PayoutRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Client minimal de l'API de transfert CinetPay (versements mobile money sortants).
 * Flux : authentification -> enregistrement du contact -> envoi -> vérification du statut.
 * Inactif tant que services.cinetpay_transfer.enabled est faux ou que les identifiants manquent.
 */
class CinetPayTransferService
{
    public const RESULT_PAID = 'paid';

    public const RESULT_PENDING = 'pending';

    public const RESULT_FAILED = 'failed';

    public function isEnabled(): bool
    {
        $config = config('services.cinetpay_transfer');

        return ! empty($config['enabled']) && ! empty($config['api_key']) && ! empty($config['password']);
    }

    /** Un retrait peut-il partir automatiquement ? (mobile money, montant sous le plafond, multiple de 5) */
    public function supports(PayoutRequest $payout): bool
    {
        return $payout->method !== PayoutRequest::METHOD_BANK_TRANSFER
            && $payout->amount_xof > 0
            && $payout->amount_xof <= (int) config('services.cinetpay_transfer.max_auto_amount_xof', 500000)
            && $payout->amount_xof % 5 === 0
            && $this->splitPhone((string) $payout->payout_destination) !== null;
    }

    /** Identifiant transmis à CinetPay, stable pour un retrait (idempotence côté opérateur). */
    public function clientTransactionId(PayoutRequest $payout): string
    {
        return 'TI-PAYOUT-'.$payout->id;
    }

    /**
     * Envoie le virement. Retourne la référence CinetPay.
     *
     * @throws \RuntimeException en cas de refus de l'opérateur
     */
    public function send(PayoutRequest $payout, string $recipientName, ?string $recipientEmail = null): string
    {
        [$prefix, $phone] = $this->splitPhone((string) $payout->payout_destination)
            ?? throw new \RuntimeException('Numéro de destination invalide.');
        $token = $this->token();
        [$surname, $name] = array_pad(explode(' ', trim($recipientName) ?: 'Prestataire', 2), 2, '-');

        $contact = $this->post('transfer/contact', $token, [[
            'prefix' => $prefix, 'phone' => $phone, 'name' => $name, 'surname' => $surname,
            'email' => $recipientEmail ?: 'versements@tresorsivoire.com',
        ]]);
        // Code 0 = créé ; un contact déjà connu n'est pas une erreur bloquante.
        if (! in_array((int) ($contact['code'] ?? -1), [0, 726], true)) {
            throw new \RuntimeException('Contact refusé par CinetPay : '.($contact['message'] ?? 'erreur inconnue'));
        }

        $send = $this->post('transfer/money/send/contact', $token, [[
            'prefix' => $prefix, 'phone' => $phone, 'amount' => (int) $payout->amount_xof,
            'client_transaction_id' => $this->clientTransactionId($payout),
            'notify_url' => route('home'),
        ]]);
        if ((int) ($send['code'] ?? -1) !== 0) {
            throw new \RuntimeException('Virement refusé par CinetPay : '.($send['message'] ?? 'erreur inconnue'));
        }

        $row = $send['data'][0][0] ?? $send['data'][0] ?? [];

        return (string) ($row['transaction_id'] ?? $this->clientTransactionId($payout));
    }

    /** Statut d'un virement déjà envoyé. */
    public function check(PayoutRequest $payout): string
    {
        $response = Http::asForm()->timeout(30)->get($this->url('transfer/check/money'), [
            'token' => $this->token(),
            'client_transaction_id' => $this->clientTransactionId($payout),
        ])->json() ?? [];

        $status = strtoupper((string) ($response['data'][0]['treatment_status'] ?? ''));

        return match ($status) {
            'VAL' => self::RESULT_PAID,
            'REJ' => self::RESULT_FAILED,
            default => self::RESULT_PENDING,
        };
    }

    /**
     * Sépare un numéro ivoirien en [indicatif, numéro local] : 0700000000, +2250700000000, 2250700000000.
     *
     * @return array{0: string, 1: string}|null
     */
    public function splitPhone(string $raw): ?array
    {
        $digits = preg_replace('/\D+/', '', $raw);
        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }
        if (str_starts_with($digits, '225') && strlen($digits) === 13) {
            return ['225', substr($digits, 3)];
        }
        if (strlen($digits) === 10) {
            return ['225', $digits];
        }

        return null;
    }

    private function token(): string
    {
        return Cache::remember('cinetpay_transfer_token', now()->addMinutes(4), function () {
            $response = Http::asForm()->timeout(30)->post($this->url('auth/login'), [
                'apikey' => config('services.cinetpay_transfer.api_key'),
                'password' => config('services.cinetpay_transfer.password'),
            ])->json() ?? [];

            $token = $response['data']['token'] ?? null;
            if ((int) ($response['code'] ?? -1) !== 0 || ! $token) {
                throw new \RuntimeException('Authentification CinetPay (transfert) refusée : '.($response['message'] ?? 'réponse vide'));
            }

            return $token;
        });
    }

    private function post(string $path, string $token, array $data): array
    {
        return Http::asForm()->timeout(30)
            ->post($this->url($path).'?'.http_build_query(['token' => $token, 'lang' => 'fr']), ['data' => json_encode($data)])
            ->json() ?? [];
    }

    private function url(string $path): string
    {
        return rtrim((string) config('services.cinetpay_transfer.base_url'), '/').'/'.$path;
    }
}
