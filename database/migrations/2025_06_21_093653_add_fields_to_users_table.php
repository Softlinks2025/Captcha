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
            $table->string('subscription_name')->nullable();
            $table->date('purchased_date')->nullable();
            $table->decimal('total_amount_paid', 8, 2)->nullable();
            $table->string('level')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['subscription_name', 'purchased_date', 'total_amount_paid', 'level']);
        });
    }
};
