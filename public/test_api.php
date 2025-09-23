<?php

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Test simple endpoint
testEndpoint('GET', '/api/test-simple');

// Test debug endpoint
testEndpoint('GET', '/api/v1/debug');

// Test registration endpoint with minimal data
testEndpoint('POST', '/api/v1/register', [
    'test' => 'data'
]);

function testEndpoint($method, $uri, $data = []) {
    $request = Illuminate\Http\Request::create(
        $uri, 
        $method, 
        [], // params
        [], // cookies
        [], // files
        ['CONTENT_TYPE' => 'application/json'], // server
        $data ? json_encode($data) : null
    );
    
    $request->headers->set('Accept', 'application/json');
    
    $kernel = app(Illuminate\Contracts\Http\Kernel::class);
    $response = $kernel->handle($request);
    
    echo "\nTesting $method $uri\n";
    echo "Status: " . $response->getStatusCode() . "\n";
    echo "Response: " . $response->getContent() . "\n";
    echo str_repeat("-", 50) . "\n";
}
