<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AgentPlan;

class AgentPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Ever Green Plan',
                'description' => 'Perfect for beginners with unlimited earning potential',
                'price' => 1000.00,
                'duration' => 'lifetime',
                'is_active' => true,
                'rate_1_50' => 100.00,
                'rate_51_100' => 150.00,
                'rate_after_100' => 200.00,
                'bonus_10_logins' => 'Cap',
                'bonus_50_logins' => 'T-shirt',
                'bonus_100_logins' => 'Bag',
                'min_withdrawal' => 250.00,
                'max_withdrawal' => null,
                'withdrawal_time' => 'Monday to Saturday 9:00AM to 18:00PM',
                'unlimited_earning' => true,
                'unlimited_logins' => false,
                'max_logins_per_day' => null,
                'sort_order' => 1
            ],
            [
                'name' => 'Gold Plan',
                'description' => 'Premium plan with higher earning rates',
                'price' => 2000.00,
                'duration' => 'lifetime',
                'is_active' => true,
                'rate_1_50' => 150.00,
                'rate_51_100' => 200.00,
                'rate_after_100' => 250.00,
                'bonus_10_logins' => 'Cap',
                'bonus_50_logins' => 'T-shirt',
                'bonus_100_logins' => 'Bag',
                'min_withdrawal' => 250.00,
                'max_withdrawal' => null,
                'withdrawal_time' => 'Monday to Saturday 9:00AM to 18:00PM',
                'unlimited_earning' => true,
                'unlimited_logins' => false,
                'max_logins_per_day' => null,
                'sort_order' => 2
            ],
            [
                'name' => 'Unlimited Plan',
                'description' => 'Ultimate plan with maximum earning potential',
                'price' => 3000.00,
                'duration' => 'lifetime',
                'is_active' => true,
                'rate_1_50' => 200.00,
                'rate_51_100' => 250.00,
                'rate_after_100' => 300.00,
                'bonus_10_logins' => 'Cap',
                'bonus_50_logins' => 'T-shirt',
                'bonus_100_logins' => 'Bag',
                'min_withdrawal' => 250.00,
                'max_withdrawal' => null,
                'withdrawal_time' => 'Monday to Saturday 9:00AM to 18:00PM',
                'unlimited_earning' => true,
                'unlimited_logins' => false,
                'max_logins_per_day' => null,
                'sort_order' => 3
            ]
        ];

        foreach ($plans as $plan) {
            AgentPlan::create($plan);
        }
    }
}
