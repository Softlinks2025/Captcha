<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

Route::get('/test-login-endpoint', function () {
    $phone = '9457508075';
    $otp = '123456'; // Test OTP
    
    try {
        Log::info('Test login endpoint called', ['phone' => $phone]);
        
        // 1. Check if user exists
        $user = User::where('phone', $phone)->first();
        
        if (!$user) {
            return [
                'status' => 'error',
                'message' => 'User not found',
                'user_exists' => false
            ];
        }
        
        // 2. Check if OTP is set
        if (empty($user->otp)) {
            return [
                'status' => 'error',
                'message' => 'No OTP set for user',
                'user_exists' => true,
                'has_otp' => false
            ];
        }
        
        // 3. Verify OTP hash
        $otpMatches = Hash::check($otp, $user->otp);
        
        // 4. Check OTP expiration
        $isExpired = $user->otp_expires_at && now()->gt($user->otp_expires_at);
        
        return [
            'status' => 'success',
            'user' => [
                'id' => $user->id,
                'phone' => $user->phone,
                'has_otp' => !empty($user->otp),
                'otp_expires_at' => $user->otp_expires_at,
                'is_expired' => $isExpired,
                'otp_matches' => $otpMatches
            ],
            'debug' => [
                'otp_stored' => $user->otp,
                'otp_provided' => $otp,
                'otp_matches' => $otpMatches,
                'current_time' => now(),
                'is_expired' => $isExpired
            ]
        ];
        
    } catch (\Exception $e) {
        Log::error('Test login error', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return [
            'status' => 'error',
            'message' => 'Test failed',
            'error' => [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'code' => $e->getCode()
            ]
        ];
    }
});
