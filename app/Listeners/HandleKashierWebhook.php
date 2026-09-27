<?php

namespace App\Listeners;

use App\Models\Payment;
use Asciisd\Kashier\Events\KashierWebhookHandled;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HandleKashierWebhook implements ShouldQueue
{
    public function handle(KashierWebhookHandled $event): void
    {
        $payload = $event->payload ?? [];

        // Kashier webhook delivers the transaction details under the 'data' key,
        // while direct event dispatches or unit tests may pass a flat array.
        $data = (isset($payload['data']) && is_array($payload['data']))
            ? $payload['data']
            : $payload;

        $orderId = $data['merchantOrderId']
            ?? $payload['merchantOrderId']
            ?? $data['orderReference']
            ?? $payload['orderReference']
            ?? null;

        $rawStatus = $data['status']
            ?? $data['paymentStatus']
            ?? $payload['status']
            ?? $payload['paymentStatus']
            ?? null;

        $paymentStatus = $rawStatus ? strtoupper((string) $rawStatus) : null;

        $paymentMethod = $data['method']
            ?? $data['paymentMethod']
            ?? $payload['method']
            ?? $payload['paymentMethod']
            ?? null;

        Log::info('HandleKashierWebhook: Processing webhook', [
            'orderId' => $orderId,
            'status' => $paymentStatus,
            'method' => $paymentMethod,
        ]);

        if (! $orderId) {
            Log::warning('HandleKashierWebhook: Missing merchantOrderId/orderReference in payload', [
                'payload' => $payload,
            ]);

            return;
        }

        $payment = Payment::where('transaction_id', $orderId)->first();

        if (! $payment) {
            Log::warning("HandleKashierWebhook: Payment not found for orderId: {$orderId}", [
                'orderId' => $orderId,
            ]);

            return;
        }

        $isSuccess = in_array($paymentStatus, ['SUCCESS', 'CAPTURED'], true)
            || (($data['transactionResponseCode'] ?? null) === '00' && ! in_array($paymentStatus, ['FAILED', 'FAILURE', 'CANCELLED', 'REJECTED', 'DECLINED'], true));

        $isFailed = in_array($paymentStatus, ['FAILED', 'FAILURE', 'CANCELLED', 'REJECTED', 'DECLINED'], true);

        if ($isSuccess && $payment->status !== 'paid') {
            DB::transaction(function () use ($payment, $paymentMethod, $payload, $orderId) {
                $payment->update([
                    'status' => 'paid',
                    'payment_method' => $paymentMethod,
                    'gateway_response' => $payload,
                ]);

                $subscription = $payment->subscription;
                if ($subscription) {
                    $plan = $subscription->plan;
                    $days = $subscription->duration_days ?: ($plan?->duration_days ?: 30);

                    $subscription->update([
                        'status' => 'active',
                        'start_date' => now(),
                        'end_date' => now()->addDays($days),
                    ]);
                }

                Log::info("HandleKashierWebhook: Payment {$payment->id} marked as paid for order {$orderId}");
            });
        } elseif ($isFailed && $payment->status === 'pending') {
            DB::transaction(function () use ($payment, $payload, $orderId) {
                $payment->update([
                    'status' => 'failed',
                    'gateway_response' => $payload,
                ]);

                if ($payment->subscription) {
                    $payment->subscription->update(['status' => 'cancelled']);
                }

                Log::info("HandleKashierWebhook: Payment {$payment->id} marked as failed for order {$orderId}");
            });
        }
    }
}
