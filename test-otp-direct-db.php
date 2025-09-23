<?php

// Simple script to test OTP functionality directly with the database

echo "Testing OTP functionality...\n\n";

// Database configuration - update these with your actual database credentials
$dbConfig = [
    'host' => '127.0.0.1',
    'database' => 'your_database_name',
    'username' => 'your_username',
    'password' => 'your_password',
];

try {
    // Connect to the database
    $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $options);
    
    echo "✓ Connected to database successfully\n\n";
    
    // Test phone number - replace with an actual phone number from your users table
    $testPhone = '9457508075';
    
    // 1. Check if user exists
    $stmt = $pdo->prepare('SELECT * FROM users WHERE phone = ?');
    $stmt->execute([$testPhone]);
    $user = $stmt->fetch();
    
    if (!$user) {
        die("✗ User with phone {$testPhone} not found in the database.\n");
    }
    
    echo "✓ User found in database:\n";
    echo "- ID: {$user->id}\n";
    echo "- Name: {$user->name}\n";
    echo "- Phone: {$user->phone}\n";
    echo "- Current OTP: " . ($user->otp ? 'Set' : 'Not set') . "\n";
    echo "- OTP Expires: " . ($user->otp_expires_at ?: 'Never') . "\n\n";
    
    // 2. Generate and save a new OTP
    $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $otpHash = password_hash($otp, PASSWORD_DEFAULT);
    $otpExpiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));
    
    echo "Generated OTP: {$otp}\n";
    echo "OTP Hash: {$otpHash}\n";
    echo "Expires at: {$otpExpiresAt}\n\n";
    
    // 3. Update the user with the new OTP
    $updateStmt = $pdo->prepare('UPDATE users SET otp = ?, otp_expires_at = ? WHERE id = ?');
    $result = $updateStmt->execute([$otpHash, $otpExpiresAt, $user->id]);
    
    if ($result) {
        echo "✓ Successfully updated OTP in database\n\n";
        
        // 4. Verify the OTP was saved correctly
        $verifyStmt = $pdo->prepare('SELECT otp, otp_expires_at FROM users WHERE id = ?');
        $verifyStmt->execute([$user->id]);
        $updatedUser = $verifyStmt->fetch();
        
        if ($updatedUser->otp === $otpHash) {
            echo "✓ OTP hash matches the one we just saved\n";
        } else {
            echo "✗ OTP hash does not match!\n";
        }
        
        echo "- Stored OTP hash: " . substr($updatedUser->otp, 0, 20) . "...\n";
        echo "- Expires at: {$updatedUser->otp_expires_at}\n\n";
        
        // 5. Test OTP verification
        if (password_verify($otp, $updatedUser->otp)) {
            echo "✓ OTP verification successful!\n";
            
            // Check if OTP is expired
            $now = new DateTime();
            $expiresAt = new DateTime($updatedUser->otp_expires_at);
            
            if ($now > $expiresAt) {
                echo "✗ OTP has expired!\n";
            } else {
                $timeLeft = $now->diff($expiresAt);
                echo "- OTP is valid for: {$timeLeft->i} minutes and {$timeLeft->s} seconds\n";
            }
        } else {
            echo "✗ OTP verification failed!\n";
        }
    } else {
        echo "✗ Failed to update OTP in database\n";
    }
    
} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
    echo "Error Code: " . $e->getCode() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} finally {
    if (isset($pdo)) {
        $pdo = null; // Close connection
    }
}

echo "\nTest completed.\n";
