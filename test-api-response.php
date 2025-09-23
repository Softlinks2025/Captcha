<?php

// Test API Response Script
// This script tests if the API is returning proper JSON responses

// Configuration
$baseUrl = 'http://127.0.0.1:8000';
$endpoints = [
    '/api/test-json',
    '/test-json',
    '/test-json-file-loaded',
    '/api/test-json-file-loaded'
];

// Function to make HTTP requests
function testEndpoint($url) {
    $ch = curl_init();
    
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'X-Requested-With: XMLHttpRequest'
        ]
    ]);
    
    $response = curl_exec($ch);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headers = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    
    curl_close($ch);
    
    return [
        'url' => $url,
        'status' => $httpCode,
        'content_type' => $contentType,
        'headers' => $headers,
        'body' => $body,
        'is_json' => strpos($contentType, 'application/json') !== false,
        'json_decoded' => json_decode($body, true)
    ];
}

// Function to print test results
function printTestResult($result) {
    echo "\n";
    echo str_repeat("=", 80) . "\n";
    echo "TESTING: {$result['url']}\n";
    echo str_repeat("-", 80) . "\n";
    
    // Status
    $statusColor = $result['status'] >= 200 && $result['status'] < 300 ? '32' : '31';
    echo "Status: \033[1;{$statusColor}m{$result['status']}\033[0m\n";
    
    // Content Type
    $contentTypeColor = $result['is_json'] ? '32' : '31';
    echo "Content-Type: \033[1;{$contentTypeColor}m{$result['content_type']}\033[0m\n";
    
    // Response Body
    echo "\nResponse Body:\n";
    if ($result['is_json']) {
        echo json_encode($result['json_decoded'], JSON_PRETTY_PRINT) . "\n";
    } else {
        echo "Not JSON: " . substr($result['body'], 0, 200) . "...\n";
    }
    
    // Headers
    echo "\nResponse Headers:\n";
    $headers = explode("\r\n", trim($result['headers']));
    foreach ($headers as $header) {
        if (!empty($header)) {
            echo "  {$header}\n";
        }
    }
    
    echo str_repeat("=", 80) . "\n";
}

// Run tests
echo "Testing API Endpoints...\n";
foreach ($endpoints as $endpoint) {
    $url = $baseUrl . $endpoint;
    $result = testEndpoint($url);
    printTestResult($result);
}

// Additional OpenSSL tests
echo "\nTesting OpenSSL Configuration...\n";
$opensslTests = [
    'OpenSSL Loaded' => extension_loaded('openssl'),
    'OpenSSL Version' => defined('OPENSSL_VERSION_TEXT') ? OPENSSL_VERSION_TEXT : 'Not available',
    'PHP Version' => phpversion(),
    'Can Encrypt' => function_exists('openssl_encrypt'),
    'Can Decrypt' => function_exists('openssl_decrypt'),
    'JWT Secret' => getenv('JWT_SECRET') ? 'Set (' . str_repeat('*', 8) . ')' : 'Not set',
    'PHP Config File' => php_ini_loaded_file(),
    'OpenSSL Config' => ini_get('openssl.openssl_conf') ?: 'Not set in php.ini',
];

echo "\nOpenSSL Configuration:\n";
foreach ($opensslTests as $test => $value) {
    if (is_bool($value)) {
        $color = $value ? '32' : '31';
        $display = $value ? 'Yes' : 'No';
        echo "  \033[1m{$test}:\033[0m \033[1;{$color}m{$display}\033[0m\n";
    } else {
        echo "  \033[1m{$test}:\033[0m {$value}\n";
    }
}

// Test JWT token generation
if (extension_loaded('openssl') && getenv('JWT_SECRET')) {
    echo "\nTesting JWT Token Generation...\n";
    
    try {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode(['sub' => 1, 'iat' => time(), 'exp' => time() + 3600]);
        
        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, getenv('JWT_SECRET'), true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        
        $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
        
        echo "  \033[1mJWT Token Generated:\033[0m " . substr($jwt, 0, 30) . "...\n";
        echo "  \033[1mToken Verified:\033[0m \033[1;32mYes\033[0m\n";
    } catch (Exception $e) {
        echo "  \033[1;31mJWT Generation Failed:\033[0m " . $e->getMessage() . "\n";
    }
}

echo "\nTest completed. Check the output above for any issues.\n";
