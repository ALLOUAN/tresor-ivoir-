<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CinetPayService
{
    protected string $apiKey;
    protected string $apiPassword;
    protected string $baseUrl;
    protected string $currency;

    public function __construct()
    {
        $this->apiKey      = (string) config('services.cynetpay.api_key', '');
        $this->apiPassword = (string) config('services.cynetpay.api_password', '');
        $this->baseUrl     = rtrim((string) config('services.cynetpay.base_url', 'https://api.cinetpay.co'), '/');
        $this->currency    = (string) config('services.cynetpay.currency', 'XOF');
    }

    public function isConfigured(): bool
    {
        return $this->apiKey !== '' && $this->apiPassword !== '';
    }

    /** @return array<int,array{code:string,label:string,icon:string,desc:string}> */
    public function getAvailableChannels(): array
    {
        return [
            ['code' => 'ALL',          'label' => 'Tous les moyens disponibles', 'icon' => 'fa-wallet',        'desc' => 'Orange Money, MTN, Wave, Carte…'],
            ['code' => 'MOBILE_MONEY', 'label' => 'Mobile Money',                'icon' => 'fa-mobile-screen', 'desc' => 'Orange Money, MTN MoMo, Wave, Moov'],
            ['code' => 'CREDIT_CARD',  'label' => 'Carte bancaire',              'icon' => 'fa-credit-card',   'desc' => 'Visa, Mastercard, CB'],
            ['code' => 'WALLET',       'label' => 'Portefeuille CinetPay',       'icon' => 'fa-coins',         'desc' => 'Solde portefeuille CinetPay'],
        ];
    }

    /**
     * Génère un merchant_transaction_id unique (max 30 caractères selon la doc).
     */
    protected function generateMerchantTransactionId(): string
    {
        return 'NV-' . strtoupper(Str::random(8)) . '-' . substr((string) time(), -8);
    }

    /**
     * Obtenir un Bearer token via POST /v1/oauth/login.
     * Mis en cache 23 h (expire en 24 h côté API).
     */
    protected function getAccessToken(): string
    {
        return Cache::remember('cinetpay_access_token', now()->addHours(23), function () {
            $response = Http::timeout(15)->post("{$this->baseUrl}/v1/oauth/login", [
                'api_key'      => $this->apiKey,
                'api_password' => $this->apiPassword,
            ]);

            if ($response->failed()) {
                Log::error('CinetPay OAuth login failed', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                throw new \RuntimeException('Impossible d\'obtenir le jeton CinetPay.');
            }

            $data = $response->json();

            if ((int) ($data['code'] ?? 0) !== 200 || empty($data['access_token'])) {
                Log::error('CinetPay OAuth unexpected response', $data);
                throw new \RuntimeException('Réponse OAuth CinetPay invalide.');
            }

            return $data['access_token'];
        });
    }

    /**
     * Initialiser une transaction (POST /v1/payment).
     *
     * Champs requis dans $data :
     *   amount, designation, client_first_name, client_last_name, client_email,
     *   success_url, failed_url, notify_url
     * Champs optionnels :
     *   client_phone_number, currency
     */
    public function initPayment(array $data): array
    {
        $merchantTxId = $this->generateMerchantTransactionId();

        $payload = [
            'currency'                => $data['currency'] ?? $this->currency,
            'merchant_transaction_id' => $merchantTxId,
            'amount'                  => (int) $data['amount'],
            'lang'                    => 'fr',
            'designation'             => $data['designation'] ?? config('app.name'),
            'client_email'            => $data['client_email'] ?? '',
            'client_first_name'       => $data['client_first_name'] ?? '',
            'client_last_name'        => $data['client_last_name'] ?? '',
            'success_url'             => $data['success_url'],
            'failed_url'              => $data['failed_url'] ?? $data['success_url'],
            'notify_url'              => $data['notify_url'],
            'direct_pay'              => false,
        ];

        if (! empty($data['client_phone_number'])) {
            $payload['client_phone_number'] = $data['client_phone_number'];
        }

        try {
            $token    = $this->getAccessToken();
            $response = Http::timeout(30)
                ->withToken($token)
                ->post("{$this->baseUrl}/v1/payment", $payload);

            $result = $response->json();
            $code   = (int) ($result['code'] ?? 0);

            // code 200 = transaction initiée avec succès
            if ($code === 200) {
                return [
                    'success'                 => true,
                    'payment_url'             => $result['payment_url'] ?? null,
                    'payment_token'           => $result['payment_token'] ?? null,
                    'notify_token'            => $result['notify_token'] ?? null,
                    'transaction_id'          => $result['transaction_id'] ?? null,
                    'merchant_transaction_id' => $merchantTxId,
                    'message'                 => $result['status'] ?? 'OK',
                ];
            }

            // Token expiré → invalider le cache et retenter une fois
            if (in_array($code, [1002, 1003])) {
                Cache::forget('cinetpay_access_token');
                return $this->initPayment($data);
            }

            Log::warning('CinetPay initPayment error', $result);

            return [
                'success'                 => false,
                'message'                 => $result['status'] ?? ('Erreur CinetPay code: ' . $code),
                'merchant_transaction_id' => $merchantTxId,
            ];
        } catch (\Throwable $e) {
            Log::error('CinetPay initPayment exception', ['error' => $e->getMessage()]);

            return [
                'success'                 => false,
                'message'                 => 'Erreur interne du service de paiement.',
                'merchant_transaction_id' => $merchantTxId,
            ];
        }
    }

    /**
     * Vérifier le statut d'un paiement (GET /v1/payment/{merchant_transaction_id}).
     * $merchantTransactionId = gateway_txn_id en base.
     * code 100 + status "SUCCESS" = paiement confirmé.
     */
    public function checkPaymentStatus(string $merchantTransactionId): array
    {
        try {
            $token    = $this->getAccessToken();
            $response = Http::timeout(15)
                ->withToken($token)
                ->get("{$this->baseUrl}/v1/payment/{$merchantTransactionId}");

            if ($response->failed()) {
                Log::error('CinetPay checkPaymentStatus failed', [
                    'merchant_transaction_id' => $merchantTransactionId,
                    'status'                  => $response->status(),
                ]);
                return ['success' => false, 'status' => 'ERROR'];
            }

            $result = $response->json();
            $code   = (int) ($result['code'] ?? 0);

            // Token expiré → retenter une fois
            if (in_array($code, [1002, 1003])) {
                Cache::forget('cinetpay_access_token');
                return $this->checkPaymentStatus($merchantTransactionId);
            }

            return [
                'success' => $code === 100,
                'status'  => $result['status'] ?? 'UNKNOWN',
                'code'    => $code,
                'raw'     => $result,
            ];
        } catch (\Throwable $e) {
            Log::error('CinetPay checkPaymentStatus exception', ['error' => $e->getMessage()]);
            return ['success' => false, 'status' => 'ERROR'];
        }
    }
}
