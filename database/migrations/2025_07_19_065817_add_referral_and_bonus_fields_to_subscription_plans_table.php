<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            // Plan duration (lifetime only)
            $table->string('duration')->default('lifetime')->after('plan_type');
            
            // Referral earnings
            $table->decimal('referral_earning_per_ref', 10, 2)->default(0)->after('min_daily_earning');
            $table->decimal('daily_captcha_earning_with_ref', 10, 2)->default(0)->after('referral_earning_per_ref');
            
            // Referral milestone bonuses
            $table->decimal('bonus_10_referrals', 10, 2)->default(0)->after('daily_captcha_earning_with_ref');
            $table->string('gift_10_referrals')->nullable()->after('bonus_10_referrals');
            $table->decimal('bonus_20_referrals', 10, 2)->default(0)->after('gift_10_referrals');
            $table->string('gift_20_referrals')->nullable()->after('bonus_20_referrals');
            
            // Captcha bonus for daily limit
            $table->decimal('daily_limit_bonus', 10, 2)->default(0)->after('gift_20_referrals');
            
            // Unlimited plan specific fields
            $table->boolean('is_unlimited')->default(false)->after('daily_limit_bonus');
            $table->decimal('unlimited_earning_rate', 10, 2)->default(0)->after('is_unlimited');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn([
                'duration',
                'referral_earning_per_ref',
                'daily_captcha_earning_with_ref',
                'bonus_10_referrals',
                'gift_10_referrals',
                'bonus_20_referrals',
                'gift_20_referrals',
                'daily_limit_bonus',
                'is_unlimited',
                'unlimited_earning_rate'
            ]);
        });
    }
};
