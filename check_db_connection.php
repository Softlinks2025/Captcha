<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get database configuration
$connection = config('database.default');
$config = config("database.connections." . $connection);

echo "🔍 Database Configuration:\n";
echo "- Driver: " . $connection . "\n";
echo "- Host: " . ($config['host'] ?? 'not set') . "\n";
echo "- Port: " . ($config['port'] ?? '3306') . "\n";
echo "- Database: " . ($config['database'] ?? 'not set') . "\n";
echo "- Username: " . ($config['username'] ?? 'not set') . "\n";

// Test connection
try {
    DB::connection()->getPdo();
    echo "\n✅ Successfully connected to the database!\n";
    
    // Check if users table exists
    $tables = DB::select('SHOW TABLES');
    $tableName = 'Tables_in_' . $config['database'];
    
    echo "\n📊 Tables in database:\n";
    foreach ($tables as $table) {
        echo "- " . $table->$tableName . "\n";
    }
    
} catch (Exception $e) {
    echo "\n❌ Connection failed: " . $e->getMessage() . "\n";
}
