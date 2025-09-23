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
        Schema::table('agent_plans', function (Blueprint $table) {
            if (!Schema::hasColumn('agent_plans', 'earning_ranges')) {
                $table->json('earning_ranges')->nullable()->after('rate_after_100');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agent_plans', function (Blueprint $table) {
            if (Schema::hasColumn('agent_plans', 'earning_ranges')) {
                $table->dropColumn('earning_ranges');
            }
        });
    }
}; 