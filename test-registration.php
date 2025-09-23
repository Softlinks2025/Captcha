<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Create a test request
$request = \Illuminate\Http\Request::create(
    '/api/v1/register', 
    'POST',
    [
        'phone' => '9876543210',
        'otp' => '123456'
    ],
    [],
    [],
    ['HTTP_ACCEPT' => 'application/json']
);

// Handle the request
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request);

// Output the response
$content = $response->getContent();
$json = json_decode($content, true);

if (json_last_error() === JSON_ERROR_NONE) {
    echo json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
} else {
    echo $content;
}

// Terminate the request
$kernel->terminate($request, $response);
