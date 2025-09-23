<?php

// Test simple endpoint
function testEndpoint($url, $method = 'GET', $data = null) {
    $ch = curl_init();
    
    $options = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'Content-Type: application/json',
        ],
        CURLOPT_CUSTOMREQUEST => $method,
    ];
    
    if ($method === 'POST' && $data) {
        $options[CURLOPT_POSTFIELDS] = json_encode($data);
    }
    
    curl_setopt_array($ch, $options);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headers = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
    
    curl_close($ch);
    
    return [
        'status' => $httpCode,
        'headers' => $headers,
        'body' => json_decode($body, true) ?? $body
    ];
}

// Test simple endpoint
echo "Testing /api/test-simple...\n";
$result = testEndpoint('http://localhost:8000/api/test-simple');
echo "Status: " . $result['status'] . "\n";
echo "Response: " . json_encode($result['body'], JSON_PRETTY_PRINT) . "\n\n";

// Test registration with minimal data
echo "Testing /api/v1/register with minimal data...\n";
$result = testEndpoint('http://localhost:8000/api/v1/register', 'POST', [
    'test' => 'data'
]);
echo "Status: " . $result['status'] . "\n";
echo "Response: " . json_encode($result['body'], JSON_PRETTY_PRINT) . "\n\n";

// Test registration with full data
echo "Testing /api/v1/register with full data...\n";
$result = testEndpoint('http://localhost:8000/api/v1/register', 'POST', [
    'name' => 'Test User',
    'email' => 'test@example.com',
    'phone' => '1234567890',
    'country_code' => '+91',
    'password' => 'Password123!',
    'password_confirmation' => 'Password123!',
    'terms_accepted' => true,
    'device_name' => 'Test Device'
]);
echo "Status: " . $result['status'] . "\n";
echo "Response: " . json_encode($result['body'], JSON_PRETTY_PRINT) . "\n\n";
