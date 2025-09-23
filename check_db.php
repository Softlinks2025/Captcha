<?php

require __DIR__.'/bootstrap/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    // Test database connection
    DB::connection()->getPdo();
    echo "✅ Database connection successful\n";
    
    // Check if users table exists
    $tables = DB::select('SHOW TABLES');
    $tables = array_map('current', array_map('array_values', (array) $tables));
    
    echo "\n📊 Tables in database:\n";
    foreach ($tables as $table) {
        echo "- $table\n";
    }
    
    // Check users table structure
    if (in_array('users', $tables)) {
        echo "\n🔍 Users table structure:\n";
        $columns = DB::select('DESCRIBE users');
        foreach ($columns as $column) {
            echo "- {$column->Field} ({$column->Type})\n";
        }
    } else {
        echo "\n❌ Users table does not exist\n";
    }
    
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    echo "Using database: " . DB::connection()->getDatabaseName() . "\n";
    echo "Database host: " . config('database.connections.mysql.host') . "\n";
}
