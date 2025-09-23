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
        Schema::create('agent_commission_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained('subscription_plans')->onDelete('cascade');
            $table->integer('min_referrals')->default(0);
            $table->integer('max_referrals')->nullable();
            $table->decimal('commission_amount', 10, 2);
            $table->timestamps();
            
            // Add unique constraint to prevent overlapping ranges for the same plan
            $table->unique(['plan_id', 'min_referrals', 'max_referrals'], 'unique_commission_tiers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_commission_tiers');
    }
};
