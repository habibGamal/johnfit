<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use App\Models\SubscriptionPlanTier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        SubscriptionPlanTier::truncate();
        SubscriptionPlan::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $plans = [
            [
                'name' => 'Basic',
                'price' => 299.00,
                'tag' => null,
                'features' => [
                    ['feature' => 'Access to 1 workout plan'],
                    ['feature' => 'Access to 1 meal plan'],
                    ['feature' => 'Progress tracking'],
                    ['feature' => 'Email support'],
                ],
                'is_active' => true,
                'duration_days' => 30,
                'tiers' => [
                    ['months' => 1, 'duration_days' => 30, 'price' => 299.00, 'tag' => null, 'order' => 1],
                    ['months' => 3, 'duration_days' => 90, 'price' => 799.00, 'tag' => 'Save 11%', 'order' => 2],
                    ['months' => 6, 'duration_days' => 180, 'price' => 1499.00, 'tag' => 'Save 16%', 'order' => 3],
                ],
            ],
            [
                'name' => 'Pro',
                'price' => 499.00,
                'tag' => 'Most Popular',
                'features' => [
                    ['feature' => 'Unlimited workout plans'],
                    ['feature' => 'Unlimited meal plans'],
                    ['feature' => 'Progress tracking & analytics'],
                    ['feature' => 'InBody composition logs'],
                    ['feature' => 'Fitness score tracking'],
                    ['feature' => 'Priority email support'],
                ],
                'is_active' => true,
                'duration_days' => 30,
                'tiers' => [
                    ['months' => 1, 'duration_days' => 30, 'price' => 499.00, 'tag' => null, 'order' => 1],
                    ['months' => 3, 'duration_days' => 90, 'price' => 1299.00, 'tag' => 'Save 13%', 'order' => 2],
                    ['months' => 6, 'duration_days' => 180, 'price' => 2399.00, 'tag' => 'Save 20%', 'order' => 3],
                ],
            ],
            [
                'name' => 'Elite',
                'price' => 799.00,
                'tag' => 'Best Value',
                'features' => [
                    ['feature' => 'Everything in Pro'],
                    ['feature' => 'Personalised coaching sessions'],
                    ['feature' => 'Weekly check-ins with your coach'],
                    ['feature' => 'Custom nutrition planning'],
                    ['feature' => 'Dedicated WhatsApp support'],
                ],
                'is_active' => true,
                'duration_days' => 30,
                'tiers' => [
                    ['months' => 1, 'duration_days' => 30, 'price' => 799.00, 'tag' => null, 'order' => 1],
                    ['months' => 3, 'duration_days' => 90, 'price' => 2099.00, 'tag' => 'Save 12%', 'order' => 2],
                    ['months' => 6, 'duration_days' => 180, 'price' => 3899.00, 'tag' => 'Best Value', 'order' => 3],
                ],
            ],
        ];

        foreach ($plans as $planData) {
            $tiers = $planData['tiers'] ?? [];
            unset($planData['tiers']);

            $plan = SubscriptionPlan::create($planData);

            foreach ($tiers as $tierData) {
                $plan->tiers()->create($tierData);
            }
        }
    }
}
