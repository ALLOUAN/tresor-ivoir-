<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Provider;
use App\Models\Subscription;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PaymentGatewayService
{
    public function initiate(
        string $gateway,
        Provider $provider,
        Subscription $subscription,
        float $amount,
        string $method,
        string $callbackUrl
    ): array {
        $payment = Payment::create([
            'subscription_id' => $subscription->id,
            'provider_id' => $provider->id,
            'amount' => $amount,
            'currency' => 'XOF',
            'method' => $method,
            'gateway' => $gateway,
            'status' => 'pending',
            'ip_address' => request()->ip(),
            'metadata' => [
                'subscription_uuid' => $subscription->uuid,
                'provider_uuid' => $provider->uuid,
            ],
        ]);

        if ($gateway === 'cinetpay') {
            return $this->initiateCinetPay($payment, $callbackUrl);
        }

        if ($gateway === 'stripe') {
            return $this->initiateStripe($payment, $callbackUrl);
        }

        if ($gateway === 'paypal') {
            return $this->initiatePaypal($payment, $callbackUrl);
        }

        return $this->simulateHostedPage($payment, $callbackUrl);
    }

    private function initiateCinetPay(Payment $payment, string $callbackUrl): array
    {
        $apiKey = (string) env('CINETPAY_API_KEY', '');
        $siteId = (string) env('CINETPAY_SITE_ID', '');
        $endpoint = (string) env('CINETPAY_ENDPOINT', 'https://api-checkout.cinetpay.com/v2/payment');

        if ($apiKey === '' || $siteId === '') {
            return $this->simulateHostedPage($payment, $callbackUrl);
        }

        $response = Http::post($endpoint, [
            'apikey' => $apiKey,
            'site_id' => $siteId,
            'transaction_id' => $payment->uuid,
            'amount' => (int) $payment->amount,
            'currency' => $payment->currency,
            'description' => 'Paiement abonnement',
            'return_url' => $callbackUrl.'?status=success&payment_uuid='.$payment->uuid,
            'notify_url' => route('provider.billing.webhook', ['gateway' => 'cinetpay']),
            'channels' => 'ALL',
        ]);

        if (! $response->ok()) {
            return $this->simulateHostedPage($payment, $callbackUrl);
        }

        $data = $response->json();
        $paymentUrl = $data['data']['payment_url'] ?? null;

        if (! $paymentUrl) {
            return $this->simulateHostedPage($payment, $callbackUrl);
        }

        return [
            'payment' => $payment,
            'payment_url' => $paymentUrl,
        ];
    }

    private function initiateStripe(Payment $payment, string $callbackUrl): array
    {
        $secret = (string) env('STRIPE_SECRET', '');

        if ($secret === '') {
            return $this->simulateHostedPage($payment, $callbackUrl);
        }

        return $this->simulateHostedPage($payment, $callbackUrl);
    }

    private function initiatePaypal(Payment $payment, string $callbackUrl): array
    {
        $clientId = (string) env('PAYPAL_CLIENT_ID', '');
        $clientSecret = (string) env('PAYPAL_CLIENT_SECRET', '');

        if ($clientId === '' || $clientSecret === '') {
            return $this->simulateHostedPage($payment, $callbackUrl);
        }

        return $this->simulateHostedPage($payment, $callbackUrl);
    }

    private function simulateHostedPage(Payment $payment, string $callbackUrl): array
    {
        return [
            'payment' => $payment,
            'payment_url' => $callbackUrl.'?status=success&payment_uuid='.$payment->uuid.'&txn='.Str::random(10),
        ];
    }
}
