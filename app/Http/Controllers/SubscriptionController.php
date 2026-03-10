<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use Asciisd\Kashier\Facades\Kashier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class SubscriptionController extends Controller
{
    public function packages(): \Inertia\Response
    {
        $plans = SubscriptionPlan::where('is_active', true)->get();
        $user = Auth::user();

        return Inertia::render('Subscription/Packages', [
            'plans' => $plans,
            'activeSubscription' => $user->activeSubscription()?->load('plan'),
        ]);
    }

    public function initiate(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate(['plan_id' => 'required|exists:subscription_plans,id']);

        $user = Auth::user();
        $plan = SubscriptionPlan::findOrFail($request->plan_id);

        // Create a pending subscription
        $subscription = Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => 'pending',
        ]);

        // Create a pending payment record
        $payment = Payment::create([
            'subscription_id' => $subscription->id,
            'user_id' => $user->id,
            'amount' => $plan->price,
            'currency' => config('kashier.currency', 'EGP'),
            'status' => 'pending',
        ]);

        $orderId = 'sub-'.$payment->id.'-'.time();

        $paymentUrl = Kashier::buildPaymentUrl(
            amount: (float) $plan->price,
            orderId: $orderId,
            attributes: [
                'customerName' => $user->name,
                'customerEmail' => $user->email,
            ]
        );

        // Store orderId on payment for lookup on callback
        $payment->update(['transaction_id' => $orderId]);

        return response()->json(['payment_url' => $paymentUrl]);
    }

    public function callback(Request $request): \Inertia\Response|\Illuminate\Http\RedirectResponse
    {
        $orderId = $request->input('merchantOrderId');
        $paymentStatus = $request->input('paymentStatus');

        $payment = Payment::where('transaction_id', $orderId)->first();

        if (! $payment) {
            return redirect()->route('packages.index')->with('error', 'Payment record not found.');
        }

        if ($paymentStatus === 'SUCCESS') {
            $payment->update([
                'status' => 'paid',
                'payment_method' => $request->input('paymentMethod'),
                'gateway_response' => $request->all(),
            ]);

            $plan = $payment->subscription->plan;

            $payment->subscription->update([
                'status' => 'active',
                'start_date' => now(),
                'end_date' => now()->addDays($plan->duration_days),
            ]);

            return Inertia::render('Subscription/Success', [
                'subscription' => $payment->subscription->load('plan'),
            ]);
        }

        $payment->update([
            'status' => 'failed',
            'gateway_response' => $request->all(),
        ]);

        $payment->subscription->update(['status' => 'cancelled']);

        return Inertia::render('Subscription/Failed', [
            'message' => 'Payment was not successful. Please try again.',
        ]);
    }
}
