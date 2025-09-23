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
            // OTP fields
            $table->string('phone', 15)->after('email')->nullable()->unique();
            $table->string('otp', 6)->nullable();
            $table->timestamp('otp_expires_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            
            // Profile fields
            $table->string('profile_photo_path')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('pincode', 20)->nullable();
            $table->string('referral_code', 20)->nullable();
            
            // Make email nullable for phone-based registration
            $table->string('email')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'otp',
                'otp_expires_at',
                'phone_verified_at',
                'profile_photo_path',
                'date_of_birth',
                'gender',
                'address',
                'city',
                'state',
                'country',
                'pincode',
                'referral_code'
            ]);
            
            // Make email required again
            $table->string('email')->nullable(false)->change();
        });
    }
};
