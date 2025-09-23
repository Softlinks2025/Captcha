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
        // Drop the sessions table if it exists
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Schema::dropIfExists('sessions');
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Reverse the migrations.
     * 
     * Note: This is a one-way migration to fix the sessions table.
     * Rolling back will not recreate the sessions table as we don't know its original structure.
     */
    public function down(): void
    {
        // This is intentionally left blank as we don't want to recreate the sessions table
        // with potentially incorrect schema during rollback
    }
};
