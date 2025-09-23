<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Test JSON API Routes
|--------------------------------------------------------------------------
*/

Route::get('/test-json', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'This is a test JSON response',
        'timestamp' => now()->toDateTimeString(),
        'data' => [
            'openssl_loaded' => extension_loaded('openssl'),
            'php_version' => phpversion(),
            'environment' => app()->environment(),
        ]
    ]);
});

// Add the test-json route to the web routes
Route::prefix('api')->group(function () {
    Route::get('/test-json', function () {
        return response()->json([
            'status' => 'success',
            'message' => 'This is a test JSON response from API',
            'timestamp' => now()->toDateTimeString(),
            'data' => [
                'openssl_loaded' => extension_loaded('openssl'),
                'php_version' => phpversion(),
                'environment' => app()->environment(),
                'middleware' => \Route::current()->gatherMiddleware()
            ]
        ]);
    });
});

// Fallback route to test if the file is being loaded
Route::get('/test-json-file-loaded', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'Test JSON file is loaded',
        'file' => __FILE__
    ]);
});
