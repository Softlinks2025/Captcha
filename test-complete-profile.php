<?php
// Test script for complete profile API with JWT authentication

// Display all errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to make API request with JWT token
function makeApiRequest($url, $method = 'GET', $data = [], $token = null) {
    $ch = curl_init();
    
    $headers = [
        'Accept: application/json',
        'Content-Type: application/json',
    ];
    
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    
    $options = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_VERBOSE => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 0,
    ];
    
    if ($method === 'POST') {
        $options[CURLOPT_POST] = true;
        $options[CURLOPT_POSTFIELDS] = json_encode($data);
    }
    
    curl_setopt_array($ch, $options);
    
    $response = curl_exec($ch);
    $error = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    
    $headers = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
    
    curl_close($ch);
    
    return [
        'status' => $httpCode,
        'headers' => $headers,
        'body' => $body,
        'json' => json_decode($body, true),
        'error' => $error,
    ];
}

// Test login to get JWT token
echo "=== Testing Login to Get JWT Token ===\n";
$loginData = [
    'phone' => '1234567890', // Replace with registered phone number
    'otp' => '123456',      // Replace with valid OTP
];

$loginUrl = 'http://127.0.0.1:8000/api/v1/login';
$loginResult = makeApiRequest($loginUrl, 'POST', $loginData);

if ($loginResult['status'] !== 200) {
    echo "Login failed with status: " . $loginResult['status'] . "\n";
    echo "Response: " . $loginResult['body'] . "\n";
    exit(1);
}

// Extract JWT token from login response
$loginResponse = json_decode($loginResult['body'], true);
$token = $loginResponse['access_token'] ?? null;

if (!$token) {
    echo "No access token received in login response\n";
    echo "Response: " . $loginResult['body'] . "\n";
    exit(1);
}

echo "Successfully obtained JWT token: " . substr($token, 0, 30) . "...\n\n";

// Test complete profile API
echo "=== Testing Complete Profile API ===\n";
$profileData = [
    'name' => 'Test User',
    'email' => 'test@example.com',
    // Add other required fields for complete profile
];

$profileUrl = 'http://127.0.0.1:8000/api/v1/profile/complete';
$profileResult = makeApiRequest($profileUrl, 'POST', $profileData, $token);

echo "Status Code: " . $profileResult['status'] . "\n";
echo "Response: " . json_encode($profileResult['json'] ?? $profileResult['body'], JSON_PRETTY_PRINT) . "\n";

if (!empty($profileResult['error'])) {
    echo "Error: " . $profileResult['error'] . "\n";
}

// Display response headers for debugging
echo "\n=== Response Headers ===\n";
$headerLines = explode("\r\n", $profileResult['headers']);
foreach ($headerLines as $header) {
    if (!empty(trim($header))) {
        echo $header . "\n";
    }
}

echo "\nTest completed.\n";
