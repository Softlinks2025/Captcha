<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(IllwareConsoleKernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\User;

// Test database connection
try {
    echo "Testing database connection...\n";
    DB::connection()->getPdo();
    echo "✓ Database connection successful\n\n";
    
    // Get a user with a phone number
    $user = User::whereNotNull('phone')->first();
    
    if (!$user) {
        echo "No users with phone numbers found in the database.\n";
        exit(1);
    }
    
    echo "Found user:\n";
    echo "- ID: {$user->id}\n";
    echo "- Name: {$user->name}\n";
    echo "- Phone: {$user->phone}\n\n";
    
    // Test OTP sending
    echo "Testing OTP sending...\n";
    $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $user->otp = \Illuminate\Support\Facades\Hash::make($otp);
    $user->otp_expires_at = now()->addMinutes(10);
    
    if ($user->save()) {
        echo "✓ OTP saved successfully\n";
        echo "- OTP: $otp (hashed in database)\n";
        echo "- Expires at: {$user->otp_expires_at}\n";
    } else {
        echo "✗ Failed to save OTP\n";
    }
    
} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    if (strpos($e->getMessage(), 'SQLSTATE') !== false) {
        echo "\nSQL Error - Check database configuration and tables.\n";
    }
    exit(1);
}

echo "\nTest completed successfully!\n";
