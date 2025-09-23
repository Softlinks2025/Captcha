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
        if (Schema::hasTable('sessions')) {
            Schema::drop('sessions');
        }
    }

    /**
     * Reverse the migrations.
     * 
     * Note: This will not recreate the sessions table with all its columns.
     * If you need to rollback, you should restore from a backup.
     */
    public function down(): void
    {
        // This is intentionally left blank as we shouldn't automatically recreate the sessions table
        // with all its columns in a down migration
    }
};
