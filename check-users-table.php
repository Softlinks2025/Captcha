<?php

try {
    // Include the Composer autoloader
    require __DIR__.'/vendor/autoload.php';
    
    // Bootstrap Laravel
    $app = require_once __DIR__.'/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    
    // Get database connection
    $pdo = DB::connection()->getPdo();
    
    // Check if users table exists
    $tableExists = DB::select("SHOW TABLES LIKE 'users'");
    
    if (empty($tableExists)) {
        die("✗ The 'users' table does not exist in the database.\n");
    }
    
    echo "✓ 'users' table exists.\n\n";
    
    // Get table structure
    $columns = DB::select('DESCRIBE users');
    
    echo "Table structure for 'users':\n";
    echo str_pad('Field', 20) . str_pad('Type', 20) . str_pad('Null', 10) . str_pad('Key', 10) . "Default\n";
    echo str_repeat('-', 70) . "\n";
    
    foreach ($columns as $column) {
        echo str_pad($column->Field, 20) . 
             str_pad($column->Type, 20) . 
             str_pad($column->Null, 10) . 
             str_pad($column->Key, 10) . 
             ($column->Default ?? 'NULL') . "\n";
    }
    
    // Check for required OTP columns
    $requiredColumns = ['otp', 'otp_expires_at', 'phone'];
    $missingColumns = [];
    
    foreach ($requiredColumns as $column) {
        $exists = false;
        foreach ($columns as $col) {
            if ($col->Field === $column) {
                $exists = true;
                break;
            }
        }
        if (!$exists) {
            $missingColumns[] = $column;
        }
    }
    
    if (!empty($missingColumns)) {
        echo "\n✗ Missing required columns: " . implode(', ', $missingColumns) . "\n";
        echo "Run the following SQL to add missing columns:\n";
        
        $sql = [];
        if (in_array('otp', $missingColumns)) {
            $sql[] = "ALTER TABLE users ADD COLUMN otp VARCHAR(255) NULL AFTER password;";
        }
        if (in_array('otp_expires_at', $missingColumns)) {
            $sql[] = "ALTER TABLE users ADD COLUMN otp_expires_at TIMESTAMP NULL AFTER otp;";
        }
        if (in_array('phone', $missingColumns)) {
            $sql[] = "ALTER TABLE users ADD COLUMN phone VARCHAR(15) NULL UNIQUE AFTER email;";
        }
        
        echo implode("\n", $sql) . "\n";
    } else {
        echo "\n✓ All required OTP columns exist.\n";
    }
    
    // Try to get a sample user with phone number
    echo "\nSample user with phone number:\n";
    $user = DB::table('users')
        ->whereNotNull('phone')
        ->where('phone', '!=', '')
        ->first();
        
    if ($user) {
        echo "- ID: {$user->id}\n";
        echo "- Name: {$user->name}\n";
        echo "- Email: {$user->email}\n";
        echo "- Phone: {$user->phone}\n";
        echo "- OTP: " . ($user->otp ? 'Set' : 'Not set') . "\n";
        echo "- OTP Expires: " . ($user->otp_expires_at ?? 'Never') . "\n";
    } else {
        echo "No users with phone numbers found in the database.\n";
    }
    
} catch (Exception $e) {
    echo "\n✗ Error: " . $e->getMessage() . "\n";
    if (strpos($e->getMessage(), 'SQLSTATE') !== false) {
        echo "SQL Error - Check your database configuration.\n";
    }
    exit(1);
}
