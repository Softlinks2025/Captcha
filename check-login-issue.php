<?php

// Enable all error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Load Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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
echo "LOGIN ISSUE DIAGNOSTIC SCRIPT\n";
echo str_repeat("=", 80) . "\n\n";

// 1. Check database connection
try {
    display("Testing database connection...");
    DB::connection()->getPdo();
    display("✓ Database connection successful");
} catch (\Exception $e) {
    die("✗ Database connection failed: " . $e->getMessage() . "\n");
}

// 2. Check users table
try {
    display("Checking users table...");
    $tables = DB::select('SHOW TABLES');
    $tables = array_map('current', (array) $tables);
    
    if (!in_array('users', $tables)) {
        die("✗ Users table does not exist in the database.\n");
    }
    
    display("✓ Users table exists");
    
    // 3. Check user with phone number
    $phone = '9457508075';
    $user = DB::table('users')->where('phone', $phone)->first();
    
    if (!$user) {
        // Show all users for debugging
        $allUsers = DB::table('users')->select('id', 'name', 'email', 'phone', 'created_at')
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get();
            
        display("✗ User with phone {$phone} not found. Here are the most recent users:", $allUsers->toArray());
        die();
    }
    
    display("✓ User found in database", [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'phone' => $user->phone,
        'otp_set' => !empty($user->otp) ? 'Yes' : 'No',
        'otp_expires_at' => $user->otp_expires_at,
        'created_at' => $user->created_at,
        'updated_at' => $user->updated_at
    ]);
    
    // 4. Check JWT configuration
    display("Checking JWT configuration...");
    $jwtSecret = config('jwt.secret');
    $jwtAlgo = config('jwt.algo', 'HS256');
    
    display("JWT Configuration", [
        'secret_set' => !empty($jwtSecret) ? 'Yes' : 'No',
        'algorithm' => $jwtAlgo,
        'note' => !empty($jwtSecret) ? 'JWT secret is set' : 'JWT secret is not set. Run: php artisan jwt:secret'
    ]);
    
    if (empty($jwtSecret)) {
        die("✗ JWT secret is not set. Please run: php artisan jwt:secret\n");
    }
    
    // 5. Check if we can generate a JWT token
    try {
        $token = auth('api')->login($user);
        
        if (!$token) {
            throw new \Exception("Failed to generate JWT token");
        }
        
        display("✓ JWT token generated successfully", [
            'token' => substr($token, 0, 20) . '...',
            'token_length' => strlen($token),
            'expires_in' => auth('api')->factory()->getTTL() . ' minutes'
        ]);
        
    } catch (\Exception $e) {
        display("✗ JWT Token generation failed", [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
    }
    
    // 6. Check OTP verification
    if (!empty($user->otp)) {
        $testOtp = '123456'; // Default test OTP
        $otpMatches = Hash::check($testOtp, $user->otp);
        
        display("OTP Verification", [
            'otp_set' => 'Yes',
            'otp_expires_at' => $user->otp_expires_at,
            'is_expired' => $user->otp_expires_at && now()->gt($user->otp_expires_at) ? 'Yes' : 'No',
            'test_otp' => $testOtp,
            'otp_matches' => $otpMatches ? 'Yes' : 'No',
            'note' => $otpMatches ? 'OTP matches!' : 'OTP does not match. The test OTP may be incorrect.'
        ]);
    } else {
        display("OTP Verification", [
            'otp_set' => 'No',
            'note' => 'No OTP is set for this user. Please request a new OTP.'
        ]);
    }
    
} catch (\Exception $e) {
    display("✗ Error checking users table", [
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
}

echo "\n" . str_repeat("=", 80) . "\n";
echo "DIAGNOSTIC COMPLETE\n";
echo str_repeat("=", 80) . "\n\n";

echo "Recommendations based on the diagnostic results:\n";
echo "1. If JWT secret is not set, run: php artisan jwt:secret\n";
echo "2. If user has no OTP, request a new OTP for the user\n";
echo "3. If OTP doesn't match, ensure you're using the correct OTP\n";
echo "4. If JWT token generation fails, check your JWT configuration and PHP OpenSSL extension\n\n";
