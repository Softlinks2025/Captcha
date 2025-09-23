<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\OtpController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AgentReferralController;
use App\Http\Controllers\Api\Agent\WalletController as AgentWalletController;
use App\Http\Controllers\Api\Agent\WithdrawalController as AgentWithdrawalController;
use App\Http\Controllers\Admin\AgentWithdrawalRequestController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\MilestoneController;

/*
|--------------------------------------------------------------------------
| Test Routes - For API response testing
|--------------------------------------------------------------------------
*/

// Test JSON response
Route::get('/test-json', function () {
    return [
        'message' => 'This is a test JSON response',
        'timestamp' => now()->toDateTimeString(),
        'success' => true
    ];
});

// Test HTML response (should be converted to JSON)
Route::get('/test-html', function () {
    return "<h1>This is an HTML response</h1>";
});

// Test error response
Route::get('/test-error', function () {
    return response()->json([
        'error' => 'Test error message',
        'code' => 400
    ], 400);
});

// Test user data response
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

/*
|--------------------------------------------------------------------------
| Test Login Route - For debugging only
|--------------------------------------------------------------------------
*/
Route::get('/test-login', function () {
    $phone = '9457508075'; // Test with the user's phone number
    $otp = '123456'; // Test OTP - should match what's in the database
    
    try {
        Log::info('Test login endpoint called', ['phone' => $phone]);
        
        // 1. Check if user exists
        $user = User::where('phone', $phone)->first();
        
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found',
                'user_exists' => false
            ]);
        }
        
        // 2. Check if OTP is set
        if (empty($user->otp)) {
            return response()->json([
                'status' => 'error',
                'message' => 'No OTP set for user',
                'user_exists' => true,
                'has_otp' => false,
                'user' => [
                    'id' => $user->id,
                    'phone' => $user->phone,
                    'otp' => $user->otp,
                    'otp_expires_at' => $user->otp_expires_at,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at
                ]
            ]);
        }
        
        // 3. Verify OTP hash
        $otpMatches = Hash::check($otp, $user->otp);
        
        // 4. Check OTP expiration
        $isExpired = $user->otp_expires_at && now()->gt($user->otp_expires_at);
        
        return response()->json([
            'status' => 'success',
            'user' => [
                'id' => $user->id,
                'phone' => $user->phone,
                'has_otp' => !empty($user->otp),
                'otp_expires_at' => $user->otp_expires_at,
                'is_expired' => $isExpired,
                'otp_matches' => $otpMatches
            ],
            'debug' => [
                'otp_stored' => $user->otp,
                'otp_provided' => $otp,
                'otp_matches' => $otpMatches,
                'current_time' => now(),
                'is_expired' => $isExpired
            ]
        ]);
        
    } catch (\Exception $e) {
        Log::error('Test login error', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'status' => 'error',
            'message' => 'Test failed',
            'error' => [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'code' => $e->getCode()
            ]
        ], 500);
    }
});

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Clean test endpoint for CORS and JSON response
Route::get('/test-clean', function (Request $request): JsonResponse {
    return new JsonResponse([
        'status' => 'success',
        'message' => 'Clean test endpoint',
        'timestamp' => now()->toDateTimeString(),
        'headers' => [
            'accept' => $request->header('accept'),
            'content_type' => $request->header('content-type'),
        ]
    ]);
});

// Test endpoint to verify API is working with JSON response
Route::get('/test-json', function (Request $request) {
    return response()->json([
        'status' => 'success',
        'message' => 'JSON test endpoint',
        'timestamp' => now()->toDateTimeString(),
        'is_api' => $request->is('api/*'),
        'middleware' => $request->route() ? $request->route()->gatherMiddleware() : [],
        'accept_header' => $request->header('Accept'),
        'content_type' => $request->header('Content-Type'),
    ]);
});

