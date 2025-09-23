<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SubscriptionPlan;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing plans
        SubscriptionPlan::truncate();

        // Ever Green Plan - Rs 999
        SubscriptionPlan::create([
            'name' => 'Ever Green Plan',
            'captcha_per_day' => '200',
            'min_withdrawal_limit' => 100,
            'cost' => 999.00,
            'earning_type' => 'limited',
            'plan_type' => 'basic',
            'duration' => 'lifetime',
            'caption_limit' => 200,
            'earnings' => [
                '1-50' => 15,
                '51-100' => 25,
                '101-150' => 40,
                '151-200' => 55
            ],
            'min_daily_earning' => 140,
            'referral_earning_per_ref' => 100.00,
            'daily_captcha_earning_with_ref' => 140.00,
            'bonus_10_referrals' => 50.00,
            'gift_10_referrals' => 'Cap',
            'bonus_20_referrals' => 100.00,
            'gift_20_referrals' => 'T-shirt',
            'daily_limit_bonus' => 5.00,
            'is_unlimited' => false,
            'unlimited_earning_rate' => 0.00,
        ]);

        // Gold Plan - Rs 1999
        SubscriptionPlan::create([
            'name' => 'Gold Plan',
            'captcha_per_day' => '400',
            'min_withdrawal_limit' => 200,
            'cost' => 1999.00,
            'earning_type' => 'limited',
            'plan_type' => 'premium',
            'duration' => 'lifetime',
            'caption_limit' => 400,
            'earnings' => [
                '1-100' => 25,
                '101-200' => 35,
                '201-300' => 55,
                '301-400' => 60
            ],
            'min_daily_earning' => 180,
            'referral_earning_per_ref' => 200.00,
            'daily_captcha_earning_with_ref' => 180.00,
            'bonus_10_referrals' => 50.00,
            'gift_10_referrals' => 'Cap',
            'bonus_20_referrals' => 100.00,
            'gift_20_referrals' => 'T-shirt',
            'daily_limit_bonus' => 10.00,
            'is_unlimited' => false,
            'unlimited_earning_rate' => 0.00,
        ]);

        // Unlimited Plan - Rs 2999
        SubscriptionPlan::create([
            'name' => 'Unlimited Plan for Ever',
            'captcha_per_day' => 'unlimited',
            'min_withdrawal_limit' => 300,
            'cost' => 2999.00,
            'earning_type' => 'unlimited',
            'plan_type' => 'premium',
            'duration' => 'lifetime',
            'caption_limit' => null,
            'earnings' => [
                '1-100' => 35,
                '101-200' => 55,
                '201-300' => 75,
                'after_300' => 30
            ],
            'min_daily_earning' => 165,
            'referral_earning_per_ref' => 300.00,
            'daily_captcha_earning_with_ref' => 165.00,
            'bonus_10_referrals' => 50.00,
            'gift_10_referrals' => 'Cap',
            'bonus_20_referrals' => 100.00,
            'gift_20_referrals' => 'T-shirt',
            'daily_limit_bonus' => 0.00,
            'is_unlimited' => true,
            'unlimited_earning_rate' => 30.00,
        ]);
    }
}
