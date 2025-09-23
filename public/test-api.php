<?php

// Test API response format
$url = 'http://localhost:8000/api/test-json';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'Content-Type: application/json',
]);

$response = curl_exec($ch);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$headers = substr($response, 0, $headerSize);
$body = substr($response, $headerSize);

curl_close($ch);

// Output results
echo "=== Test API Response ===\n\n";
echo "URL: $url\n";
echo "=== Headers ===\n$headers\n";
echo "=== Body ===\n$body\n";

// Check if response is JSON
$json = json_decode($body);
if (json_last_error() === JSON_ERROR_NONE) {
    echo "\n✅ Response is valid JSON\n";
} else {
    echo "\n❌ Response is NOT valid JSON\n";    
}

// Check content type
if (strpos($headers, 'Content-Type: application/json') !== false) {
    echo "✅ Content-Type is application/json\n";
} else {
    echo "❌ Content-Type is NOT application/json\n";
}
