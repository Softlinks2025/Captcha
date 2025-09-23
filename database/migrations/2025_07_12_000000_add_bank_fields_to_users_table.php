<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('bank_account_number')->nullable()->after('upi_id');
            $table->string('ifsc_code')->nullable()->after('bank_account_number');
            $table->string('account_holder_name')->nullable()->after('ifsc_code');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['bank_account_number', 'ifsc_code', 'account_holder_name']);
        });
    }
}; 