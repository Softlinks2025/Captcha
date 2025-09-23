<?php

$url = 'http://localhost:8000/api/v1/register';
$data = [
    'phone' => '1234567890',
    'country_code' => '+91',
    'otp' => '123456'
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    echo 'cURL Error: ' . curl_error($ch);
} else {
    echo "Status Code: $httpCode\n";
    echo "Response: $response\n";
    
    // Output response headers
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $header = substr($response, 0, $header_size);
    $body = substr($response, $header_size);
    
    echo "\nResponse Headers:\n$header\n";
    echo "Response Body:\n$body\n";
}

curl_close($ch);
