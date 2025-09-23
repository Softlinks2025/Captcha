<?php
// Minimal JWT test script

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Load Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;

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
    // 1. Get a user
    $user = User::first();
    if (!$user) {
        die("No users found in database.\n");
    }
    
    display("Testing with user", [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email
    ]);
    
    // 2. Try to generate token
    display("Generating JWT token...");
    $token = JWTAuth::fromUser($user);
    
    display("Token generated successfully", [
        'token' => substr($token, 0, 20) . '...',
        'length' => strlen($token)
    ]);
    
    // 3. Verify the token
    $payload = JWTAuth::setToken($token)->getPayload();
    display("Token verified", [
        'subject' => $payload->get('sub'),
        'expires' => date('Y-m-d H:i:s', $payload->get('exp')),
        'custom_claims' => $payload->get('requires_profile_completion', 'none')
    ]);
    
} catch (Exception $e) {
    display("Error", [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
}

display("Test completed");
