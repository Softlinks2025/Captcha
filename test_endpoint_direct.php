<?php

// Test the test-register endpoint directly
$url = 'http://localhost:8000/api/test-register';
$data = [
    'test' => 'data',
    'timestamp' => time()
];

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/json\r\n" .
                    "Accept: application/json",
        'content' => json_encode($data),
        'ignore_errors' => true
    ]
]);

$response = file_get_contents($url, false, $context);
$status_line = $http_response_header[0];
preg_match('{HTTP\/\S*\s(\d{3})}', $status_line, $match);
$status = $match[1];

echo "Status: $status\n";
echo "Response: " . print_r(json_decode($response, true), true) . "\n";
