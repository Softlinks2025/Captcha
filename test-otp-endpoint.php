<?php

// Set the content type to JSON
header('Content-Type: application/json');

// Load Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(IllwareConsoleKernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\User;

// Test data
$testPhone = '9457508075'; // Use a known phone number from your database

try {
    // 1. First verify the user exists
    $user = User::where('phone', $testPhone)->first();
    
    if (!$user) {
        throw new Exception("User with phone {$testPhone} not found in database");
    }
    
    echo "✓ User found in database\n";
    echo "- ID: {$user->id}\n";
    echo "- Name: {$user->name}\n";
    echo "- Email: {$user->email}\n\n";
    
    // 2. Test OTP generation and saving
    $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $otpHash = password_hash($otp, PASSWORD_DEFAULT);
    $otpExpiresAt = now()->addMinutes(10);
    
    $user->otp = $otpHash;
    $user->otp_expires_at = $otpExpiresAt;
    
    if (!$user->save()) {
        throw new Exception("Failed to save OTP to database");
    }
    
    echo "✓ OTP saved to database\n";
    echo "- OTP: {$otp} (hashed in database)\n";
    echo "- Expires at: {$otpExpiresAt}\n\n";
    
    // 3. Test OTP verification
    $storedOtp = $user->otp;
    if (password_verify($otp, $storedOtp)) {
        echo "✓ OTP verification successful\n";
    } else {
        throw new Exception("OTP verification failed");
    }
    
    // 4. Test the actual API endpoint
    echo "\nTesting API endpoint...\n";
    
    $client = new GuzzleHttp\Client([
        'base_uri' => 'http://localhost:8000',
        'timeout'  => 5.0,
        'http_errors' => false
    ]);
    
    $response = $client->post('/api/v1/send-otp', [
        'json' => [
            'phone' => $testPhone
        ],
        'headers' => [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ]
    ]);
    
    $statusCode = $response->getStatusCode();
    $responseBody = json_decode($response->getBody(), true);
    
    echo "API Response (Status: {$statusCode}):\n";
    print_r($responseBody);
    
    if ($statusCode === 200) {
        echo "\n✓ OTP sent successfully!\n";
        
        // Verify the OTP was saved correctly
        $user->refresh();
        if ($user->otp && $user->otp_expires_at) {
            echo "- OTP hash updated in database\n";
            echo "- New OTP expires at: {$user->otp_expires_at}\n";
        } else {
            echo "- WARNING: OTP not updated in database\n";
        }
    } else {
        throw new Exception("API request failed with status: {$statusCode}");
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ], JSON_PRETTY_PRINT);
}
