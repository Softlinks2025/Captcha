<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            if (!Schema::hasColumn('subscription_plans', 'captcha_per_day')) {
                $table->string('captcha_per_day');
            }
            if (!Schema::hasColumn('subscription_plans', 'min_withdrawal_limit')) {
                $table->integer('min_withdrawal_limit')->nullable();
            }
            if (!Schema::hasColumn('subscription_plans', 'cost')) {
                $table->decimal('cost', 10, 2);
            }
            if (!Schema::hasColumn('subscription_plans', 'earning_type')) {
                $table->string('earning_type')->nullable();
            }
            if (!Schema::hasColumn('subscription_plans', 'plan_type')) {
                $table->string('plan_type')->nullable();
            }
            if (!Schema::hasColumn('subscription_plans', 'image')) {
                $table->string('image')->nullable();
            }
            if (!Schema::hasColumn('subscription_plans', 'caption_limit')) {
                $table->integer('caption_limit')->nullable();
            }
            if (!Schema::hasColumn('subscription_plans', 'earnings')) {
                $table->json('earnings')->nullable();
            }
            if (!Schema::hasColumn('subscription_plans', 'min_daily_earning')) {
                $table->integer('min_daily_earning')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            if (Schema::hasColumn('subscription_plans', 'captcha_per_day')) {
                $table->dropColumn('captcha_per_day');
            }
            if (Schema::hasColumn('subscription_plans', 'min_withdrawal_limit')) {
                $table->dropColumn('min_withdrawal_limit');
            }
            if (Schema::hasColumn('subscription_plans', 'cost')) {
                $table->dropColumn('cost');
            }
            if (Schema::hasColumn('subscription_plans', 'earning_type')) {
                $table->dropColumn('earning_type');
            }
            if (Schema::hasColumn('subscription_plans', 'plan_type')) {
                $table->dropColumn('plan_type');
            }
            if (Schema::hasColumn('subscription_plans', 'image')) {
                $table->dropColumn('image');
            }
            if (Schema::hasColumn('subscription_plans', 'caption_limit')) {
                $table->dropColumn('caption_limit');
            }
            if (Schema::hasColumn('subscription_plans', 'earnings')) {
                $table->dropColumn('earnings');
            }
            if (Schema::hasColumn('subscription_plans', 'min_daily_earning')) {
                $table->dropColumn('min_daily_earning');
            }
        });
    }
}; 