<?php
// Test script for complete flow: OTP request -> OTP verification -> Registration -> Login -> Complete Profile

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
        CURLOPT_VERBOSE => false,
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

// Step 1: Request OTP
echo "=== Step 1: Requesting OTP ===\n";
$otpRequestData = [
    'phone' => $phoneNumber,
];

$otpRequestUrl = 'http://127.0.0.1:8000/api/v1/send-otp';
$otpRequestResult = makeApiRequest($otpRequestUrl, 'POST', $otpRequestData);

echo "Status Code: " . $otpRequestResult['status'] . "\n";
echo "Response: " . json_encode($otpRequestResult['json'] ?? $otpRequestResult['body'], JSON_PRETTY_PRINT) . "\n\n";

if ($otpRequestResult['status'] !== 200) {
    echo "OTP request failed. Cannot proceed with registration.\n";
    exit(1);
}

// For testing purposes, we'll use a hardcoded OTP since we can't receive SMS in this environment
// In a real scenario, you would extract the OTP from the response or database
$otp = '123456'; // Default OTP for testing

echo "Using test OTP: $otp\n\n";

// Step 2: Verify OTP
echo "=== Step 2: Verifying OTP ===\n";
$verifyOtpData = [
    'phone' => $phoneNumber,
    'otp' => $otp,
];

$verifyOtpUrl = 'http://127.0.0.1:8000/api/v1/verify-otp';
$verifyOtpResult = makeApiRequest($verifyOtpUrl, 'POST', $verifyOtpData);

echo "Status Code: " . $verifyOtpResult['status'] . "\n";
echo "Response: " . json_encode($verifyOtpResult['json'] ?? $verifyOtpResult['body'], JSON_PRETTY_PRINT) . "\n\n";

if ($verifyOtpResult['status'] !== 200) {
    echo "OTP verification failed. Cannot proceed with registration.\n";
    exit(1);
}

// Step 3: Register user
echo "=== Step 3: Registering User ===\n";
$registerData = [
    'phone' => $phoneNumber,
    'otp' => $otp,
    'name' => 'Test User ' . rand(1000, 9999),
    'email' => 'test' . rand(1000, 9999) . '@example.com',
    'password' => 'password',
    'password_confirmation' => 'password',
];

$registerUrl = 'http://127.0.0.1:8000/api/v1/register';
$registerResult = makeApiRequest($registerUrl, 'POST', $registerData);

echo "Status Code: " . $registerResult['status'] . "\n";
echo "Response: " . json_encode($registerResult['json'] ?? $registerResult['body'], JSON_PRETTY_PRINT) . "\n\n";

if ($registerResult['status'] !== 200) {
    echo "Registration failed. Cannot proceed with login test.\n";
    exit(1);
}

// Extract JWT token from registration response
$registerResponse = json_decode($registerResult['body'], true);
$token = $registerResponse['access_token'] ?? null;

if (!$token) {
    echo "No access token received in registration response\n";
    echo "Trying to login...\n";
    
    // If no token in registration response, try to login
    echo "=== Trying to Login ===\n";
    $loginData = [
        'phone' => $phoneNumber,
        'otp' => $otp,
    ];
    
    $loginUrl = 'http://127.0.0.1:8000/api/v1/login';
    $loginResult = makeApiRequest($loginUrl, 'POST', $loginData);
    
    echo "Status Code: " . $loginResult['status'] . "\n";
    echo "Response: " . json_encode($loginResult['json'] ?? $loginResult['body'], JSON_PRETTY_PRINT) . "\n\n";
    
    if ($loginResult['status'] !== 200) {
        echo "Login failed. Cannot proceed with complete profile test.\n";
        exit(1);
    }
    
    $loginResponse = json_decode($loginResult['body'], true);
    $token = $loginResponse['access_token'] ?? null;
    
    if (!$token) {
        echo "No access token received in login response\n";
        exit(1);
    }
}

echo "\nSuccessfully obtained JWT token: " . substr($token, 0, 30) . "...\n";

// Step 4: Test complete profile API
echo "\n=== Step 4: Testing Complete Profile API ===\n";
$profileData = [
    'name' => 'Test User ' . rand(1000, 9999),
    'email' => 'test' . rand(1000, 9999) . '@example.com',
    // Add other required fields for complete profile
];

$profileUrl = 'http://127.0.0.1:8000/api/v1/profile/complete';
$profileResult = makeApiRequest($profileUrl, 'POST', $profileData, $token);

echo "Status Code: " . $profileResult['status'] . "\n";
echo "Response: " . json_encode($profileResult['json'] ?? $profileResult['body'], JSON_PRETTY_PRINT) . "\n";

// Display response headers for debugging
echo "\n=== Response Headers ===\n";
$headerLines = explode("\r\n", $profileResult['headers']);
foreach ($headerLines as $header) {
    if (!empty(trim($header))) {
        echo $header . "\n";
    }
}

echo "\nTest completed.\n";
