<?php

// Test CORS and JSON response
$url = 'http://localhost:8000/api/test-clean';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'Content-Type: application/json',
]);

// For testing CORS
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Origin: http://example.com',
    'Access-Control-Request-Method: GET',
    'Access-Control-Request-Headers: content-type',
]);

$response = curl_exec($ch);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$headers = substr($response, 0, $headerSize);
$body = substr($response, $headerSize);

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$contentType = '';
$corsHeaders = [];

// Parse headers
$headerLines = explode("\r\n", $headers);
foreach ($headerLines as $line) {
    if (stripos($line, 'Content-Type:') === 0) {
        $contentType = trim(substr($line, 13));
    }
    if (stripos($line, 'Access-Control-') === 0) {
        $corsHeaders[] = trim($line);
    }
}

curl_close($ch);

// Output results
echo "=== CORS and JSON Test ===\n\n";
echo "URL: $url\n";
echo "HTTP Status: $httpCode\n";
echo "Content-Type: $contentType\n";

echo "\n=== CORS Headers ===\n";
if (empty($corsHeaders)) {
    echo "No CORS headers found\n";
} else {
    foreach ($corsHeaders as $header) {
        echo "- $header\n";
    }
}

echo "\n=== Response Body ===\n";
echo $body . "\n";

// Check if response is JSON
$json = json_decode($body);
if (json_last_error() === JSON_ERROR_NONE) {
    echo "\n" . '✅ Response is valid JSON' . "\n";
} else {
    echo "\n" . '❌ Response is NOT valid JSON' . "\n";
}

// Check content type
if (strpos($contentType, 'application/json') !== false) {
    echo '✅ Content-Type is application/json' . "\n";
} else {
    echo '❌ Content-Type is NOT application/json' . "\n";
}

// Check for CORS headers
$requiredCorsHeaders = [
    'Access-Control-Allow-Origin',
    'Access-Control-Allow-Methods',
    'Access-Control-Allow-Headers',
];

$missingHeaders = [];
foreach ($requiredCorsHeaders as $header) {
    $found = false;
    foreach ($corsHeaders as $h) {
        if (stripos($h, $header) === 0) {
            $found = true;
            break;
        }
    }
    if (!$found) {
        $missingHeaders[] = $header;
    }
}

if (empty($missingHeaders)) {
    echo '✅ All required CORS headers are present' . "\n";
} else {
    echo '❌ Missing CORS headers: ' . implode(', ', $missingHeaders) . "\n";
}
