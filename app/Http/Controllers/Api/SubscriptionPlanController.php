<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;

class SubscriptionPlanController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.admin')->only(['store']);
        $this->middleware('auth:api')->only(['purchase']);
    }

    public function index()
    {
        $user = auth()->user();
        $plans = SubscriptionPlan::with('agentCommissionTiers')->get();
        
        $userPlans = $plans->map(function ($plan) use ($user) {
            $planData = [
                'id' => $plan->id,
                'name' => $plan->name,
                'cost' => (float) $plan->cost,
                'currency' => $plan->currency ?? 'INR',
                'earning_type' => $plan->earning_type,
                'captcha_per_day' => $plan->captcha_per_day,
                'captchas_per_level' => $plan->captchas_per_level,
                'earnings' => is_string($plan->earnings) ? json_decode($plan->earnings, true) : $plan->earnings,
                'min_withdrawal_limit' => $plan->min_withdrawal_limit,
                'plan_type' => $plan->plan_type,
                'icon' => $plan->icon,
                'image' => $plan->image,
                'caption_limit' => $plan->caption_limit,
                'referral_earning_per_ref' => $plan->referral_earning_per_ref,
                'daily_captcha_earning_with_ref' => $plan->daily_captcha_earning_with_ref,
                'bonus_10_referrals' => $plan->bonus_10_referrals,
                'gift_10_referrals' => $plan->gift_10_referrals,
                'bonus_20_referrals' => $plan->bonus_20_referrals,
                'gift_20_referrals' => $plan->gift_20_referrals,
                'daily_limit_bonus' => $plan->daily_limit_bonus,
                'unlimited_earning_rate' => $plan->unlimited_earning_rate,
                'min_daily_earning' => (int) $plan->min_daily_earning,
                
                'commission_tiers' => $plan->agentCommissionTiers->map(function($tier) {
                    return [
                        'id' => $tier->id,
                        'min_referrals' => (int) $tier->min_referrals,
                        'max_referrals' => $tier->max_referrals ? (int) $tier->max_referrals : null,
                        'commission_amount' => (float) $tier->commission_amount,
                        'referral_range' => $tier->referral_range,
                    ];
                })->sortBy('min_referrals')->values(),
            ];
            
            // If the request is from an agent, calculate their potential commission
            if ($user && $user->hasRole('agent')) {
                $referralCount = \App\Models\User::where('agent_id', $user->id)
                    ->where('id', '<>', $user->id)
                    ->whereHas('subscription')
                    ->count();
                
                // Include the current user if they are already counted as referred
                $totalReferrals = $referralCount + 1;
                
                // Find the appropriate commission tier
                $applicableTier = $plan->agentCommissionTiers
                    ->where('min_referrals', '<=', $totalReferrals)
                    ->where(function($query) use ($totalReferrals) {
                        $query->where('max_referrals', '>=', $totalReferrals)
                              ->orWhereNull('max_referrals');
                    })
                    ->sortByDesc('min_referrals')
                    ->first();
                
                if ($applicableTier) {
                    $planData['applicable_commission'] = (float) $applicableTier->commission_amount;
                    $planData['current_referral_count'] = $totalReferrals;
                    $planData['next_tier'] = $plan->agentCommissionTiers
                        ->where('min_referrals', '>', $totalReferrals)
                        ->sortBy('min_referrals')
                        ->map(function($tier) {
                            return [
                                'min_referrals' => (int) $tier->min_referrals,
                                'max_referrals' => $tier->max_referrals ? (int) $tier->max_referrals : null,
                                'commission_amount' => (float) $tier->commission_amount,
                            ];
                        })
                        ->first();
                }
            }
            
            return $planData;
        });
        
        return response()->json([
            'status' => 'success',
            'user_plans' => $userPlans,
            'meta' => [
                'is_agent' => $user && $user->hasRole('agent'),
                'current_referral_count' => $user && $user->hasRole('agent') ? 
                    \App\Models\User::where('agent_id', $user->id)
                        ->where('id', '<>', $user->id)
                        ->whereHas('subscription')
                        ->count() : null,
            ]
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'icon' => 'nullable|string',
            'caption_limit' => 'required|integer',
        ]);

        $plan = SubscriptionPlan::create($request->only(['name', 'icon', 'caption_limit']));
        return response()->json($plan, 201);
    }

    public function purchase(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|integer|exists:subscription_plans,id',
            'user_id' => 'required|integer|exists:users,id',
        ]);
        
        $user = \App\Models\User::find($request->user_id);
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'User not found.'], 404);
        }
        if ($user->hasRole('admin')) {
            return response()->json(['status' => 'error', 'message' => 'Admins cannot purchase plans.'], 403);
        }
        
        $plan = \App\Models\SubscriptionPlan::find($request->plan_id);
        if (!$plan) {
            return response()->json(['status' => 'error', 'message' => 'Plan not found.'], 404);
        }

        // Start database transaction
        \DB::beginTransaction();
        
        try {
            // Update user with plan purchase
            $user->subscription_name = $plan->name; // This will be used to identify the plan
            $user->purchased_date = now();
            $user->total_amount_paid = $plan->cost;
            
            // Save the user first to ensure we have the subscription updated
            $user->save();
            
            // Refresh the user model to ensure we have the latest data
            $user->refresh();
            
            // Log the purchase details
            \Log::info('Plan purchased', [
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'plan_name' => $plan->name,
                'referred_by' => $user->referred_by,
                'subscription_updated' => true
            ]);

            // Handle agent reward using the helper method
            $agentRewardResult = $user->handleAgentRewardForSubscription($plan);
            
            // Handle user referral reward if this user was referred by another user
            $userReferralResult = [];
            if ($user->referred_by) {
                // Log before processing referral reward
                \Log::info('Processing referral reward', [
                    'user_id' => $user->id,
                    'referred_by' => $user->referred_by,
                    'has_subscription' => $user->hasSubscription()
                ]);
                
                $userReferralResult = $user->handleUserReferralReward($plan);
                
                // Log the result of the referral reward processing
                \Log::info('Referral reward processed', [
                    'user_id' => $user->id,
                    'referred_by' => $user->referred_by,
                    'result' => $userReferralResult
                ]);
            }
            
            \DB::commit();
            
            $response = [
                'status' => 'success',
                'message' => 'Plan purchased successfully.',
                'user' => $user,
                'plan' => $plan
            ];
            
            // Add agent reward information to response if agent was rewarded
            if ($agentRewardResult['rewarded']) {
                $response['agent_reward'] = [
                    'rewarded' => true,
                    'reward_amount' => $agentRewardResult['reward_amount'],
                    'message' => $agentRewardResult['message']
                ];
            }
            
            // Add user referral reward information to response if referrer was rewarded
            if (!empty($userReferralResult) && $userReferralResult['rewarded']) {
                $response['referral_reward'] = [
                    'rewarded' => true,
                    'reward_amount' => $userReferralResult['reward_amount'],
                    'message' => $userReferralResult['message']
                ];
            }
            
            return response()->json($response);
            
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Subscription plan purchase failed', [
                'user_id' => $user->id,
                'plan_id' => $request->plan_id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to purchase plan. Please try again.'
            ], 500);
        }
    }

    /**
     * Get plan details by user ID (admin only)
     * POST /api/v1/plans/by-user-id
     * Body: { user_id: int }
     */
    public function getPlanByUserId(Request $request)
    {
        // If called from the user route, always use the authenticated user's ID
        $route = $request->route()->getName() ?? $request->path();
        $isUserRoute = str_contains($route, 'by-user-id-user');
        $userId = $isUserRoute ? auth()->id() : $request->input('user_id');

        if (!$userId) {
            return response()->json(['status' => 'error', 'message' => 'User ID not found.'], 400);
        }

        $user = \App\Models\User::find($userId);
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'User not found.'], 404);
        }
        if (!$user->subscription_name) {
            return response()->json(['status' => 'error', 'message' => 'User has not purchased any plan.'], 404);
        }
        $plan = \App\Models\SubscriptionPlan::with('agentCommissionTiers')->where('name', $user->subscription_name)->first();
        if (!$plan) {
            return response()->json(['status' => 'error', 'message' => 'Plan not found.'], 404);
        }
        
        // Get level-based earnings
        $levelEarnings = is_string($plan->earnings) ? json_decode($plan->earnings, true) : $plan->earnings;
        
        // Format commission tiers
        $commissionTiers = $plan->agentCommissionTiers->map(function($tier) {
            return [
                'id' => $tier->id,
                'min_referrals' => (int) $tier->min_referrals,
                'max_referrals' => $tier->max_referrals ? (int) $tier->max_referrals : null,
                'commission_amount' => (float) $tier->commission_amount,
                'referral_range' => $tier->referral_range,
            ];
        })->sortBy('min_referrals')->values();
        
        return response()->json([
            'status' => 'success',
            'user_id' => $user->id,
            'plan' => [
                'id' => $plan->id,
                'name' => $plan->name,
                'cost' => (float) $plan->cost,
                'currency' => $plan->currency ?? 'INR',
                'earning_type' => $plan->earning_type,
                'captcha_per_day' => $plan->captcha_per_day,
                'captchas_per_level' => $plan->captchas_per_level,
                'earnings' => $levelEarnings,
                'level_earnings' => $levelEarnings, // Duplicate for backward compatibility
                'min_withdrawal_limit' => $plan->min_withdrawal_limit,
                'plan_type' => $plan->plan_type,
                'icon' => $plan->icon,
                'image' => $plan->image,
                'caption_limit' => $plan->caption_limit,
                'min_daily_earning' => (int) $plan->min_daily_earning,
                'purchased_date' => $user->purchased_date ?? null,
                'commission_tiers' => $commissionTiers,
                'referral_earning_per_ref' => $plan->referral_earning_per_ref,
                'daily_captcha_earning_with_ref' => $plan->daily_captcha_earning_with_ref,
                'bonus_10_referrals' => $plan->bonus_10_referrals,
                'gift_10_referrals' => $plan->gift_10_referrals,
                'bonus_20_referrals' => $plan->bonus_20_referrals,
                'gift_20_referrals' => $plan->gift_20_referrals,
                'daily_limit_bonus' => $plan->daily_limit_bonus,
                'unlimited_earning_rate' => $plan->unlimited_earning_rate,
                'has_commission_tiers' => !$commissionTiers->isEmpty(),
            ],
        ]);
    }
}