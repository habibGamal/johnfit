<?php

use App\Models\Payment;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Asciisd\Kashier\Events\KashierWebhookHandled;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    $this->user = User::factory()->create(['assessment_completed_at' => now()]);

    $this->plan = SubscriptionPlan::create([
        'name' => 'Pro Plan',
        'price' => 499.00,
        'tag' => 'Most Popular',
        'features' => [['feature' => 'Unlimited workouts'], ['feature' => 'Custom meal plans']],
        'is_active' => true,
        'duration_days' => 30,
    ]);
});

describe('Packages Page', function () {
    it('shows the packages page to authenticated users', function () {
        $response = $this->actingAs($this->user)
            ->get(route('packages.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Subscription/Packages')
            ->has('plans')
        );
    });

    it('redirects unauthenticated users to login', function () {
        $this->get(route('packages.index'))
            ->assertRedirect(route('login'));
    });

    it('only shows active plans', function () {
        SubscriptionPlan::create([
            'name' => 'Inactive Plan',
            'price' => 100,
            'is_active' => false,
            'duration_days' => 30,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('packages.index'));

        $response->assertInertia(fn ($page) => $page
            ->has('plans', 1)
        );
    });
});

describe('Subscription Initiation', function () {
    it('returns a payment URL JSON after initiating a subscription', function () {
        $response = $this->actingAs($this->user)
            ->postJson(route('subscriptions.initiate'), ['plan_id' => $this->plan->id]);

        $response->assertOk()
            ->assertJsonStructure(['payment_url']);

        $subscription = Subscription::where('user_id', $this->user->id)->first();
        expect($subscription)->not->toBeNull();
        expect($subscription->status)->toBe('pending');
        expect($subscription->plan_id)->toBe($this->plan->id);

        $payment = Payment::where('subscription_id', $subscription->id)->first();
        expect($payment)->not->toBeNull();
        expect($payment->status)->toBe('pending');
        expect((float) $payment->amount)->toBe((float) $this->plan->price);
    });

    it('validates the plan_id is required', function () {
        $this->actingAs($this->user)
            ->post(route('subscriptions.initiate'), [])
            ->assertSessionHasErrors('plan_id');
    });

    it('validates the plan must exist', function () {
        $this->actingAs($this->user)
            ->post(route('subscriptions.initiate'), ['plan_id' => 9999])
            ->assertSessionHasErrors('plan_id');
    });
});

describe('User Subscription Methods', function () {
    it('returns false when user has no subscriptions', function () {
        expect($this->user->hasActiveSubscription())->toBeFalse();
        expect($this->user->activeSubscription())->toBeNull();
    });

    it('returns true when user has an active subscription', function () {
        Subscription::create([
            'user_id' => $this->user->id,
            'plan_id' => $this->plan->id,
            'status' => 'active',
            'start_date' => now(),
            'end_date' => now()->addDays(30),
        ]);

        expect($this->user->hasActiveSubscription())->toBeTrue();
        expect($this->user->activeSubscription())->not->toBeNull();
    });

    it('returns false for expired subscriptions', function () {
        Subscription::create([
            'user_id' => $this->user->id,
            'plan_id' => $this->plan->id,
            'status' => 'active',
            'start_date' => now()->subDays(60),
            'end_date' => now()->subDays(30),
        ]);

        expect($this->user->hasActiveSubscription())->toBeFalse();
    });

    it('returns false for cancelled subscriptions', function () {
        Subscription::create([
            'user_id' => $this->user->id,
            'plan_id' => $this->plan->id,
            'status' => 'cancelled',
            'start_date' => now(),
            'end_date' => now()->addDays(30),
        ]);

        expect($this->user->hasActiveSubscription())->toBeFalse();
    });
});

describe('Kashier Webhook', function () {
    it('activates subscription on successful webhook', function () {
        $subscription = Subscription::create([
            'user_id' => $this->user->id,
            'plan_id' => $this->plan->id,
            'status' => 'pending',
        ]);

        $payment = Payment::create([
            'subscription_id' => $subscription->id,
            'user_id' => $this->user->id,
            'transaction_id' => 'sub-test-order-123',
            'amount' => $this->plan->price,
            'status' => 'pending',
        ]);

        Event::dispatch(new KashierWebhookHandled([
            'merchantOrderId' => 'sub-test-order-123',
            'paymentStatus' => 'SUCCESS',
            'paymentMethod' => 'card',
        ]));

        expect($payment->fresh()->status)->toBe('paid');
        expect($subscription->fresh()->status)->toBe('active');
        expect($subscription->fresh()->end_date)->not->toBeNull();
    });

    it('cancels subscription on failed webhook', function () {
        $subscription = Subscription::create([
            'user_id' => $this->user->id,
            'plan_id' => $this->plan->id,
            'status' => 'pending',
        ]);

        $payment = Payment::create([
            'subscription_id' => $subscription->id,
            'user_id' => $this->user->id,
            'transaction_id' => 'sub-test-order-456',
            'amount' => $this->plan->price,
            'status' => 'pending',
        ]);

        Event::dispatch(new KashierWebhookHandled([
            'merchantOrderId' => 'sub-test-order-456',
            'paymentStatus' => 'FAILED',
        ]));

        expect($payment->fresh()->status)->toBe('failed');
        expect($subscription->fresh()->status)->toBe('cancelled');
    });
});
