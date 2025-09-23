<?php

// Database configuration
$config = [
    'host' => '127.0.0.1',
    'port' => '3306',
    'database' => 'captcha_platform',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
];

try {
    // Create a PDO instance
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    $pdo = new PDO($dsn, $config['username'], $config['password'], $options);
    
    echo "✅ Successfully connected to the database!\n";
    
    // Check if users table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Users table exists\n";
        
        // Check if profile_completed column exists
        $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'profile_completed'");
        if ($stmt->rowCount() > 0) {
            echo "❌ profile_completed column already exists\n";
        } else {
            echo "ℹ️ profile_completed column does not exist. It will be created.\n";
            
            // Add the column
            $pdo->exec("ALTER TABLE users ADD COLUMN profile_completed TINYINT(1) NOT NULL DEFAULT 0 AFTER is_verified");
            echo "✅ Added profile_completed column to users table\n";
        }
    } else {
        echo "❌ Users table does not exist\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Connection failed: " . $e->getMessage() . "\n";
    echo "DSN: $dsn\n";
    echo "Username: {$config['username']}\n";
}
