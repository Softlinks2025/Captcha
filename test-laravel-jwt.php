<?php
// Test JWT functionality within Laravel

// Load Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\JWT;
use Tymon\JWTAuth\Manager;
use Tymon\JWTAuth\Http\Parser\Parser;
use Tymon\JWTAuth\Claims\Factory as ClaimFactory;
use Tymon\JWTAuth\Validators\PayloadValidator;
use App\Models\User;

// Function to display output in a readable format
function display($message, $data = null) {
    echo "\n=== " . $message . " ===\n";
    if ($data !== null) {
        if (is_string($data)) {
            echo $data . "\n";
        } else {
            print_r($data);
        }
    }
    echo "\n";
}

echo "\n" . str_repeat("=", 80) . "\n";
echo "LARAVEL JWT TEST SCRIPT\n";
echo str_repeat("=", 80) . "\n\n";

try {
    // 1. Check JWT configuration
    $jwtConfig = config('jwt');
    
    // Hide sensitive data
    if (isset($jwtConfig['secret'])) {
        $jwtConfig['secret'] = substr($jwtConfig['secret'], 0, 5) . '...';
    }
    
    display("JWT Configuration", $jwtConfig);
    
    // 2. Get a test user
    $user = User::first();
    
    if (!$user) {
        die("No users found in the database. Please create a user first.\n");
    }
    
    display("Test User", [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email
    ]);
    
    // 3. Test JWT token generation
    display("Testing JWT Token Generation...");
    
    try {
        // Method 1: Using JWTAuth facade
        $token1 = JWTAuth::fromUser($user);
        display("✓ Token generated using JWTAuth::fromUser()", [
            'token' => substr($token1, 0, 20) . '...',
            'length' => strlen($token1)
        ]);
    } catch (Exception $e) {
        display("✗ Error with JWTAuth::fromUser()", $e->getMessage());
    }
    
    try {
        // Method 2: Using auth() helper
        $token2 = auth('api')->login($user);
        display("✓ Token generated using auth('api')->login()", [
            'token' => substr($token2, 0, 20) . '...',
            'length' => strlen($token2)
        ]);
    } catch (Exception $e) {
        display("✗ Error with auth('api')->login()", $e->getMessage());
    }
    
    // 4. Test token verification
    if (isset($token1)) {
        display("Testing Token Verification...");
        
        try {
            $payload = JWTAuth::setToken($token1)->getPayload();
            display("✓ Token verified successfully", [
                'subject' => $payload->get('sub'),
                'expires_at' => date('Y-m-d H:i:s', $payload->get('exp')),
                'issued_at' => date('Y-m-d H:i:s', $payload->get('iat'))
            ]);
        } catch (Exception $e) {
            display("✗ Token verification failed", $e->getMessage());
        }
    }
    
    // 5. Test token refresh
    if (isset($token1)) {
        display("Testing Token Refresh...");
        
        try {
            $refreshed = JWTAuth::setToken($token1)->refresh();
            display("✓ Token refreshed successfully", [
                'new_token' => substr($refreshed, 0, 20) . '...',
                'length' => strlen($refreshed)
            ]);
        } catch (Exception $e) {
            display("✗ Token refresh failed", $e->getMessage());
        }
    }
    
} catch (Exception $e) {
    display("✗ An error occurred", [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
}

display("Test completed", "Please review the results above for any issues.");
echo "\n" . str_repeat("=", 80) . "\n\n";