// Simple test endpoint
Route::get('/test-simple', function () {
    return [
        'status' => 'success',
        'message' => 'Simple test endpoint',
        'timestamp' => now()->toDateTimeString(),
    ];
});

// Test database connection
Route::get('/test-db', function() {
    try {
        // Test database connection
        DB::connection()->getPdo();
        
        // Test users table
        $user = \App\Models\User::first();
        
        return response()->json([
            'status' => 'success',
            'database' => 'Connected successfully',
            'users_table' => $user ? 'Has users' : 'No users found',
            'time' => now()->toDateTimeString()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ], 500);
    }
});

// Test OTP sending
Route::post('/test-send-otp', function(\Illuminate\Http\Request $request) {
    try {
        $request->validate([
            'phone' => 'required|string|regex:/^[0-9]{10}$/'
        ]);
        
        $phone = $request->phone;
        $user = \App\Models\User::where('phone', $phone)->first();
        
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found with this phone number'
            ], 404);
        }
        
        // Generate OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $user->otp = \Illuminate\Support\Facades\Hash::make($otp);
        $user->otp_expires_at = now()->addMinutes(10);
        $user->save();
        
        return response()->json([
            'status' => 'success',
            'message' => 'OTP sent successfully',
            'data' => [
                'phone' => $user->phone,
                'otp' => $otp, // Only for testing
                'otp_expires_at' => $user->otp_expires_at
            ]
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => config('app.debug') ? $e->getTraceAsString() : null
        ], 500);
    }
});

