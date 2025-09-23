<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CaptchaSolve;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class CaptchaSolveController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    // POST /api/v1/captcha/solve
    public function solveCaptcha(Request $request)
    {
        $user = Auth::user();
        
        // Check if user has an active subscription
        if (!$user->subscription_name) {
            return response()->json(['status' => 'error', 'message' => 'No active subscription found.'], 400);
        }

        // Get user's subscription plan with fresh data
        $plan = SubscriptionPlan::where('name', $user->subscription_name)->first();
        if (!$plan) {
            return response()->json(['status' => 'error', 'message' => 'Subscription plan not found.'], 400);
        }

        // Check daily limit
        $limitRaw = $plan->captcha_per_day ?? $plan->caption_limit ?? 0;
        $todayCount = CaptchaSolve::where('user_id', $user->id)
            ->whereDate('created_at', now()->toDateString())
            ->count();

        if (strtolower($limitRaw) !== 'unlimited' && $todayCount >= (int)$limitRaw) {
            return response()->json([
                'status' => 'error',
                'message' => 'Daily limit reached.',
                'remaining' => 0
            ], 400);
        }

        // Record solve
        $captchaSolve = CaptchaSolve::create(['user_id' => $user->id]);
        
        // Get total captchas solved by the user (including this one)
        $totalCaptchas = CaptchaSolve::where('user_id', $user->id)->count();
        
        // Get captchas required per level from the plan (default to 1 if not set)
        $captchasPerLevel = (int)($plan->captchas_per_level ?? 1);
        
        // Calculate current level (1-based)
        $currentLevel = floor(($totalCaptchas - 1) / $captchasPerLevel) + 1;
        
        // Calculate how many captchas have been solved in the current level
        $captchasInCurrentLevel = ($totalCaptchas % $captchasPerLevel) ?: $captchasPerLevel;
        
        // Only award earnings when a full level is completed
        $earning = 0;
        $levelCompleted = false;
        
        if ($captchasInCurrentLevel === $captchasPerLevel) {
            // --- BEGIN UPDATED REWARD LOGIC ---
            $earning = 0;
            $earnings = $plan->earnings;
            if (is_string($earnings)) {
                $earnings = json_decode($earnings, true);
            }
            if (is_array($earnings)) {
                // Check for exact level match
                $levelKey = (string)$currentLevel;
                if (array_key_exists($levelKey, $earnings)) {
                    $earning = (float)$earnings[$levelKey];
                    \Log::info('CaptchaSolve Reward', ['level' => $currentLevel, 'reward' => $earning, 'source' => 'exact'] );
                } else {
                    // Check for after_X fallback
                    $fallbackReward = 0;
                    $fallbackFound = false;
                    foreach ($earnings as $key => $value) {
                        if (strpos($key, 'after_') === 0) {
                            $afterLevel = (int)substr($key, 6);
                            if ($currentLevel > $afterLevel) {
                                $fallbackReward = (float)$value;
                                $fallbackFound = true;
                            }
                        }
                    }
                    if ($fallbackFound) {
                        $earning = $fallbackReward;
                        \Log::info('CaptchaSolve Reward', ['level' => $currentLevel, 'reward' => $earning, 'source' => 'after_X'] );
                    } else {
                        \Log::info('CaptchaSolve Reward', ['level' => $currentLevel, 'reward' => 0, 'source' => 'none'] );
                    }
                }
            } else {
                \Log::info('CaptchaSolve Reward', ['level' => $currentLevel, 'reward' => 0, 'source' => 'invalid_json']);
            }
            // --- END UPDATED REWARD LOGIC ---
            // Calculate earning based on current level and plan's earnings
            $earnings = is_string($plan->earnings) ? json_decode($plan->earnings, true) : $plan->earnings;
            
            if (is_array($earnings)) {
                foreach ($earnings as $range => $amount) {
                    if (strpos($range, '-') !== false) {
                        // Handle range format: "1-50"
                        [$start, $end] = array_map('intval', explode('-', $range));
                        if ($currentLevel >= $start && $currentLevel <= $end) {
                            $earning = (float)$amount;
                            break;
                        }
                    } elseif (strpos($range, 'after_') === 0) {
                        // Handle "after_X" format
                        $after = (int)str_replace('after_', '', $range);
                        if ($currentLevel > $after) {
                            $earning = (float)$amount;
                            break;
                        }
                    }
                }
            }
            
            $levelCompleted = true;
        }
        
        // Log earning calculation for debugging
        \Log::info('Captcha Solve - Earning Calculation', [
            'user_id' => $user->id,
            'total_captchas' => $totalCaptchas,
            'captchas_per_level' => $captchasPerLevel,
            'current_level' => $currentLevel,
            'captchas_in_current_level' => $captchasInCurrentLevel,
            'level_completed' => $levelCompleted,
            'plan_earnings' => $earnings ?? [],
            'calculated_earning' => $earning,
            'current_wallet_balance' => $user->wallet_balance
        ]);

        // Update user's wallet and level if level was completed
        if ($levelCompleted && $earning > 0) {
            try {
                // Use database transaction to ensure data consistency
                \DB::beginTransaction();

                // Refresh user data to prevent race conditions
                $user->refresh();
                
                // Store the old balance for logging
                $oldBalance = $user->wallet_balance;
                
                // Update wallet balance
                $user->wallet_balance += $earning;
                $user->level = $currentLevel;
                $user->save();

                // Create wallet transaction
                $transaction = \App\Models\WalletTransaction::create([
                    'user_id' => $user->id,
                    'amount' => $earning,
                    'type' => 'earning',
                    'description' => 'Earning for completing level ' . $currentLevel,
                    'status' => 'completed',
                    'reference_id' => 'LVL-' . $currentLevel . '-' . now()->format('YmdHis') . '-' . $user->id,
                    'balance_before' => $oldBalance,
                    'balance_after' => $user->wallet_balance
                ]);

                // Commit the transaction
                \DB::commit();

                // Log transaction creation
                \Log::info('Wallet Transaction Created', [
                    'transaction_id' => $transaction->id,
                    'user_id' => $user->id,
                    'amount' => $earning,
                    'old_balance' => $oldBalance,
                    'new_balance' => $user->wallet_balance,
                    'level' => $currentLevel,
                    'captcha_solve_id' => $captchaSolve->id
                ]);
                
            } catch (\Exception $e) {
                // Rollback the transaction in case of error
                \DB::rollBack();
                \Log::error('Error updating wallet balance: ' . $e->getMessage(), [
                    'user_id' => $user->id,
                    'error' => $e->getTraceAsString()
                ]);
                
                // Re-throw the exception to be handled by Laravel's exception handler
                throw $e;
            }
        }

        $remaining = (strtolower((string)$limitRaw) === 'unlimited') ? 'unlimited' : max(0, ((int)$limitRaw) - $todayCount - 1);
        
        // Refresh user data to ensure we have the latest wallet balance
        $user->refresh();
        
        return response()->json([
            'status' => 'success', 
            'message' => 'Captcha solved', 
            'level' => (int)$user->level, 
            'current_level' => (int)$currentLevel,
            'captchas_in_level' => $captchasInCurrentLevel,
            'captchas_required' => (int)$captchasPerLevel,
            'remaining' => $remaining, 
            'wallet_balance' => $user->wallet_balance, 
            'earned' => $earning,
            'level_completed' => $levelCompleted,
            'total_captchas' => $totalCaptchas
        ]);
    }

    // GET /api/v1/captcha/level
    public function getLevel(Request $request)
    {
        $user = Auth::user();
        $plan = SubscriptionPlan::where('name', $user->subscription_name)->first();
        $limitRaw = $plan->captcha_per_day ?? $plan->caption_limit ?? 0;
        $todayCount = CaptchaSolve::where('user_id', $user->id)
            ->whereDate('created_at', now()->toDateString())
            ->count();
        $todayLevel = CaptchaSolve::where('user_id', $user->id)
            ->whereDate('created_at', now()->toDateString())
            ->count(); // Today's level
        $totalLevel = CaptchaSolve::where('user_id', $user->id)->count(); // All-time total
        $remaining = (strtolower((string)$limitRaw) === 'unlimited') ? 'unlimited' : max(0, ((int)$limitRaw) - $todayCount);
        return response()->json([
            'status' => 'success',
            'level' => $todayLevel, // Today's level (resets daily)
            'total_level' => $totalLevel, // all-time total
            'remaining_today' => $remaining,
            'plan_limit' => $limitRaw,
            'wallet_balance' => $user->wallet_balance,
        ]);
    }

    // GET /api/v1/captcha/level/{user_id} (admin only)
    public function getLevelByUserId($user_id)
    {
        $authUser = Auth::user();
        if (!$authUser->hasRole('admin')) {
            return response()->json(['status' => 'error', 'message' => 'Forbidden'], 403);
        }
        $user = \App\Models\User::find($user_id);
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'User not found'], 404);
        }
        $plan = SubscriptionPlan::where('name', $user->subscription_name)->first();
        $limitRaw = $plan->captcha_per_day ?? $plan->caption_limit ?? 0;
        $todayCount = CaptchaSolve::where('user_id', $user->id)
            ->whereDate('created_at', now()->toDateString())
            ->count();
        $todayLevel = CaptchaSolve::where('user_id', $user->id)
            ->whereDate('created_at', now()->toDateString())
            ->count(); // Today's level
        $totalLevel = CaptchaSolve::where('user_id', $user->id)->count(); // All-time total
        $remaining = (strtolower((string)$limitRaw) === 'unlimited') ? 'unlimited' : max(0, ((int)$limitRaw) - $todayCount);
        return response()->json([
            'status' => 'success',
            'user_id' => $user->id,
            'level' => $todayLevel, // Today's level (resets daily)
            'total_level' => $totalLevel, // All-time total
            'remaining_today' => $remaining,
            'plan_limit' => $limitRaw,
            'wallet_balance' => $user->wallet_balance,
        ]);
    }

    // POST /api/v1/captcha/level-by-user (admin only, user_id in body)
    public function getLevelByUserIdFromBody(Request $request)
    {
        $authUser = Auth::user();
        if (!$authUser->hasRole('admin')) {
            return response()->json(['status' => 'error', 'message' => 'Forbidden'], 403);
        }
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
        ]);
        $user = \App\Models\User::find($request->user_id);
        $plan = SubscriptionPlan::where('name', $user->subscription_name)->first();
        $limitRaw = $plan->captcha_per_day ?? $plan->caption_limit ?? 0;
        $todayCount = CaptchaSolve::where('user_id', $user->id)
            ->whereDate('created_at', now()->toDateString())
            ->count();
        $todayLevel = CaptchaSolve::where('user_id', $user->id)
            ->whereDate('created_at', now()->toDateString())
            ->count(); // Today's level
        $totalLevel = CaptchaSolve::where('user_id', $user->id)->count(); // All-time total
        $remaining = (strtolower((string)$limitRaw) === 'unlimited') ? 'unlimited' : max(0, ((int)$limitRaw) - $todayCount);
        return response()->json([
            'status' => 'success',
            'user_id' => $user->id,
            'level' => $todayLevel, // Today's level (resets daily)
            'total_level' => $totalLevel, // All-time total
            'remaining_today' => $remaining,
            'plan_limit' => $limitRaw,
            'wallet_balance' => $user->wallet_balance,
        ]);
    }

    // GET /api/v1/captcha/todays-earning
    public function getTodaysEarning(Request $request)
    {
        $user = Auth::user();
        $plan = SubscriptionPlan::where('name', $user->subscription_name)->first();
        if (!$plan) {
            return response()->json(['status' => 'error', 'message' => 'No active plan.'], 400);
        }
        
        // Get the plan's earnings configuration
        $earnings = is_string($plan->earnings) ? json_decode($plan->earnings, true) : $plan->earnings;
        if (!is_array($earnings)) {
            $earnings = [];
        }
        
        // Get today's solves ordered by creation time
        $todaySolves = \App\Models\CaptchaSolve::where('user_id', $user->id)
            ->whereDate('created_at', now()->toDateString())
            ->orderBy('created_at', 'asc')
            ->get();
            
        $totalEarning = 0;
        $solveNumber = 0;
        $captchasPerLevel = (int)($plan->captchas_per_level ?? 1);
        $levelEarnings = []; // Track earnings per level
        
        // Group solves into levels
        $currentLevel = 1;
        $solvesInCurrentLevel = 0;
        
        foreach ($todaySolves as $solve) {
            $solveNumber++;
            $solvesInCurrentLevel++;
            
            // Check if we've completed a level
            if ($solvesInCurrentLevel >= $captchasPerLevel) {
                // Calculate which level was completed
                $completedLevel = $currentLevel;
                
                // Get the earning for this level
                $levelEarning = 0;
                
                // Check for exact level match first
                if (isset($earnings[(string)$completedLevel])) {
                    $levelEarning = (float)$earnings[(string)$completedLevel];
                } else {
                    // Check for after_X pattern
                    $fallbackEarning = 0;
                    foreach ($earnings as $key => $amount) {
                        if (strpos($key, 'after_') === 0) {
                            $after = (int)substr($key, 6);
                            if ($completedLevel > $after) {
                                $fallbackEarning = (float)$amount;
                            }
                        }
                    }
                    $levelEarning = $fallbackEarning;
                }
                
                // Add to total and reset for next level
                if ($levelEarning > 0) {
                    $totalEarning += $levelEarning;
                    $levelEarnings[$completedLevel] = $levelEarning;
                }
                
                // Reset for next level
                $solvesInCurrentLevel = 0;
                $currentLevel++;
            }
        }
        
        // Log for debugging
        \Log::info('Today\'s earnings calculation', [
            'user_id' => $user->id,
            'total_solves' => $solveNumber,
            'captchas_per_level' => $captchasPerLevel,
            'levels_completed' => array_keys($levelEarnings),
            'level_earnings' => $levelEarnings,
            'total_earning' => $totalEarning
        ]);
        
        return response()->json([
            'status' => 'success',
            'todays_earning' => round($totalEarning, 2),
            'solves_today' => $solveNumber,
            'levels_completed' => array_keys($levelEarnings),
            'level_earnings' => $levelEarnings
        ]);
    }
} 