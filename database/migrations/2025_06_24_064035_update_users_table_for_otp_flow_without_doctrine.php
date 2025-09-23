<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Use raw SQL to modify columns to be nullable
        DB::statement('ALTER TABLE users MODIFY name VARCHAR(255) NULL');
        DB::statement('ALTER TABLE users MODIFY email VARCHAR(255) NULL');
        DB::statement('ALTER TABLE users MODIFY password VARCHAR(255) NULL');

        // Add new columns if they don't exist
        if (!Schema::hasColumn('users', 'phone')) {
            DB::statement('ALTER TABLE users ADD phone VARCHAR(15) NULL UNIQUE AFTER email');
        }
        
        if (!Schema::hasColumn('users', 'otp')) {
            DB::statement('ALTER TABLE users ADD otp VARCHAR(255) NULL AFTER phone');
        }
        
        if (!Schema::hasColumn('users', 'otp_expires_at')) {
            DB::statement('ALTER TABLE users ADD otp_expires_at TIMESTAMP NULL AFTER otp');
        }
        
        if (!Schema::hasColumn('users', 'phone_verified_at')) {
            DB::statement('ALTER TABLE users ADD phone_verified_at TIMESTAMP NULL AFTER otp_expires_at');
        }
        
        if (!Schema::hasColumn('users', 'is_verified')) {
            DB::statement('ALTER TABLE users ADD is_verified TINYINT(1) NOT NULL DEFAULT 0 AFTER phone_verified_at');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove new columns
        if (Schema::hasColumn('users', 'phone')) {
            DB::statement('ALTER TABLE users DROP COLUMN phone');
        }
        if (Schema::hasColumn('users', 'otp')) {
            DB::statement('ALTER TABLE users DROP COLUMN otp');
        }
        if (Schema::hasColumn('users', 'otp_expires_at')) {
            DB::statement('ALTER TABLE users DROP COLUMN otp_expires_at');
        }
        if (Schema::hasColumn('users', 'phone_verified_at')) {
            DB::statement('ALTER TABLE users DROP COLUMN phone_verified_at');
        }
        if (Schema::hasColumn('users', 'is_verified')) {
            DB::statement('ALTER TABLE users DROP COLUMN is_verified');
        }

        // Revert nullable changes (set default values for required fields)
        DB::statement("UPDATE users SET name = 'User' WHERE name IS NULL");
        DB::statement("UPDATE users SET email = CONCAT('user_', id, '@example.com') WHERE email IS NULL");
        DB::statement("UPDATE users SET password = '' WHERE password IS NULL");
        
        DB::statement('ALTER TABLE users MODIFY name VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE users MODIFY email VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE users MODIFY password VARCHAR(255) NOT NULL');
    }
};
