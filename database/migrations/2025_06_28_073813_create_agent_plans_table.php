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
        Schema::create('agent_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Ever Green, Gold, Unlimited
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2); // 1000, 2000, 3000
            $table->string('duration')->default('lifetime'); // lifetime, monthly, yearly
            $table->boolean('is_active')->default(true);
            
            // Earning rates
            $table->decimal('rate_1_50', 10, 2); // Rate for 1-50 logins
            $table->decimal('rate_51_100', 10, 2); // Rate for 51-100 logins
            $table->decimal('rate_after_100', 10, 2); // Rate after 100 logins
            
            // Bonuses
            $table->string('bonus_10_logins')->nullable(); // Cap
            $table->string('bonus_50_logins')->nullable(); // T-shirt
            $table->string('bonus_100_logins')->nullable(); // Bag
            
            // Withdrawal settings
            $table->decimal('min_withdrawal', 10, 2)->default(250.00);
            $table->decimal('max_withdrawal', 10, 2)->nullable();
            $table->string('withdrawal_time')->default('Monday to Saturday 9:00AM to 18:00PM');
            
            // Features
            $table->boolean('unlimited_earning')->default(true);
            $table->boolean('unlimited_logins')->default(false);
            $table->integer('max_logins_per_day')->nullable();
            
            // Plan order for display
            $table->integer('sort_order')->default(0);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_plans');
    }
};
