<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\WithdrawalRequestController;
use App\Http\Controllers\SubscriptionPlanController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\Admin\ChangePasswordController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Test route to verify basic routing is working
Route::get('/test', function () {
    return 'Laravel is working! Server is running on ' . request()->getHost() . ':' . request()->getPort();
});

// Debug route to check admin user
Route::get('/debug/admin', function () {
    $user = \App\Models\User::where('email', 'admin@example.com')->first();
    
    if (!$user) {
        return 'Admin user not found. Please create an admin user.';
    }
    
    return [
        'user_exists' => true,
        'user_id' => $user->id,
        'user_email' => $user->email,
        'user_name' => $user->name,
        'has_admin_role' => $user->hasRole('admin'),
        'roles' => $user->getRoleNames()->toArray(),
        'is_authenticated' => auth()->check(),
        'current_user' => auth()->user() ? auth()->user()->email : null
    ];
});

// Fallback route for debugging
Route::fallback(function () {
    return response()->json([
        'error' => 'Route not found',
        'requested_url' => request()->url(),
        'method' => request()->method(),
        'available_routes' => [
            'test' => '/test',
            'debug_admin' => '/debug/admin',
            'login' => '/login',
            'api_test' => '/api/test',
            'api_v1_test' => '/api/v1/test'
        ]
    ], 404);
});

// Redirect root to login page - must be before any other routes
Route::redirect('/', '/login');

// Public routes - only accessible to guests
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
});

// Logout route
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Comment out or remove the home route if not needed
// Route::get('/home', function () {
//     return redirect()->route('admin.dashboard');
// })->name('home');

// Temporary debug routes - REMOVE IN PRODUCTION
if (app()->environment('local')) {
    Route::get('/debug/check-admin', function () {
        $user = \App\Models\User::where('email', 'admin@example.com')->first();
        
        if (!$user) {
            return 'Admin user not found';
        }
        
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'password' => $user->password,
            'is_admin' => $user->hasRole('admin'),
            'roles' => $user->getRoleNames(),
            'created_at' => $user->created_at,
        ];
    });

  
    Route::get('/debug/users', function () {
        $users = \App\Models\User::with('roles')->get();
        
        return view('debug.users', [
            'users' => $users->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'password' => $user->password,
                    'roles' => $user->getRoleNames()->toArray(),
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ];
            })
        ]);
    });

    Route::get('/debug/assign-admin/{userId?}', function ($userId = null) {
        $user = $userId 
            ? \App\Models\User::find($userId)
            : \App\Models\User::first();
            
        if (!$user) {
            return 'User not found';
        }
        
        // Create admin role if it doesn't exist
        $adminRole = \Spatie\Permission\Models\Role::firstOrCreate(
            ['name' => 'admin'],
            ['guard_name' => 'web']
        );
        
        // Assign admin role to the user
        $user->assignRole('admin');
        
        return 'Assigned admin role to user: ' . $user->email;
    });
}

// Admin Change Password
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/change-password', [ChangePasswordController::class, 'showChangePasswordForm'])->name('admin.change-password');
    Route::post('/admin/change-password', [ChangePasswordController::class, 'changePassword'])->name('admin.change-password.update');
});