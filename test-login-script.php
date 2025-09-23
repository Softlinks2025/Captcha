<?php

// Enable all error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Start output buffering to capture all output
ob_start();

echo "Starting login test script...\n";

// Load Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Log;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

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

// Test with a known phone number from your database
$phone = '9457508075';
$otp = '123456'; // Test OTP - update this to match what's in your database

// Clear any existing output
ob_clean();

display("Test Login Script Started", [
    'phone' => $phone,
    'time' => date('Y-m-d H:i:s')
]);

// Test database connection
try {
    display("Testing database connection...");
    DB::connection()->getPdo();
    display("Database connection successful");
} catch (\Exception $e) {
    die("✗ Database connection failed: " . $e->getMessage() . "\n");
}

try {
    // 1. Check if users table exists
    display("Checking if users table exists...");
    $tableExists = DB::select("SHOW TABLES LIKE 'users'");
    if (empty($tableExists)) {
        die("✗ Users table does not exist in the database.\n");
    }
    display("Users table exists");
    
    // 2. Check if user exists
    display("Searching for user with phone: " . $phone);
    $user = User::where('phone', $phone)->first();
    
    if (!$user) {
        // Output all users for debugging
        $allUsers = User::all();
        display("No user found with phone: {$phone}", [
            'total_users_in_database' => $allUsers->count(),
            'sample_users' => $allUsers->take(3)->map(function($u) {
                return [
                    'id' => $u->id,
                    'phone' => $u->phone,
                    'created_at' => $u->created_at
                ];
            })
        ]);
        die("✗ User with phone {$phone} not found in the database.\n");
    }
    
    display("User found in database", [
        'id' => $user->id,
        'phone' => $user->phone,
        'otp_set' => !empty($user->otp) ? 'Yes' : 'No',
        'otp_expires_at' => $user->otp_expires_at,
        'created_at' => $user->created_at,
        'updated_at' => $user->updated_at
    ]);
    
    // 3. Check if OTP is set
    if (empty($user->otp)) {
        display("No OTP set for this user. Please request a new OTP.");
        
        // Check if we can update the OTP
        try {
            $testOtp = '123456';
            $user->otp = Hash::make($testOtp);
            $user->otp_expires_at = now()->addMinutes(10);
            $user->save();
            
            display("Test OTP has been set for this user", [
                'otp' => $testOtp,
                'expires_at' => $user->otp_expires_at
            ]);
            
            // Refresh user from database
            $user = $user->fresh();
            
        } catch (\Exception $e) {
            display("Failed to set test OTP", [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
    }
    
    display("OTP is set for this user");
    
    // 4. Check OTP expiration
    $now = now();
    $expiresAt = $user->otp_expires_at;
    
    if ($expiresAt && $now->gt($expiresAt)) {
        display("OTP has expired", [
            'expired_at' => $expiresAt,
            'current_time' => $now,
            'minutes_since_expiry' => $now->diffInMinutes($expiresAt)
        ]);
        
        // Optionally update the OTP
        try {
            $newOtp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $user->otp = Hash::make($newOtp);
            $user->otp_expires_at = now()->addMinutes(10);
            $user->save();
            
            display("New OTP has been generated", [
                'otp' => $newOtp,
                'expires_at' => $user->otp_expires_at
            ]);
            
            // Update the OTP variable for verification
            $otp = $newOtp;
            
            // Refresh user from database
            $user = $user->fresh();
            
        } catch (\Exception $e) {
            display("Failed to generate new OTP", [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            die("✗ Please generate a new OTP and try again.\n");
        }
    }
    
    echo "✓ OTP is not expired. Expires at: " . ($expiresAt ? $expiresAt : 'Never') . "\n";
    
    // 5. Verify OTP
    display("Verifying OTP...", [
        'provided_otp' => $otp,
        'stored_otp_hash' => substr($user->otp, 0, 30) . '...',
        'hash_type' => 'bcrypt'
    ]);
    
    if (Hash::check($otp, $user->otp)) {
        display("OTP verification successful!");
        
        // 6. Attempt to generate JWT token
        try {
            display("Attempting to generate JWT token...");
            
            // Check JWT configuration
            $jwtSecret = config('jwt.secret');
            $jwtAlgo = config('jwt.algo', 'HS256');
            
            display("JWT Configuration", [
                'secret_set' => !empty($jwtSecret) ? 'Yes' : 'No',
                'algorithm' => $jwtAlgo
            ]);
            
            if (empty($jwtSecret)) {
                throw new \Exception("JWT secret key is not set. Please run: php artisan jwt:secret");
            }
            
            // Generate token
            $token = auth('api')->login($user);
            
            if (!$token) {
                throw new \Exception("Failed to generate JWT token. Auth driver: " . config('auth.defaults.guard'));
            }
            
            display("JWT token generated successfully!", [
                'token' => substr($token, 0, 20) . '...',
                'token_length' => strlen($token),
                'expires_in' => auth('api')->factory()->getTTL() . ' minutes'
            ]);
            
            // 7. Get authenticated user
            $authenticatedUser = auth('api')->user();
            if ($authenticatedUser) {
                display("Successfully authenticated user", [
                    'id' => $authenticatedUser->id,
                    'name' => $authenticatedUser->name,
                    'email' => $authenticatedUser->email,
                    'phone' => $authenticatedUser->phone
                ]);
            } else {
                throw new \Exception("Failed to authenticate user with JWT token");
            }
            
        } catch (\Exception $e) {
            display("JWT Token generation failed", [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'code' => $e->getCode(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Additional debug for JWT issues
            if (strpos($e->getMessage(), 'openssl') !== false) {
                display("OpenSSL Extension Check", [
                    'openssl_loaded' => extension_loaded('openssl') ? 'Yes' : 'No',
                    'openssl_algorithms' => function_exists('openssl_get_cipher_methods') ? 
                        openssl_get_cipher_methods() : 'Not available'
                ]);
            }
        }
        
    } else {
        display("Invalid OTP", [
            'provided_otp' => $otp,
            'stored_hash' => $user->otp,
            'hash_algorithm' => 'bcrypt',
            'note' => 'Make sure the OTP matches what was sent to the user'
        ]);
        
        // Try to verify the OTP directly with the database
        try {
            $dbUser = DB::table('users')
                ->where('phone', $phone)
                ->select('otp', 'otp_expires_at')
                ->first();
                
            if ($dbUser) {
                display("Direct database OTP verification", [
                    'db_otp_hash' => $dbUser->otp ? substr($dbUser->otp, 0, 30) . '...' : 'null',
                    'db_otp_expires' => $dbUser->otp_expires_at,
                    'hash_check' => Hash::check($otp, $dbUser->otp) ? 'Match' : 'No match'
                ]);
            }
        } catch (\Exception $dbE) {
            display("Error during direct database OTP check", [
                'error' => $dbE->getMessage()
            ]);
        }
    }
    
} catch (\Exception $e) {
    display("Unhandled Exception", [
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'code' => $e->getCode(),
        'exception' => get_class($e),
        'trace' => $e->getTraceAsString()
    ]);
    
    if (strpos($e->getMessage(), 'SQLSTATE') !== false) {
        display("Database Error", [
            'connection' => config('database.default'),
            'database' => config("database.connections." . config('database.default') . ".database"),
            'tables' => DB::select('SHOW TABLES')
        ]);
    }
}

// Display final output
$output = ob_get_clean();
echo "\n" . str_repeat("=", 80) . "\n";
echo "TEST LOGIN SCRIPT COMPLETED\n";
echo str_repeat("=", 80) . "\n\n";
echo $output;

display("Final System Checks", [
    'php_version' => PHP_VERSION,
    'laravel_version' => app()->version(),
    'timezone' => config('app.timezone'),
    'env' => app()->environment(),
    'debug_mode' => config('app.debug') ? 'ON' : 'OFF',
    'memory_usage' => round(memory_get_peak_usage(true) / 1024 / 1024, 2) . 'MB',
    'execution_time' => round((microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]), 2) . 's'
]);

echo "\n" . str_repeat("=", 80) . "\n\n";
