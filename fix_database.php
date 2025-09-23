<?php

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$config = [
    'host' => '127.0.0.1',
    'port' => '3307',
    'database' => 'captcha',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
];

function testConnection($config) {
    echo "🔍 Testing database connection...\n";
    echo "- Host: {$config['host']}\n";
    echo "- Port: {$config['port']}\n";
    echo "- Database: {$config['database']}\n";
    echo "- Username: {$config['username']}\n";
    
    try {
        $dsn = "mysql:host={$config['host']};port={$config['port']};charset={$config['charset']}";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        // First try to connect without database name
        $pdo = new PDO($dsn, $config['username'], $config['password'], $options);
        echo "✅ Connected to MySQL server successfully!\n";
        
        // Check if database exists
        $stmt = $pdo->query("SHOW DATABASES LIKE '{$config['database']}'");
        if ($stmt->rowCount() === 0) {
            echo "❌ Database '{$config['database']}' does not exist.\n";
            echo "   Please create the database first.\n";
            return false;
        }
        
        // Now connect to the specific database
        $pdo = new PDO(
            "{$dsn};dbname={$config['database']}",
            $config['username'],
            $config['password'],
            $options
        );
        
        echo "✅ Connected to database '{$config['database']}' successfully!\n";
        
        // Check if users table exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
        if ($stmt->rowCount() === 0) {
            echo "❌ Users table does not exist.\n";
            return false;
        }
        
        echo "✅ Users table exists.\n";
        
        // Check if profile_completed column exists
        $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'profile_completed'");
        if ($stmt->rowCount() > 0) {
            echo "✅ profile_completed column already exists.\n";
            return true;
        }
        
        // Add the column if it doesn't exist
        echo "ℹ️ Adding profile_completed column to users table...\n";
        $pdo->exec("ALTER TABLE users ADD COLUMN profile_completed TINYINT(1) NOT NULL DEFAULT 0 AFTER is_verified");
        echo "✅ Added profile_completed column to users table.\n";
        
        return true;
        
    } catch (PDOException $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
        return false;
    }
}

// Run the test
testConnection($config);

echo "\nScript completed.\n";
