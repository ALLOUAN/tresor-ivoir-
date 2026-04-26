<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class CynetPayService
{
    private string $apiKey;
    private string $siteId;
    private string $endpoint;
    private string $checkEndpoint;

    public function __construct()
    {
        $this->apiKey        = (string) config('services.cinetpay.api_key', '');
        $this->siteId        = (string) config('services.cinetpay.site_id', '');
        $this->endpoint      = (string) config('services.cinetpay.endpoint', 'https://api-checkout.cinetpay.com/v2/payment');
        $this->checkEndpoint = (string) config('services.cinetpay.check_endpoint', 'https://api-checkout.cinetpay.com/v2/payment/check');
    }

    public function isConfigured(): bool
    {
        return $this->apiKey !== '' && $this->siteId !== '';
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

    /** @param array<string,mixed> $data */
    public function initiatePayment(array $data): array
    {
        try {
            $response = Http::timeout(15)->post($this->endpoint, [
                'apikey'                => $this->apiKey,
                'site_id'              => $this->siteId,
                'transaction_id'       => $data['transaction_id'],
                'amount'               => (int) $data['amount'],
                'currency'             => $data['currency'] ?? 'XOF',
                'description'          => $data['description'] ?? 'Paiement abonnement',
                'return_url'           => $data['return_url'],
                'notify_url'           => $data['notify_url'],
                'channels'             => $data['channel'] ?? 'ALL',
                'customer_name'        => $data['customer_surname'] ?? '',
                'customer_surname'     => $data['customer_name'] ?? '',
                'customer_email'       => $data['customer_email'] ?? '',
                'customer_phone_number' => $data['customer_phone'] ?? '',
                'customer_address'     => $data['customer_address'] ?? 'Abidjan',
                'customer_city'        => $data['customer_city'] ?? 'Abidjan',
                'customer_country'     => 'CI',
                'customer_state'       => 'CI',
                'customer_zip_code'    => '00225',
            ]);

            if (! $response->ok()) {
                return ['success' => false, 'message' => 'Erreur de connexion à CinetPay (HTTP ' . $response->status() . ').'];
            }

            $result = $response->json();
            $code   = (string) ($result['code'] ?? '');

            // CinetPay returns code "201" when payment is successfully created (pending user action)
            if ($code === '201') {
                return [
                    'success'        => true,
                    'payment_url'    => $result['data']['payment_url'] ?? '',
                    'payment_token'  => $result['data']['payment_token'] ?? '',
                    'transaction_id' => $data['transaction_id'],
                ];
            }

            return [
                'success' => false,
                'message' => $result['message'] ?? ('Erreur CinetPay : code ' . $code),
                'code'    => $code,
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Erreur réseau : ' . $e->getMessage()];
        }
    }

    public function checkPaymentStatus(string $transactionId): array
    {
        try {
            $response = Http::timeout(15)->post($this->checkEndpoint, [
                'apikey'         => $this->apiKey,
                'site_id'        => $this->siteId,
                'transaction_id' => $transactionId,
            ]);

            if (! $response->ok()) {
                return ['status' => 'ERROR', 'message' => 'Impossible de vérifier le statut.'];
            }

            $data = $response->json();
            $code = (string) ($data['code'] ?? '');

            // CinetPay status codes: '00' = success, '600' = pending
            $status = match (true) {
                $code === '00'  => 'PAYMENT_SUCCESS',
                $code === '600' => 'PAYMENT_PENDING',
                default         => 'PAYMENT_FAILED',
            };

            return [
                'status'         => $status,
                'code'           => $code,
                'message'        => $data['message'] ?? '',
                'transaction_id' => $transactionId,
            ];
        } catch (\Throwable) {
            return ['status' => 'ERROR', 'message' => 'Erreur lors de la vérification.'];
        }
    }
}
