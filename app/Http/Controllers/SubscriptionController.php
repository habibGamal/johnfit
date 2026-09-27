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
        $plans = SubscriptionPlan::where('is_active', true)
            ->with(['activeTiers'])
            ->get();
        $user = Auth::user();

        return Inertia::render('Subscription/Packages', [
            'plans' => $plans,
            'activeSubscription' => $user->activeSubscription()?->load(['plan', 'tier']),
        ]);
    }

    public function initiate(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id',
            'tier_id' => 'nullable|exists:subscription_plan_tiers,id',
            'months' => 'nullable|integer|min:1',
        ]);

        $user = Auth::user();
        $plan = SubscriptionPlan::findOrFail($request->plan_id);

        // Find matching tier by tier_id or months, or fallback to first active tier
        $tier = null;
        if ($request->filled('tier_id')) {
            $tier = $plan->activeTiers()->find($request->tier_id);
        } elseif ($request->filled('months')) {
            $tier = $plan->getTierForMonths((int) $request->months);
        }

        if (! $tier) {
            $tier = $plan->activeTiers()->first();
        }

        $price = $tier ? (float) $tier->price : (float) $plan->price;
        $months = $tier ? (int) $tier->months : 1;
        $days = $tier ? (int) $tier->effective_days : (int) ($plan->duration_days ?: 30);

        // Create a pending subscription
        $subscription = Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'tier_id' => $tier?->id,
            'duration_months' => $months,
            'duration_days' => $days,
            'status' => 'pending',
        ]);

        // Create a pending payment record
        $payment = Payment::create([
            'subscription_id' => $subscription->id,
            'user_id' => $user->id,
            'amount' => $price,
            'currency' => config('kashier.currency', 'EGP'),
            'status' => 'pending',
        ]);

        $orderId = 'sub-'.$payment->id.'-'.time();

        $paymentUrl = Kashier::buildPaymentUrl(
            amount: (float) $price,
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

        $subscription = $payment->subscription;
        $plan = $subscription->plan;
        $days = $subscription->duration_days ?: ($plan?->duration_days ?: 30);

        if ($paymentStatus === 'SUCCESS') {
            $payment->update([
                'status' => 'paid',
                'payment_method' => $request->input('paymentMethod'),
                'gateway_response' => $request->all(),
            ]);

            $subscription->update([
                'status' => 'active',
                'start_date' => now(),
                'end_date' => now()->addDays($days),
            ]);

            return Inertia::render('Subscription/Success', [
                'subscription' => $subscription->load(['plan', 'tier']),
            ]);
        }

        $payment->update([
            'status' => 'failed',
            'gateway_response' => $request->all(),
        ]);

        $subscription->update(['status' => 'cancelled']);

        return Inertia::render('Subscription/Failed', [
            'message' => 'Payment was not successful. Please try again.',
        ]);
    }
}
