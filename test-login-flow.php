<?php
// Test the login flow to identify where the 500 error occurs

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Load Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

function display($message, $data = null) {
    echo "\n=== $message ===\n";
    if ($data !== null) {
        if (is_string($data)) {
            echo $data . "\n";
        } else {
            print_r($data);
        }
    }
    echo "\n";
}

try {
    // 1. Get a test user with OTP
    $user = User::whereNotNull('otp')->first();
    
    if (!$user) {
        // If no user with OTP, create one for testing
        $user = User::first();
        if ($user) {
            $user->otp = '123456';
            $user->otp_expires_at = now()->addMinutes(30);
            $user->save();
            display("Updated test user with OTP", [
                'id' => $user->id,
                'phone' => $user->phone,
                'otp' => $user->otp,
                'otp_expires_at' => $user->otp_expires_at
            ]);
        } else {
            die("No users found in database. Please create a user first.\n");
        }
    }
    
    display("Testing with user", [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'phone' => $user->phone,
        'otp' => $user->otp,
        'otp_expires_at' => $user->otp_expires_at,
        'phone_verified_at' => $user->phone_verified_at
    ]);
    
    // 2. Simulate login with OTP
    display("Simulating login with OTP...");
    
    // 3. Verify OTP
    $otp = $user->otp; // This would normally come from the request
    
    if (!$otp) {
        throw new Exception("No OTP found for user");
    }
    
    if ($user->otp !== $otp) {
        throw new Exception("Invalid OTP");
    }
    
    if ($user->otp_expires_at && $user->otp_expires_at->isPast()) {
        throw new Exception("OTP has expired");
    }
    
    display("OTP verified successfully");
    
    // 4. Mark phone as verified if not already
    if (!$user->phone_verified_at) {
        $user->phone_verified_at = now();
        $user->is_verified = true;
        $user->save();
        display("Marked phone as verified");
    }
    
    // 5. Generate JWT token
    display("Generating JWT token...");
    
    try {
        $token = auth('api')->login($user);
        
        if (!$token) {
            throw new Exception("Failed to generate token");
        }
        
        display("Token generated successfully", [
            'token' => substr($token, 0, 20) . '...',
            'length' => strlen($token)
        ]);
        
        // 6. Verify the token
        $payload = JWTAuth::setToken($token)->getPayload();
        display("Token verified", [
            'subject' => $payload->get('sub'),
            'expires' => date('Y-m-d H:i:s', $payload->get('exp')),
            'custom_claims' => $payload->get('requires_profile_completion', 'none')
        ]);
        
        // 7. Return the response that would be sent to the client
        $response = [
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'is_verified' => (bool)$user->is_verified,
                    'requires_profile_completion' => !$user->isProfileComplete(),
                ],
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => config('jwt.ttl') * 60, // in seconds
            ]
        ];
        
        display("Login successful", [
            'response' => $response
        ]);
        
    } catch (Exception $e) {
        display("Error generating token", [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
    }
    
} catch (Exception $e) {
    display("Error", [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
}

display("Test completed");
