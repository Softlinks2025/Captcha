<?php

// Suppress warnings for cleaner output
error_reporting(E_ERROR | E_PARSE);

// Simple function to make HTTP requests
function makeRequest($url, $method = 'GET', $data = null) {
    $options = [
        'http' => [
            'method'  => $method,
            'header'  => "Content-type: application/json\r\nAccept: application/json",
            'ignore_errors' => true
        ]
    ];
    
    if ($data) {
        $options['http']['content'] = json_encode($data);
    }
    
    $context = stream_context_create($options);
    $result = @file_get_contents($url, false, $context);
    
    return [
        'status' => $http_response_header[0] ?? null,
        'body' => $result ? json_decode($result, true) : null
    ];
}

// Test endpoints
$endpoints = [
    'test-simple' => 'http://localhost:8000/api/test-simple',
    'register' => 'http://localhost:8000/api/v1/register'
];

foreach ($endpoints as $name => $url) {
    echo "\nTesting $name ($url)...\n";
    $response = makeRequest($url);
    echo "Status: " . ($response['status'] ?? 'No response') . "\n";
    echo "Response: " . json_encode($response['body'], JSON_PRETTY_PRINT) . "\n";
    echo str_repeat("-", 50) . "\n";
}

// Test POST to register
$testData = [
    'name' => 'Test User',
    'email' => 'test@example.com',
    'phone' => '1234567890',
    'country_code' => '+91',
    'password' => 'Password123!',
    'password_confirmation' => 'Password123!',
    'terms_accepted' => true,
    'device_name' => 'Test Device'
];

echo "\nTesting POST to register...\n";
$response = makeRequest($endpoints['register'], 'POST', $testData);
echo "Status: " . ($response['status'] ?? 'No response') . "\n";
echo "Response: " . json_encode($response['body'], JSON_PRETTY_PRINT) . "\n";
