<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->string('caption_limit')->nullable()->change();
            $table->string('min_withdrawal_limit')->nullable()->change();
            $table->string('min_daily_earning')->nullable()->change();
        });
        Schema::table('agent_plans', function (Blueprint $table) {
            $table->string('rate_1_50')->nullable()->change();
            $table->string('rate_51_100')->nullable()->change();
            $table->string('rate_after_100')->nullable()->change();
            $table->string('min_withdrawal')->nullable()->change();
            $table->string('max_withdrawal')->nullable()->change();
            $table->string('max_logins_per_day')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->integer('caption_limit')->nullable()->change();
            $table->integer('min_withdrawal_limit')->nullable()->change();
            $table->integer('min_daily_earning')->nullable()->change();
        });
        Schema::table('agent_plans', function (Blueprint $table) {
            $table->decimal('rate_1_50', 10, 2)->nullable()->change();
            $table->decimal('rate_51_100', 10, 2)->nullable()->change();
            $table->decimal('rate_after_100', 10, 2)->nullable()->change();
            $table->decimal('min_withdrawal', 10, 2)->nullable()->change();
            $table->decimal('max_withdrawal', 10, 2)->nullable()->change();
            $table->integer('max_logins_per_day')->nullable()->change();
        });
    }
}; 