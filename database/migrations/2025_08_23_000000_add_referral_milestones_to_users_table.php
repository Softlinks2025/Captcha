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
        Schema::table('users', function (Blueprint $table) {
            // Add referred_by foreign key if not exists
            if (!Schema::hasColumn('users', 'referred_by')) {
                $table->unsignedBigInteger('referred_by')->nullable()->after('id');
                $table->foreign('referred_by')->references('id')->on('users')->onDelete('set null');
            }
            
            // Referral milestone tracking
            if (!Schema::hasColumn('users', 'total_referrals')) {
                $table->integer('total_referrals')->default(0)->after('referred_by');
                $table->boolean('milestone_10_reached')->default(false)->after('total_referrals');
                $table->boolean('milestone_50_reached')->default(false)->after('milestone_10_reached');
                $table->boolean('milestone_100_reached')->default(false)->after('milestone_50_reached');
                
                // Bonus tracking
                $table->boolean('bonus_claimed_10')->default(false)->after('milestone_100_reached');
                $table->boolean('bonus_claimed_50')->default(false)->after('bonus_claimed_10');
                $table->boolean('bonus_claimed_100')->default(false)->after('bonus_claimed_50');
                $table->timestamp('last_bonus_claimed_at')->nullable()->after('bonus_claimed_100');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['referred_by']);
            $table->dropColumn([
                'referred_by',
                'total_referrals',
                'milestone_10_reached',
                'milestone_50_reached',
                'milestone_100_reached',
                'bonus_claimed_10',
                'bonus_claimed_50',
                'bonus_claimed_100',
                'last_bonus_claimed_at'
            ]);
        });
    }
};
