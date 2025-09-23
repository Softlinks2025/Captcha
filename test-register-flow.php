<?php
// Test script to verify registration and token generation flow

// Display all errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to make API request
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

// Generate a random phone number for testing
$phoneNumber = '9' . str_pad(rand(0, 999999999), 9, '0', STR_PAD_LEFT);
$otp = '123456'; // Default OTP for testing

// Step 1: Send OTP
echo "=== Step 1: Sending OTP ===\n";
$otpData = [
    'phone' => $phoneNumber,
];

$otpUrl = 'http://127.0.0.1:8000/api/v1/send-otp';
$otpResult = makeApiRequest($otpUrl, 'POST', $otpData);

echo "Status Code: " . $otpResult['status'] . "\n";
echo "Response: " . json_encode($otpResult['json'] ?? $otpResult['body'], JSON_PRETTY_PRINT) . "\n\n";

if ($otpResult['status'] !== 200) {
    echo "Failed to send OTP. Cannot proceed with registration.\n";
    exit(1);
}

echo "Using test OTP: $otp\n\n";

// Step 2: Register user with OTP
echo "=== Step 2: Registering User ===\n";
$registerData = [
    'phone' => $phoneNumber,
    'otp' => $otp,
];

$registerUrl = 'http://127.0.0.1:8000/api/v1/register';
$registerResult = makeApiRequest($registerUrl, 'POST', $registerData);

echo "Status Code: " . $registerResult['status'] . "\n";
$response = $registerResult['json'] ?? json_decode($registerResult['body'], true);

echo "Response: " . json_encode($response, JSON_PRETTY_PRINT) . "\n\n";

if ($registerResult['status'] !== 200 && $registerResult['status'] !== 201) {
    echo "Registration failed. Cannot proceed with complete profile test.\n";
    exit(1);
}

// Extract token from response
$token = $response['data']['token']['access_token'] ?? null;
$redirectTo = $response['data']['redirect_to'] ?? null;

if (!$token) {
    echo "No access token received in registration response\n";
    exit(1);
}

echo "Successfully obtained JWT token: " . substr($token, 0, 30) . "...\n";
echo "Redirect to: " . ($redirectTo ?? 'Not specified') . "\n\n";

// Step 3: Test complete profile API
echo "=== Step 3: Testing Complete Profile API ===\n";
$profileData = [
    'name' => 'Test User ' . rand(1000, 9999),
    'email' => 'test' . rand(1000, 9999) . '@example.com',
    // Add other required fields for complete profile
];

$profileUrl = 'http://127.0.0.1:8000/api/v1/profile/complete';
$profileResult = makeApiRequest($profileUrl, 'POST', $profileData, $token);

echo "Status Code: " . $profileResult['status'] . "\n";
echo "Response: " . json_encode($profileResult['json'] ?? $profileResult['body'], JSON_PRETTY_PRINT) . "\n";

echo "\nTest completed.\n";
