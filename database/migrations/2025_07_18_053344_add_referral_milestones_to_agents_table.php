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
        Schema::table('agents', function (Blueprint $table) {
            // Referral milestone tracking
            $table->integer('total_referrals')->default(0)->after('total_withdrawals');
            $table->boolean('milestone_10_reached')->default(false)->after('total_referrals');
            $table->boolean('milestone_50_reached')->default(false)->after('milestone_10_reached');
            $table->boolean('milestone_100_reached')->default(false)->after('milestone_50_reached');
            
            // Bonus tracking
            $table->boolean('bonus_tshirt_claimed')->default(false)->after('milestone_100_reached');
            $table->boolean('bonus_bag_claimed')->default(false)->after('bonus_tshirt_claimed');
            $table->decimal('earnings_cap', 10, 2)->nullable()->after('bonus_bag_claimed');
            $table->timestamp('earnings_cap_applied_at')->nullable()->after('earnings_cap');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agents', function (Blueprint $table) {
            $table->dropColumn([
                'total_referrals',
                'milestone_10_reached',
                'milestone_50_reached',
                'milestone_100_reached',
                'bonus_tshirt_claimed',
                'bonus_bag_claimed',
                'earnings_cap',
                'earnings_cap_applied_at'
            ]);
        });
    }
};
