<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        SubscriptionPlan::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

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
            ],
            [
                'name' => 'Elite',
                'price' => 799.00,
                'tag' => 'Best Value',
                'features' => [
                    ['feature' => 'Everything in Pro'],
                    ['feature' => '3-month access (save 33%)'],
                    ['feature' => 'Personalised coaching sessions'],
                    ['feature' => 'Weekly check-ins with your coach'],
                    ['feature' => 'Custom nutrition planning'],
                    ['feature' => 'Dedicated WhatsApp support'],
                ],
                'is_active' => true,
                'duration_days' => 90,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::create($plan);
        }
    }
}