// API v1 Routes
Route::prefix('v1')->group(function () {
    // Public routes - Authentication
    Route::post('login', [\App\Http\Controllers\Api\AuthController::class, 'login']);
    Route::post('send-otp', [\App\Http\Controllers\Api\Auth\OtpController::class, 'sendOtp']);
    Route::post('verify-otp', [\App\Http\Controllers\Api\Auth\OtpController::class, 'verifyOtp']);
    Route::post('register', [RegisterController::class, 'register']);
    
    // Agent routes - Public
    Route::prefix('agent')->group(function () {
        Route::post('send-otp', [\App\Http\Controllers\Api\Agent\OtpController::class, 'sendOtp']);
        Route::post('verify-otp', [\App\Http\Controllers\Api\Agent\OtpController::class, 'verifyOtp']);
        Route::post('register', [\App\Http\Controllers\Api\Agent\RegisterController::class, 'register']);
        Route::post('login', [\App\Http\Controllers\Api\Agent\AuthController::class, 'login']);
    });
    
    // Debug route
    Route::get('debug', function () {
        return response()->json([
            'status' => 'success',
            'message' => 'Debug route is working',
            'data' => [
                'request' => [
                    'headers' => request()->headers->all(),
                    'method' => request()->method(),
                    'path' => request()->path(),
                    'url' => request()->url(),
                    'full_url' => request()->fullUrl(),
                    'ip' => request()->ip(),
                    'secure' => request()->secure(),
                    'ajax' => request()->ajax(),
                    'wants_json' => request()->wantsJson(),
                    'accepts_json' => request()->wantsJson(),
                    'accepts_html' => request()->acceptsHtml(),
                    'content_type' => request()->header('content-type'),
                    'accept' => request()->header('accept'),
                ],
                'app' => [
                    'env' => app()->environment(),
                    'debug' => config('app.debug'),
                ]
            ]
        ]);
    });
    
    // Protected routes (require JWT authentication)
    Route::middleware('auth:api')->group(function () {
        // Profile routes
        Route::prefix('profile')->group(function () {
            Route::get('/', [ProfileController::class, 'getProfile']);
            Route::post('/complete', [ProfileController::class, 'completeProfile']);
            Route::post('/', [ProfileController::class, 'updateProfile']);
            Route::post('/user/fcm-token', [\App\Http\Controllers\Api\ProfileController::class, 'updateFcmToken']);
            Route::post('/user/send-test-notification', [\App\Http\Controllers\Api\ProfileController::class, 'sendTestNotification']);
        });
        
        // Logout
        Route::post('logout', [AuthController::class, 'logout']);

        // New route for purchasing a plan (must be before admin group)
        Route::post('plans/purchase', [\App\Http\Controllers\Api\SubscriptionPlanController::class, 'purchase']);
        
        // Withdrawal requests (user create, admin list/approve/decline)
        Route::post('withdrawal-requests', [\App\Http\Controllers\Api\WithdrawalRequestController::class, 'store']); // user create
        Route::get('withdrawal-requests', [\App\Http\Controllers\Api\WithdrawalRequestController::class, 'index']); // admin list
        Route::get('withdrawal-requests/history', [\App\Http\Controllers\Api\WithdrawalRequestController::class, 'history']); // user history
        Route::middleware(['auth:api'])->post('/admin/agent-withdrawal-requests/{id}/approve', [AgentWithdrawalRequestController::class, 'approve']);
        Route::middleware(['auth:api'])->post('/admin/agent-withdrawal-requests/{id}/decline', [AgentWithdrawalRequestController::class, 'decline']);

        // Captcha solve routes
        Route::post('captcha/solve', [\App\Http\Controllers\Api\CaptchaSolveController::class, 'solveCaptcha']);
        Route::get('captcha/level', [\App\Http\Controllers\Api\CaptchaSolveController::class, 'getLevel']);
        Route::get('captcha/level/{user_id}', [\App\Http\Controllers\Api\CaptchaSolveController::class, 'getLevelByUserId']);
        Route::post('captcha/level-by-user', [\App\Http\Controllers\Api\CaptchaSolveController::class, 'getLevelByUserIdFromBody']);
        Route::get('captcha/todays-earning', [\App\Http\Controllers\Api\CaptchaSolveController::class, 'getTodaysEarning']);

        // New route for getting wallet
        Route::get('wallet', [\App\Http\Controllers\Api\WalletController::class, 'show']);
        Route::post('wallet/by-user', [\App\Http\Controllers\Api\WalletController::class, 'showByUserId']);

        // User wallet add balance (testing only)
        Route::post('wallet/add-balance', [\App\Http\Controllers\Api\WalletController::class, 'addBalance']);

        // New route for getting users list
        Route::get('users/list', [\App\Http\Controllers\Api\UserController::class, 'list']);
        Route::get('agents/list', [\App\Http\Controllers\AgentController::class, 'list']);
        
        // User referral bonuses route
        Route::get('user/referral-bonuses', [\App\Http\Controllers\Api\UserController::class, 'getReferralBonuses']);
    });
    
    // Milestone Routes with JWT auth
    Route::middleware('jwt.auth')->group(function () {
        // Get milestone status
        Route::get('/milestones/status', [\App\Http\Controllers\Api\MilestoneController::class, 'getMilestoneStatus']);
        
        // Claim milestone bonus
        Route::post('/milestones/claim/{milestone}', [\App\Http\Controllers\Api\MilestoneController::class, 'claimMilestoneBonus'])
            ->where('milestone', '10|50|100');
    });
    
    // Agent protected routes (require agent JWT authentication)
    Route::middleware(\App\Http\Middleware\AuthenticateAgent::class)->prefix('agent')->group(function () {
        // Test route to debug authentication
        Route::get('/test-auth', function() {
            return response()->json([
                'status' => 'success',
                'message' => 'Agent authentication working',
                'agent' => auth('agent')->user()
            ]);
        });
        
        // Agent profile routes
        Route::prefix('profile')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\Agent\ProfileController::class, 'getProfile']);
            Route::post('/complete', [\App\Http\Controllers\Api\Agent\ProfileController::class, 'completeProfile']);
            Route::post('/', [\App\Http\Controllers\Api\Agent\ProfileController::class, 'updateProfile']);
            Route::post('/upload-image', [\App\Http\Controllers\Api\Agent\ProfileController::class, 'uploadProfileImage']);
            Route::post('/agent/fcm-token', [\App\Http\Controllers\Api\Agent\ProfileController::class, 'updateFcmToken']);
        });
        
        // Agent authentication routes
        Route::post('logout', [\App\Http\Controllers\Api\Agent\AuthController::class, 'logout']);
        Route::post('refresh', [\App\Http\Controllers\Api\Agent\AuthController::class, 'refresh']);
        Route::get('me', [\App\Http\Controllers\Api\Agent\AuthController::class, 'me']);
        
        // Agent plan routes
        Route::prefix('plans')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\Agent\PlanController::class, 'index']);
            Route::get('/{id}', [\App\Http\Controllers\Api\Agent\PlanController::class, 'show']);
            Route::post('/purchase', [\App\Http\Controllers\Api\Agent\PlanController::class, 'purchase']);
            Route::get('/my-plan', [\App\Http\Controllers\Api\Agent\PlanController::class, 'myPlan']);
            Route::get('/agent/details', [\App\Http\Controllers\Api\Agent\PlanController::class, 'agentDetails']);
            Route::get('/history', [\App\Http\Controllers\Api\Agent\PlanController::class, 'planHistory']);
            Route::get('/debug-all-plans', [\App\Http\Controllers\Api\Agent\PlanController::class, 'debugAllPlans']);
            Route::post('/by-agent-id', [\App\Http\Controllers\Api\Agent\PlanController::class, 'getPlanByAgentId']);
        });

        // Agent withdrawal request routes
        Route::middleware('auth:agent')->post('/agent/withdrawal-requests', [AgentWithdrawalController::class, 'store']);
        Route::middleware('auth:agent')->get('/agent/withdrawal-requests', [AgentWithdrawalController::class, 'index']);

        // Agent wallet add balance (testing only)
        Route::post('wallet/add-balance', [\App\Http\Controllers\Api\Agent\WalletController::class, 'addBalance']);

        // New route for agent referral earnings
        Route::post('/earnings/referral', [\App\Http\Controllers\Api\AgentReferralController::class, 'referralEarnings']);

        // Agent milestone routes
        Route::prefix('milestones')->group(function () {
            Route::get('/status', [\App\Http\Controllers\Api\Agent\MilestoneController::class, 'getMilestoneStatus']);
            Route::get('/stats', [\App\Http\Controllers\Api\Agent\MilestoneController::class, 'getReferralStats']);
            Route::post('/claim-tshirt', [\App\Http\Controllers\Api\Agent\MilestoneController::class, 'claimTshirtBonus']);
            Route::post('/claim-bag', [\App\Http\Controllers\Api\Agent\MilestoneController::class, 'claimBagBonus']);
        });
        // Agent joining fee status
        Route::get('/joining-fee/status', [\App\Http\Controllers\Api\Agent\ProfileController::class, 'joiningFeeStatus']);
    });
    
    // New route for getting plans
    Route::get('plans', [\App\Http\Controllers\Api\SubscriptionPlanController::class, 'index']);
    Route::post('plans/by-user-id', [\App\Http\Controllers\Api\SubscriptionPlanController::class, 'getPlanByUserId'])->middleware('jwt.admin');
    Route::post('plans/by-user-id-user', [\App\Http\Controllers\Api\SubscriptionPlanController::class, 'getPlanByUserId'])->middleware('auth:api');
});
Route::post('/test-register', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'Test register endpoint is working',
        'data' => [
            'request_data' => request()->all(),
            'headers' => request()->headers->all(),
            'timestamp' => now()->toDateTimeString()
        ]
    ]);
});
// Agent Joining Fee Routes
Route::prefix('v1/agent/joining-fees')->group(function () {
    Route::get('/', [\App\Http\Controllers\Api\Agent\JoiningFeeController::class, 'index']);
    Route::post('/', [\App\Http\Controllers\Api\Agent\JoiningFeeController::class, 'store']);
    Route::get('/active', [\App\Http\Controllers\Api\Agent\JoiningFeeController::class, 'activeFees']);
    Route::get('/show', [\App\Http\Controllers\Api\Agent\JoiningFeeController::class, 'show']);
    Route::put('/', [\App\Http\Controllers\Api\Agent\JoiningFeeController::class, 'update']);
    Route::delete('/', [\App\Http\Controllers\Api\Agent\JoiningFeeController::class, 'destroy']);
    
    // New route for updating joining fee status after payment
    Route::post('/update-status', [\App\Http\Controllers\Api\Agent\JoiningFeeController::class, 'updateStatus']);
})->middleware('auth:api');

