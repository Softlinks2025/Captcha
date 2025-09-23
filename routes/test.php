<?php

use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

Route::get('/test-registration', function () {
    try {
        DB::beginTransaction();
        
        // Test data
        $phone = '9876543210';
        $otp = '123456';
        
        // Create a test user with OTP
        $user = User::create([
            'phone' => $phone,
            'otp' => bcrypt($otp),
            'otp_expires_at' => now()->addMinutes(10),
            'is_verified' => false,
            'password' => bcrypt('password123')
        ]);
        
        // Test the registration flow
        $response = app()->handle(
            Request::create('/api/v1/register', 'POST', [
                'phone' => $phone,
                'otp' => $otp
            ], [], [], [
                'HTTP_ACCEPT' => 'application/json'
            ])
        );
        
        DB::rollBack();
        
        return [
            'status' => $response->getStatusCode(),
            'content' => json_decode($response->getContent(), true),
            'headers' => $response->headers->all()
        ];
        
    } catch (\Exception $e) {
        DB::rollBack();
        return [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ];
    }
});
