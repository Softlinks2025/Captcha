<?php
// Simple JWT token generation test without Laravel

// Enable all error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Simple JWT implementation for testing
class SimpleJWT {
    public static function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    public static function generate($payload, $secret, $algorithm = 'HS256') {
        $header = [
            'typ' => 'JWT',
            'alg' => $algorithm
        ];
        
        $header = json_encode($header);
        $payload = json_encode($payload);
        
        $base64UrlHeader = self::base64UrlEncode($header);
        $base64UrlPayload = self::base64UrlEncode($payload);
        
        $signature = hash_hmac('sha256', "$base64UrlHeader.$base64UrlPayload", $secret, true);
        $base64UrlSignature = self::base64UrlEncode($signature);
        
        return "$base64UrlHeader.$base64UrlPayload.$base64UrlSignature";
    }
}

// Test function to check if we can generate a JWT token
function testJwtGeneration() {
    echo "=== Testing JWT Token Generation ===\n\n";
    
    // Test data
    $payload = [
        'sub' => '1234567890',
        'name' => 'Test User',
        'iat' => time(),
        'exp' => time() + 3600
    ];
    
    $secret = 'test-secret-key';
    
    try {
        echo "Generating JWT token...\n";
        $token = SimpleJWT::generate($payload, $secret);
        
        if ($token) {
            echo "✓ JWT token generated successfully!\n";
            echo "Token: $token\n";
            
            // Verify the token structure
            $parts = explode('.', $token);
            if (count($parts) === 3) {
                echo "✓ Token has correct structure (3 parts)\n";
            } else {
                echo "✗ Token has incorrect structure\n";
            }
            
            return true;
        } else {
            echo "✗ Failed to generate JWT token\n";
            return false;
        }
    } catch (Exception $e) {
        echo "✗ Error generating JWT token: " . $e->getMessage() . "\n";
        return false;
    }
}

// Run the test
testJwtGeneration();

echo "\n=== Test Completed ===\n";
