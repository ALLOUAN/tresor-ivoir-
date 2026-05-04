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
        $this->apiKey      = config('services.cinetpay.api_key');
        $this->apiPassword = config('services.cinetpay.api_password');
        $this->baseUrl     = rtrim(config('services.cinetpay.base_url'), '/');
        $this->currency    = config('services.cinetpay.currency', 'XOF');
    }

    public function isConfigured(): bool
    {
        return $this->apiKey !== '' && $this->apiPassword !== '' && $this->baseUrl !== '';
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
     * Obtenir un Bearer token via OAuth (/v1/oauth/login).
     * Mis en cache 23 h (expire en 24 h côté API).
     */
    protected function getAccessToken(): string
    {
        return Cache::remember('cinetpay_access_token', now()->addHours(23), function () {
            $response = Http::timeout(15)
                ->post("{$this->baseUrl}/v1/oauth/login", [
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

            if (($data['code'] ?? 0) !== 200 || empty($data['access_token'])) {
                Log::error('CinetPay OAuth unexpected response', $data);
                throw new \RuntimeException('Réponse CinetPay OAuth invalide.');
            }

            return $data['access_token'];
        });
    }

    /**
     * Générer un merchant_transaction_id unique (max 30 caractères).
     */
    protected function generateMerchantTransactionId(): string
    {
        return 'NV-' . strtoupper(Str::random(8)) . '-' . substr((string) time(), -8);
    }

    /**
     * Initialiser une transaction de paiement (POST /v1/payment).
     */
    public function initPayment(array $data): array
    {
        $merchantTxId = $this->generateMerchantTransactionId();

        $payload = [
            'currency'                => $data['currency'] ?? $this->currency,
            'merchant_transaction_id' => $merchantTxId,
            'amount'                  => (int) $data['amount'],
            'lang'                    => app()->getLocale() === 'en' ? 'en' : 'fr',
            'designation'             => $data['designation'] ?? config('app.name'),
            'client_first_name'       => $data['client_first_name'] ?? '',
            'client_last_name'        => $data['client_last_name'] ?? '',
            'client_email'            => $data['client_email'] ?? '',
            'success_url'             => Str::limit($data['success_url'], 120, ''),
            'failed_url'              => Str::limit($data['failed_url'], 120, ''),
            'notify_url'              => Str::limit($data['notify_url'], 120, ''),
            'direct_pay'              => false,
        ];

        if (!empty($data['client_phone_number'])) {
            $payload['client_phone_number'] = $data['client_phone_number'];
        }

        try {
            $token = $this->getAccessToken();

            $response = Http::timeout(30)
                ->withToken($token)
                ->post("{$this->baseUrl}/v1/payment", $payload);

            if ($response->failed()) {
                Log::error('CinetPay initPayment HTTP failed', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);

                return [
                    'success'                 => false,
                    'message'                 => 'Erreur de connexion au service de paiement.',
                    'merchant_transaction_id' => $merchantTxId,
                ];
            }

            $result = $response->json();
            $code   = $result['code'] ?? 0;

            if ($code === 200) {
                return [
                    'success'                 => true,
                    'payment_url'             => $result['payment_url'] ?? null,
                    'payment_token'           => $result['payment_token'] ?? null,
                    'notify_token'            => $result['notify_token'] ?? null,
                    'transaction_id'          => $result['transaction_id'] ?? null,
                    'merchant_transaction_id' => $result['merchant_transaction_id'] ?? $merchantTxId,
                    'details'                 => $result['details'] ?? null,
                    'message'                 => $result['status'] ?? 'OK',
                ];
            }

            // Token expiré — invalider le cache et retenter une fois
            if (in_array($code, [1002, 1003])) {
                Cache::forget('cinetpay_access_token');
                return $this->initPayment($data);
            }

            Log::warning('CinetPay initPayment error response', $result);

            return [
                'success'                 => false,
                'message'                 => $result['status'] ?? 'Erreur inconnue',
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
     * Vérifier le statut d'un paiement (GET /v1/payment/{payment_token}).
     */
    public function checkPaymentStatus(string $paymentToken): array
    {
        try {
            $token = $this->getAccessToken();

            $response = Http::timeout(15)
                ->withToken($token)
                ->get("{$this->baseUrl}/v1/payment/{$paymentToken}");

            if ($response->failed()) {
                Log::error('CinetPay checkPaymentStatus failed', [
                    'payment_token' => $paymentToken,
                    'status'        => $response->status(),
                ]);
                return ['success' => false, 'status' => 'ERROR'];
            }

            $result = $response->json();
            $code   = $result['code'] ?? 0;
            $status = $result['status'] ?? 'UNKNOWN';

            return [
                'success'                 => $code === 100 && $status === 'SUCCESS',
                'status'                  => $status,
                'code'                    => $code,
                'merchant_transaction_id' => $result['merchant_transaction_id'] ?? null,
                'transaction_id'          => $result['transaction_id'] ?? null,
                'user'                    => $result['user'] ?? null,
                'raw'                     => $result,
            ];
        } catch (\Throwable $e) {
            Log::error('CinetPay checkPaymentStatus exception', ['error' => $e->getMessage()]);
            return ['success' => false, 'status' => 'ERROR'];
        }
    }
}
