<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\User;

// Test with a known phone number from your database
$phone = '9457508075';

try {
    // Get the user with the phone number
    $user = User::where('phone', $phone)->first();
    
    if (!$user) {
        die("User with phone {$phone} not found in the database.\n");
    }
    
    echo "User found in database:\n";
    echo "- ID: {$user->id}\n";
    echo "- Name: {$user->name}\n";
    echo "- Phone: {$user->phone}\n";
    echo "- OTP: " . ($user->otp ? 'Set' : 'Not set') . "\n";
    echo "- OTP Expires: " . ($user->otp_expires_at ? $user->otp_expires_at : 'Never') . "\n";
    
    // If OTP is set, verify it
    if ($user->otp) {
        echo "\nOTP is set. You can verify it using the following code:\n";
        echo "password_verify(\$otp, '{$user->otp}');\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
