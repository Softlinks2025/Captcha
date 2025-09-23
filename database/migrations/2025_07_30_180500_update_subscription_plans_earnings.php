<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\SubscriptionPlan;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update Ever Green Plan
        $everGreen = SubscriptionPlan::where('name', 'Ever Green Plan')->first();
        if ($everGreen) {
            $everGreen->update([
                'earnings' => json_encode([
                    '1-50' => 15,
                    '51-100' => 25,
                    '101-150' => 40,
                    '151-200' => 55
                ])
            ]);
        }

        // Update Gold Plan
        $goldPlan = SubscriptionPlan::where('name', 'Gold Plan')->first();
        if ($goldPlan) {
            $goldPlan->update([
                'earnings' => json_encode([
                    '1-50' => 20,
                    '51-100' => 35,
                    '101-150' => 55,
                    '151-200' => 75,
                    '201-250' => 100
                ])
            ]);
        }

        // Update Unlimited Plan
        $unlimitedPlan = SubscriptionPlan::where('name', 'Unlimited Plan')->first();
        if ($unlimitedPlan) {
            $unlimitedPlan->update([
                'earnings' => json_encode([
                    '1-100' => 35,
                    '101-200' => 55,
                    '201-300' => 75,
                    'after_300' => 30
                ])
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This is a data migration, so we don't need to do anything in the down method
        // as we don't want to lose the earnings data if we rollback
    }
};
