<?php
// Display all errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to test API endpoint
function testApiEndpoint($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Content-Type: application/json'
    ]);
    
    $response = curl_exec($ch);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headers = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    
    curl_close($ch);
    
    return [
        'status' => $httpCode,
        'content_type' => $contentType,
        'headers' => $headers,
        'body' => $body,
        'is_json' => strpos($contentType, 'application/json') !== false,
        'json' => json_decode($body, true)
    ];
}

// Check OpenSSL
$opensslLoaded = extension_loaded('openssl');
$opensslVersion = $opensslLoaded ? OPENSSL_VERSION_TEXT : 'Not available';

// Check JWT_SECRET
$jwtSecret = getenv('JWT_SECRET');

// Output results
echo "=== PHP Configuration ===\n";
echo "PHP Version: " . phpversion() . "\n";
echo "PHP SAPI: " . php_sapi_name() . "\n";
echo "PHP Config File: " . php_ini_loaded_file() . "\n";

echo "\n=== OpenSSL Configuration ===\n";
echo "OpenSSL Loaded: " . ($opensslLoaded ? 'Yes' : 'No') . "\n";
echo "OpenSSL Version: " . $opensslVersion . "\n";

echo "\n=== JWT Configuration ===\n";
echo "JWT_SECRET Set: " . ($jwtSecret ? 'Yes' : 'No') . "\n";
if ($jwtSecret) {
    echo "JWT_SECRET Length: " . strlen($jwtSecret) . "\n";
}

// Test API endpoints if running in CLI
if (php_sapi_name() === 'cli') {
    $baseUrl = 'http://127.0.0.1:8000';
    $endpoints = [
        '/api/test-json',
        '/test-json',
        '/api/test-json-file-loaded'
    ];
    
    echo "\n=== Testing API Endpoints ===\n";
    foreach ($endpoints as $endpoint) {
        $url = $baseUrl . $endpoint;
        echo "\nTesting: {$url}\n";
        $result = testApiEndpoint($url);
        
        echo "Status: {$result['status']}\n";
        echo "Content-Type: {$result['content_type']}\n";
        echo "Is JSON: " . ($result['is_json'] ? 'Yes' : 'No') . "\n";
        
        if ($result['is_json'] && $result['json']) {
            echo "Response: " . json_encode($result['json'], JSON_PRETTY_PRINT) . "\n";
        } else {
            echo "Response: " . substr($result['body'], 0, 200) . "...\n";
        }
    }
} else {
    echo "\nNote: API tests are only run in CLI mode.\n";
}

echo "\n=== Test Complete ===\n";
