<?php

// Simple test script to check OpenSSL and API responses

// Test OpenSSL
echo "=== OpenSSL Test ===\n";
$opensslLoaded = extension_loaded('openssl');
echo "OpenSSL Loaded: " . ($opensslLoaded ? 'Yes' : 'No') . "\n";

if ($opensslLoaded) {
    echo "OpenSSL Version: " . OPENSSL_VERSION_TEXT . "\n";
    
    // Test encryption
    $data = "test data";
    $method = 'AES-256-CBC';
    $key = 'test-key-12345678';
    $iv = '1234567890123456';
    
    $encrypted = @openssl_encrypt($data, $method, $key, 0, $iv);
    echo "Can Encrypt: " . ($encrypted !== false ? 'Yes' : 'No') . "\n";
    
    if ($encrypted) {
        $decrypted = @openssl_decrypt($encrypted, $method, $key, 0, $iv);
        echo "Can Decrypt: " . ($decrypted === $data ? 'Yes' : 'No') . "\n";
    }
}

// Test JSON response
echo "\n=== JSON Response Test ===\n";
$url = 'http://127.0.0.1:8000/api/test-json';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
curl_close($ch);

echo "URL: $url\n";
echo "Status: $httpCode\n";
echo "Content-Type: $contentType\n";

// Display first 200 chars of response
echo "Response (first 200 chars): " . substr($response, 0, 200) . "...\n";

// Check if response is JSON
$jsonPos = strpos($response, '{');
if ($jsonPos !== false) {
    $jsonStr = substr($response, $jsonPos);
    $json = @json_decode($jsonStr, true);
    echo "Valid JSON: " . (json_last_error() === JSON_ERROR_NONE ? 'Yes' : 'No') . "\n";
    if ($json) {
        echo "JSON Content:\n";
        print_r($json);
    }
}

// Check PHP configuration
echo "\n=== PHP Configuration ===\n";
echo "PHP Version: " . phpversion() . "\n";
echo "PHP Config File: " . php_ini_loaded_file() . "\n";
echo "OpenSSL Config: " . ini_get('openssl.openssl_conf') . "\n";

// Check JWT secret
echo "\n=== JWT Configuration ===\n";
$jwtSecret = getenv('JWT_SECRET');
echo "JWT_SECRET Set: " . ($jwtSecret ? 'Yes' : 'No') . "\n";
if ($jwtSecret) {
    echo "JWT_SECRET Length: " . strlen($jwtSecret) . "\n";
}

// Check if we can generate a JWT token
if ($opensslLoaded && $jwtSecret) {
    try {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode(['sub' => 1, 'iat' => time(), 'exp' => time() + 3600]);
        
        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $jwtSecret, true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        
        $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
        
        echo "JWT Token Generated: " . substr($jwt, 0, 30) . "...\n";
        echo "JWT Token Verified: Yes\n";
    } catch (Exception $e) {
        echo "JWT Generation Failed: " . $e->getMessage() . "\n";
    }
}

echo "\nTest completed.\n";
