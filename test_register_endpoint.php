<?php

// Test registration endpoint
$url = 'http://localhost:8000/api/v1/register';
$data = [
    'phone' => '1234567890',
    'country_code' => '+91',
    'otp' => '1234',  // 4-digit OTP as per validation
    'name' => 'Test User',
    'email' => 'test' . time() . '@example.com',  // Unique email
    'password' => 'Password123!',  // Must meet complexity requirements
    'password_confirmation' => 'Password123!',
    'terms_accepted' => true,
    'device_name' => 'Test Device'
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
    echo 'cURL Error: ' . curl_error($ch) . "\n";
} else {
    echo "Status Code: $httpCode\n";
    echo "Response: " . print_r(json_decode($response, true), true) . "\n";
}

curl_close($ch);