// User Milestone Routes
Route::prefix('v1/user/milestones')->group(function () {
    Route::get('/status', [\App\Http\Controllers\Api\MilestoneController::class, 'getMilestoneStatus']);
    Route::get('/stats', [\App\Http\Controllers\Api\MilestoneController::class, 'getReferralStats']);
    Route::post('/claim-tshirt', [\App\Http\Controllers\Api\MilestoneController::class, 'claimTshirtBonus']);
    Route::post('/claim-bag', [\App\Http\Controllers\Api\MilestoneController::class, 'claimBagBonus']);
});

// Test route to verify API is working
Route::get('/test', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'API is working!',
        'debug' => [
            'route' => request()->path(),
            'method' => request()->method(),
            'headers' => request()->headers->all(),
            'accept' => request()->header('accept'),
            'content_type' => request()->header('content-type')
        ]
    ]);
});

// Test POST route
Route::post('/test-post', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'POST request received',
        'data' => request()->all(),
        'debug' => [
            'route' => request()->path(),
            'method' => request()->method(),
            'headers' => request()->headers->all()
        ]
    ]);
});

// Debug route to list all registered routes
Route::get('/debug-routes', function() {
    return response()->json([
        'routes' => array_map(function($route) {
            return [
                'uri' => $route->uri(),
                'methods' => $route->methods(),
                'action' => $route->getActionName(),
            ];
        }, \Route::getRoutes()->getRoutes())
    ]);
});

