<?php

namespace App\Services;

use App\Mail\PaymentConfirmedMail;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\Mail;

class PaymentLifecycleService
{
    public function markAsCompleted(Payment $payment, ?string $gatewayTxnId = null): Payment
    {
        if ($payment->status === 'completed') {
            return $payment;
        }

        $payment->update([
            'status' => 'completed',
            'paid_at' => now(),
            'gateway_txn_id' => $gatewayTxnId ?: $payment->gateway_txn_id,
            'failed_at' => null,
            'failure_reason' => null,
        ]);

        $this->createInvoiceIfMissing($payment->fresh(['provider.user', 'subscription.plan']));
        $this->sendPaymentConfirmation($payment->fresh(['provider.user', 'subscription.plan', 'invoice']));

        return $payment->fresh();
    }

    public function markAsFailed(Payment $payment, ?string $reason = null): Payment
    {
        $payment->update([
            'status' => 'failed',
            'failed_at' => now(),
            'failure_reason' => $reason,
        ]);

        return $payment->fresh();
    }

    private function createInvoiceIfMissing(Payment $payment): void
    {
        if ($payment->invoice) {
            return;
        }

        $taxRate = 18.00;
        $amountTtc = (float) $payment->amount;
        $amountHt = round($amountTtc / (1 + ($taxRate / 100)), 2);
        $taxAmount = round($amountTtc - $amountHt, 2);

        Invoice::create([
            'number' => $this->generateInvoiceNumber(),
            'payment_id' => $payment->id,
            'provider_id' => $payment->provider_id,
            'amount_ht' => $amountHt,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'amount_ttc' => $amountTtc,
            'pdf_url' => null,
            'issued_at' => now(),
            'due_at' => now(),
        ]);
    }

    private function sendPaymentConfirmation(Payment $payment): void
    {
        $email = $payment->provider?->user?->email;

        if (! $email) {
            return;
        }

        Mail::to($email)->send(new PaymentConfirmedMail($payment));
    }

    private function generateInvoiceNumber(): string
    {
        $nextId = ((int) Invoice::max('id')) + 1;

        return 'TDI-'.now()->format('Y').'-'.str_pad((string) $nextId, 5, '0', STR_PAD_LEFT);
    }
}
