<?php
// List all users in the database

// Load Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illware\Contracts\Http\Kernel::class);

// Run the application
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Get all users
$users = DB::table('users')->get();

echo "=== Users in Database ===\n";
foreach ($users as $user) {
    echo "ID: {$user->id}, Phone: {$user->phone}, Name: {$user->name}, Email: {$user->email}\n";
}

echo "\n=== Test Complete ===\n";
