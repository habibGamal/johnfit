<?php

namespace App\Listeners;

use App\Models\Payment;
use Asciisd\Kashier\Events\KashierWebhookHandled;
use Illuminate\Contracts\Queue\ShouldQueue;

class HandleKashierWebhook implements ShouldQueue
{
    public function handle(KashierWebhookHandled $event): void
    {
        $data = $event->payload;
        $orderId = $data['merchantOrderId'] ?? null;
        $paymentStatus = $data['paymentStatus'] ?? null;

        if (! $orderId) {
            return;
        }

        $payment = Payment::where('transaction_id', $orderId)->first();

        if (! $payment) {
            return;
        }

        if ($paymentStatus === 'SUCCESS' && $payment->status !== 'paid') {
            $payment->update([
                'status' => 'paid',
                'payment_method' => $data['paymentMethod'] ?? null,
                'gateway_response' => $data,
            ]);

            $plan = $payment->subscription->plan;

            $payment->subscription->update([
                'status' => 'active',
                'start_date' => now(),
                'end_date' => now()->addDays($plan->duration_days),
            ]);
        } elseif (in_array($paymentStatus, ['FAILED', 'CANCELLED', 'REJECTED']) && $payment->status === 'pending') {
            $payment->update([
                'status' => 'failed',
                'gateway_response' => $data,
            ]);

            $payment->subscription->update(['status' => 'cancelled']);
        }
    }
}
