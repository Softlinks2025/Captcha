<?php

// Simple script to test OTP functionality with SQLite

// Initialize SQLite database connection
$dbFile = __DIR__ . '/database/database.sqlite';

// Create database file if it doesn't exist
if (!file_exists($dbFile)) {
    touch($dbFile);
}

try {
    // Connect to SQLite database
    $pdo = new PDO("sqlite:" . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✓ Connected to SQLite database successfully\n\n";
    
    // Check if users table exists
    $tableExists = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'")->fetchColumn();
    
    if (!$tableExists) {
        die("✗ 'users' table does not exist in the database.\n");
    }
    
    echo "✓ 'users' table exists\n\n";
    
    // Get a sample user with phone number
    $stmt = $pdo->query("SELECT * FROM users WHERE phone IS NOT NULL AND phone != '' LIMIT 1");
    $user = $stmt->fetch(PDO::FETCH_OBJ);
    
    if (!$user) {
        die("✗ No users with phone numbers found in the database.\n");
    }
    
    echo "✓ Found user with phone number:\n";
    echo "- ID: {$user->id}\n";
    echo "- Name: {$user->name}\n";
    echo "- Phone: {$user->phone}\n";
    echo "- Current OTP: " . ($user->otp ? 'Set' : 'Not set') . "\n";
    echo "- OTP Expires: " . ($user->otp_expires_at ?: 'Never') . "\n\n";
    
    // Generate a new OTP
    $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $otpHash = password_hash($otp, PASSWORD_DEFAULT);
    $otpExpiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));
    
    echo "Generated OTP: {$otp}\n";
    echo "OTP Hash: " . substr($otpHash, 0, 20) . "...\n";
    echo "Expires at: {$otpExpiresAt}\n\n";
    
    // Update the user with the new OTP
    $updateStmt = $pdo->prepare('UPDATE users SET otp = ?, otp_expires_at = ? WHERE id = ?');
    $result = $updateStmt->execute([$otpHash, $otpExpiresAt, $user->id]);
    
    if ($result) {
        echo "✓ Successfully updated OTP in database\n\n";
        
        // Verify the OTP was saved correctly
        $verifyStmt = $pdo->prepare('SELECT otp, otp_expires_at FROM users WHERE id = ?');
        $verifyStmt->execute([$user->id]);
        $updatedUser = $verifyStmt->fetch(PDO::FETCH_OBJ);
        
        if ($updatedUser && $updatedUser->otp) {
            echo "✓ OTP hash was saved to database\n";
            
            // Test OTP verification
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
            echo "✗ Failed to verify OTP was saved\n";
        }
    } else {
        echo "✗ Failed to update OTP in database\n";
    }
    
} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
    echo "Error Code: " . $e->getCode() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\nTest completed.\n";
