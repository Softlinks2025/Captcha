<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Test API Routes
|--------------------------------------------------------------------------
| These routes are for testing the API response format
*/

Route::get('/test-json', function () {
    return [
        'message' => 'This is a test JSON response',
        'timestamp' => now()->toDateTimeString(),
        'success' => true
    ];
});

Route::get('/test-html', function () {
    return "<h1>This is an HTML response</h1>";
});

Route::get('/test-error', function () {
    return response()->json([
        'error' => 'Test error message',
        'code' => 400
    ], 400);
});

Route::get('/test-user', function (Request $request) {
    return [
        'user' => [
            'id' => 1,
            'name' => 'Test User',
            'email' => 'test@example.com'
        ],
        'auth' => [
            'authenticated' => $request->user() ? true : false,
            'token' => $request->bearerToken() ? '***' . substr($request->bearerToken(), -8) : null
        ]
    ];
});
