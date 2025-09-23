<?php
// Script to fix JWT and OpenSSL configuration issues

// Load Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

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
echo "JWT & OPENSSL CONFIGURATION FIX SCRIPT\n";
echo str_repeat("=", 80) . "\n\n";

// 1. Check and fix JWT secret
try {
    display("Checking JWT secret...");
    
    $envPath = base_path('.env');
    $envContent = File::exists($envPath) ? File::get($envPath) : '';
    
    // Check if JWT_SECRET exists
    if (!preg_match('/JWT_SECRET=/', $envContent)) {
        display("JWT_SECRET not found in .env. Generating a new one...");
        Artisan::call('jwt:secret', ['--force' => true]);
        display("New JWT secret generated successfully!");
    } else {
        display("JWT_SECRET already exists in .env");
    }
    
    // Display current JWT secret (first 10 chars for security)
    $jwtSecret = env('JWT_SECRET');
    display("JWT Secret (first 10 chars): " . ($jwtSecret ? substr($jwtSecret, 0, 10) . '...' : 'Not set'));
    
} catch (\Exception $e) {
    display("Error setting JWT secret: " . $e->getMessage());
}

// 2. Check OpenSSL configuration
try {
    display("Checking OpenSSL configuration...");
    
    // Check if OpenSSL extension is loaded
    if (!extension_loaded('openssl')) {
        die("\n✗ OpenSSL extension is not loaded. This is required for JWT token generation.\n" .
            "Please enable the OpenSSL extension in your php.ini file.\n");
    }
    
    display("OpenSSL extension is loaded");
    
    // Check OpenSSL configuration
    $config = @openssl_get_cert_locations();
    display("OpenSSL Configuration:", $config);
    
    // Test OpenSSL functions
    $testData = 'test_data';
    $key = 'test_key';
    $signature = hash_hmac('sha256', $testData, $key);
    
    display("OpenSSL Test:", [
        'HMAC-SHA256 Test' => $signature ? 'Success' : 'Failed',
        'Signature (first 10 chars)' => substr($signature, 0, 10) . '...'
    ]);
    
} catch (\Exception $e) {
    display("Error checking OpenSSL: " . $e->getMessage());
}

// 3. Check JWT configuration
try {
    display("Checking JWT configuration...");
    
    $jwtConfig = config('jwt');
    
    // Hide sensitive data
    if (isset($jwtConfig['secret'])) {
        $jwtConfig['secret'] = substr($jwtConfig['secret'], 0, 5) . '...';
    }
    
    display("JWT Configuration:", $jwtConfig);
    
    // Check if JWT secret is set
    if (empty($jwtConfig['secret'])) {
        display("✗ JWT secret is not set. Please run: php artisan jwt:secret");
    } else {
        display("✓ JWT secret is set");
    }
    
} catch (\Exception $e) {
    display("Error checking JWT configuration: " . $e->getMessage());
}

// 4. Test JWT token generation
try {
    display("Testing JWT token generation...");
    
    // Get a test user
    $user = \App\Models\User::first();
    
    if (!$user) {
        display("No users found in the database. Please create a user first.");
    } else {
        display("Testing with user:", [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email
        ]);
        
        // Try to generate a token
        $token = auth('api')->login($user);
        
        if ($token) {
            display("✓ JWT token generated successfully!");
            display("Token (first 20 chars): " . substr($token, 0, 20) . '...');
            
            // Verify the token
            $payload = auth('api')->payload();
            display("Token Payload:", $payload->toArray());
            
        } else {
            display("✗ Failed to generate JWT token");
        }
    }
    
} catch (\Exception $e) {
    display("Error generating JWT token: " . $e->getMessage());
    display("Stack Trace:", $e->getTraceAsString());
}

display("Fix script completed. Please check the output above for any issues that need to be resolved.");
display("If you're still having issues, please check the following:");
echo "1. Make sure the OpenSSL extension is properly installed and enabled in php.ini\n";
echo "2. Check that the .env file has the correct permissions and contains the JWT_SECRET\n";
echo "3. Verify that the storage directory is writable\n";
echo "4. Check the Laravel logs for any additional error messages\n\n";
