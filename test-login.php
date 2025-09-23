<?php
// Test script for login API

// Display all errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to make API request
function makeApiRequest($url, $method = 'GET', $data = []) {
    $ch = curl_init();
    
    $options = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'Content-Type: application/json',
        ],
        CURLOPT_VERBOSE => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 0,
    ];
    
    if ($method === 'POST') {
        $options[CURLOPT_POST] = true;
        $options[CURLOPT_POSTFIELDS] = json_encode($data);
        $options[CURLOPT_HTTPHEADER][] = 'Content-Length: ' . strlen(json_encode($data));
    }
    
    curl_setopt_array($ch, $options);
    
    $verbose = fopen('php://temp', 'w+');
    curl_setopt($ch, CURLOPT_STDERR, $verbose);
    
    $response = curl_exec($ch);
    $error = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    
    rewind($verbose);
    $verboseLog = stream_get_contents($verbose);
    
    $headers = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
    
    curl_close($ch);
    
    return [
        'status' => $httpCode,
        'headers' => $headers,
        'body' => $body,
        'json' => json_decode($body, true),
        'error' => $error,
        'verbose' => $verboseLog,
    ];
}

// Test login API
echo "=== Testing Login API ===\n";

// Replace with your test credentials
$testData = [
    'phone' => '1234567890', // Replace with registered phone number
    'otp' => '123456',      // Replace with valid OTP
];

$apiUrl = 'http://127.0.0.1:8000/api/login';

// Make the API request
echo "Sending request to: $apiUrl\n";
echo "With data: " . json_encode($testData, JSON_PRETTY_PRINT) . "\n\n";

$result = makeApiRequest($apiUrl, 'POST', $testData);

// Display results
echo "=== API Response ===\n";
echo "Status Code: " . $result['status'] . "\n";
echo "Response Body: " . json_encode($result['json'] ?? $result['body'], JSON_PRETTY_PRINT) . "\n";

if (!empty($result['error'])) {
    echo "\n=== cURL Error ===\n";
    echo $result['error'] . "\n";
}

// Display Laravel logs
echo "\n=== Laravel Logs (last 20 lines) ===\n";
$laravelLog = __DIR__ . '/storage/logs/laravel.log';
if (file_exists($laravelLog)) {
    $logContent = `tail -n 20 "$laravelLog"`;
    echo $logContent ?: 'No recent logs found';
} else {
    echo "Laravel log file not found at: $laravelLog\n";
}

echo "\nTest completed.\n";
