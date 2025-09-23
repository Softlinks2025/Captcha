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
            $table->string('additional_contact_number', 20)->nullable()->after('phone_number');
            $table->boolean('joining_fee_paid')->default(false)->after('additional_contact_number');
            $table->timestamp('joining_fee_paid_at')->nullable()->after('joining_fee_paid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agents', function (Blueprint $table) {
            $table->dropColumn('additional_contact_number');
            $table->dropColumn(['joining_fee_paid', 'joining_fee_paid_at']);
        });
    }
};
