<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('reference_id')->nullable()->after('description');
            $table->string('reference_type')->nullable()->after('reference_id');
            
            // Add index for better performance on polymorphic relationships
            $table->index(['reference_id', 'reference_type']);
        });
    }

    public function down(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropIndex(['reference_id', 'reference_type']);
            $table->dropColumn(['reference_id', 'reference_type']);
        });
    }
};
