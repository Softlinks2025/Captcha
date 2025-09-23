<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Use raw SQL to modify the otp column to be larger
        DB::statement('ALTER TABLE users MODIFY otp VARCHAR(255) NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to the original size (100 characters should be enough for unhashed OTPs)
        DB::statement('ALTER TABLE users MODIFY otp VARCHAR(100) NULL');
    }
};