// Agent referral tracking API
Route::middleware('auth:agent')->get('/agent/referrals', [AgentReferralController::class, 'referrals']);

// New route for agent wallet transaction history
Route::middleware('auth:agent')->get('/agent/wallet/transactions', [AgentWalletController::class, 'transactions']);

// Allow both users and agents to access the list endpoints
Route::middleware(['auth:api,auth:agent'])->group(function () {
    Route::get('users/list', [\App\Http\Controllers\Api\UserController::class, 'list']);
    Route::get('agents/list', [\App\Http\Controllers\AgentController::class, 'list']);
});

// User tickets API (user JWT only)
Route::middleware('auth:api')->group(function () {
    Route::get('/tickets', [TicketController::class, 'index']);
    Route::post('/tickets', [TicketController::class, 'store']);
});

// Agent v1 ticket routes
Route::middleware(\App\Http\Middleware\AuthenticateAgent::class)
    ->prefix('v1/agent')
    ->group(function () {
        Route::post('/tickets', [\App\Http\Controllers\Api\Agent\TicketController::class, 'store']);
        Route::get('/tickets', [\App\Http\Controllers\Api\Agent\TicketController::class, 'index']);
    });

// Payment APIs (for both agents and users) - use built-in auth middleware for multiple guards
Route::prefix('v1')->group(function () {
    Route::middleware(['auth:agent,api'])->post('/payment/create-order', [\App\Http\Controllers\Api\PaymentController::class, 'createOrder']);
    Route::middleware(['auth:agent,api'])->post('/payment/verify', [\App\Http\Controllers\Api\PaymentController::class, 'verifyPayment']);
});