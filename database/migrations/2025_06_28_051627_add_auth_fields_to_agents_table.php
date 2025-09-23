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
            $table->string('email')->nullable()->after('phone_number');
            $table->string('password')->nullable()->after('email');
            $table->string('otp', 255)->nullable()->after('password');
            $table->timestamp('otp_expires_at')->nullable()->after('otp');
            $table->boolean('is_verified')->default(false)->after('otp_expires_at');
            $table->boolean('profile_completed')->default(false)->after('is_verified');
            $table->decimal('wallet_balance', 10, 2)->default(0.00)->after('profile_completed');
            $table->decimal('total_earnings', 10, 2)->default(0.00)->after('wallet_balance');
            $table->decimal('total_withdrawals', 10, 2)->default(0.00)->after('total_earnings');
            $table->string('upi_id')->nullable()->after('total_withdrawals');
            $table->string('bank_account_number')->nullable()->after('upi_id');
            $table->string('ifsc_code')->nullable()->after('bank_account_number');
            $table->string('account_holder_name')->nullable()->after('ifsc_code');
            $table->string('address')->nullable()->after('account_holder_name');
            $table->string('city')->nullable()->after('address');
            $table->string('state')->nullable()->after('city');
            $table->string('pincode')->nullable()->after('state');
            $table->string('profile_image')->nullable()->after('pincode');
            $table->string('aadhar_number')->nullable()->after('profile_image');
            $table->string('pan_number')->nullable()->after('aadhar_number');
            $table->string('gst_number')->nullable()->after('pan_number');
            $table->text('bio')->nullable()->after('gst_number');
            $table->string('status')->default('active')->after('bio'); // active, inactive, suspended
            $table->timestamp('last_login_at')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agents', function (Blueprint $table) {
            $table->dropColumn([
                'email', 'password', 'otp', 'otp_expires_at', 'is_verified', 
                'profile_completed', 'wallet_balance', 'total_earnings', 
                'total_withdrawals', 'upi_id', 'bank_account_number', 'ifsc_code',
                'account_holder_name', 'address', 'city', 'state', 'pincode',
                'profile_image', 'aadhar_number', 'pan_number', 'gst_number',
                'bio', 'status', 'last_login_at'
            ]);
        });
    }
};
