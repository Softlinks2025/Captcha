<?php
// Detailed test of the login flow with comprehensive logging

// Enable all error reporting and display errors
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Start output buffering to capture all output
ob_start();

// Log function to output with timestamp
function log_message($message) {
    $timestamp = date('Y-m-d H:i:s');
    echo "[$timestamp] $message\n";
    flush();
    ob_flush();
}

log_message("Starting detailed login flow test...");

// Load Laravel
log_message("Loading Laravel...");
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
log_message("Laravel bootstrapped successfully");

use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

log_message("Starting test...");

try {
    // 1. Get or create a test user
    log_message("Getting or creating test user...");
    $user = User::whereNotNull('otp')->first();
    
    if (!$user) {
        log_message("No user with OTP found, using first user...");
        $user = User::first();
        
        if (!$user) {
            die("No users found in database. Please create a user first.\n");
        }
        
        // Set test OTP
        $user->otp = '123456';
        $user->otp_expires_at = now()->addMinutes(30);
        $user->save();
        log_message("Updated test user with OTP");
    }
    
    log_message("Using user:" . json_encode([
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'phone' => $user->phone,
        'otp' => $user->otp ? '***' . substr($user->otp, -2) : null,
        'otp_expires_at' => $user->otp_expires_at,
        'phone_verified_at' => $user->phone_verified_at
    ]));
    
    // 2. Verify OTP
    log_message("Verifying OTP...");
    
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
    
    log_message("OTP verified successfully");
    
    // 3. Mark phone as verified if not already
    if (!$user->phone_verified_at) {
        log_message("Marking phone as verified...");
        $user->phone_verified_at = now();
        $user->is_verified = true;
        $user->save();
        log_message("Phone marked as verified");
    }
    
    // 4. Generate JWT token
    log_message("Generating JWT token...");
    
    try {
        log_message("Calling auth('api')->login()...");
        $token = auth('api')->login($user);
        log_message("auth('api')->login() completed");
        
        if (!$token) {
            throw new Exception("Failed to generate token: auth('api')->login() returned null");
        }
        
        log_message("Token generated successfully");
        log_message("Token: " . substr($token, 0, 20) . "... (length: " . strlen($token) . ")");
        
        // 5. Verify the token
        log_message("Verifying token...");
        try {
            $payload = JWTAuth::setToken($token)->getPayload();
            log_message("Token verified successfully");
            log_message("Token payload: " . json_encode([
                'subject' => $payload->get('sub'),
                'expires' => date('Y-m-d H:i:s', $payload->get('exp')),
                'custom_claims' => $payload->get('requires_profile_completion', 'none')
            ]));
            
            // 6. Return the response that would be sent to the client
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
            
            log_message("Login successful");
            log_message("Response: " . json_encode($response, JSON_PRETTY_PRINT));
            
        } catch (Exception $e) {
            log_message("Token verification failed: " . $e->getMessage());
            log_message("File: " . $e->getFile() . ":" . $e->getLine());
            log_message("Trace: " . $e->getTraceAsString());
            throw $e;
        }
        
    } catch (Exception $e) {
        log_message("Error generating token: " . $e->getMessage());
        log_message("File: " . $e->getFile() . ":" . $e->getLine());
        log_message("Trace: " . $e->getTraceAsString());
        throw $e;
    }
    
} catch (Exception $e) {
    log_message("Test failed: " . $e->getMessage());
    log_message("File: " . $e->getFile() . ":" . $e->getLine());
    log_message("Trace: " . $e->getTraceAsString());
}

log_message("Test completed");

// Output all buffered content
ob_end_flush();
