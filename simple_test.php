<?php

function testEndpoint($url) {
    $ch = curl_init($url);
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Content-Type: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    curl_close($ch);
    
    return [
        'status' => $httpCode,
        'response' => json_decode($response, true) ?: $response
    ];
}

// Test simple endpoint
echo "Testing /api/test-simple...\n";
$result = testEndpoint('http://localhost:8000/api/test-simple');
echo "Status: " . $result['status'] . "\n";
echo "Response: " . print_r($result['response'], true) . "\n\n";

// Test debug endpoint
echo "Testing /api/v1/debug...\n";
$result = testEndpoint('http://localhost:8000/api/v1/debug');
echo "Status: " . $result['status'] . "\n";
echo "Response: " . print_r($result['response'], true) . "\n\n";
