<?php

try {
    // Manually include the Laravel autoloader
    require __DIR__.'/vendor/autoload.php';
    
    // Create application instance
    $app = require_once __DIR__.'/bootstrap/app.php';
    
    // Run the application
    $kernel = $app->make(IllwareConsoleKernel::class);
    $kernel->bootstrap();
    
    // Now we can use Laravel's DB facade
    $pdo = DB::connection()->getPdo();
    echo "✓ Successfully connected to the database.\n";
    
    // Test users table
    $users = DB::table('users')->whereNotNull('phone')->take(3)->get();
    
    if ($users->isEmpty()) {
        echo "No users found with phone numbers.\n";
    } else {
        echo "\nFound users with phone numbers:\n";
        foreach ($users as $user) {
            echo "- ID: {$user->id}, Name: {$user->name}, Phone: {$user->phone}\n";
        }
        
        // Test OTP for first user
        $user = $users->first();
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $otpHash = password_hash($otp, PASSWORD_DEFAULT);
        
        $updated = DB::table('users')
            ->where('id', $user->id)
            ->update([
                'otp' => $otpHash,
                'otp_expires_at' => now()->addMinutes(10)->toDateTimeString()
            ]);
            
        if ($updated) {
            echo "\n✓ Successfully updated OTP for user ID {$user->id}\n";
            echo "- OTP: $otp (hashed in database)\n";
            echo "- Expires at: " . now()->addMinutes(10)->toDateTimeString() . "\n";
        } else {
            echo "\n✗ Failed to update OTP for user ID {$user->id}\n";
        }
    }
    
} catch (Exception $e) {
    echo "\n✗ Error: " . $e->getMessage() . "\n";
    if (strpos($e->getMessage(), 'SQLSTATE') !== false) {
        echo "SQL Error - Check your database configuration.\n";
    }
    exit(1);
}

echo "\nTest completed!\n";
