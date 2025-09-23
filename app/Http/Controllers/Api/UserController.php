<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function __construct()
    {
        // Removed jwt.admin middleware to allow access for all authenticated users/agents
    }

    public function index()
    {
        return User::with('roles')->paginate(10);
    }

    /**
     * Get a list of all users (for contact matching)
     * GET /api/v1/users/list
     */
    public function list(Request $request)
    {
        $users = \App\Models\User::select('id', 'name', 'phone', 'profile_photo_path')->get();
        $users = $users->map(function($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
                'profile_photo_url' => $user->profile_photo_path ? asset('storage/' . $user->profile_photo_path) : null,
            ];
        });
        return response()->json(['status' => 'success', 'users' => $users]);
    }

    /**
     * Get user referral bonuses and statistics
     * GET /api/v1/user/referral-bonuses
     */
    public function getReferralBonuses(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not authenticated'
                ], 401);
            }

            // Calculate referral statistics
            $totalReferrals = $user->referredUsers()->count();
            $referralEarnings = $user->walletTransactions()
                ->where('type', 'referral_earning')
                ->sum('amount');
            $bonusEarnings = $user->walletTransactions()
                ->whereIn('type', ['bonus_10_referrals', 'bonus_20_referrals', 'daily_limit_bonus'])
                ->sum('amount');
            $totalBonusEarnings = $referralEarnings + $bonusEarnings;
            
            // Get user's subscription plan for bonus rates
            $subscriptionPlan = $user->subscriptionPlan;
            
            // Calculate milestone progress
            $milestone10Progress = min(100, ($totalReferrals / 10) * 100);
            $milestone20Progress = min(100, ($totalReferrals / 20) * 100);
            
            // Get recent bonus transactions
            $recentBonusTransactions = $user->walletTransactions()
                ->whereIn('type', ['referral_earning', 'bonus_10_referrals', 'bonus_20_referrals', 'daily_limit_bonus'])
                ->latest()
                ->take(10)
                ->get()
                ->map(function($transaction) {
                    return [
                        'id' => $transaction->id,
                        'amount' => $transaction->amount,
                        'type' => $transaction->type,
                        'description' => $transaction->description,
                        'created_at' => $transaction->created_at->format('Y-m-d H:i:s'),
                        'type_label' => $this->getTransactionTypeLabel($transaction->type)
                    ];
                });

            // Build response data
            $response = [
                'status' => 'success',
                'data' => [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'referral_code' => $user->referral_code,
                    
                    // Referral Statistics
                    'referral_stats' => [
                        'total_referrals' => $totalReferrals,
                        'referral_earnings' => $referralEarnings,
                        'bonus_earnings' => $bonusEarnings,
                        'total_bonus_earnings' => $totalBonusEarnings,
                        'per_referral_rate' => $subscriptionPlan ? $subscriptionPlan->referral_earning_per_ref : 0,
                    ],
                    
                    // Subscription Plan Info
                    'subscription_plan' => $subscriptionPlan ? [
                        'name' => $subscriptionPlan->name,
                        'referral_earning_per_ref' => $subscriptionPlan->referral_earning_per_ref,
                        'bonus_10_referrals' => $subscriptionPlan->bonus_10_referrals,
                        'bonus_20_referrals' => $subscriptionPlan->bonus_20_referrals,
                        'gift_10_referrals' => $subscriptionPlan->gift_10_referrals,
                        'gift_20_referrals' => $subscriptionPlan->gift_20_referrals,
                        'daily_limit_bonus' => $subscriptionPlan->daily_limit_bonus,
                    ] : null,
                    
                    // Milestone Progress
                    'milestones' => [
                        '10_referrals' => [
                            'target' => 10,
                            'current' => $totalReferrals,
                            'progress' => $milestone10Progress,
                            'achieved' => $totalReferrals >= 10,
                            'bonus_amount' => $subscriptionPlan ? $subscriptionPlan->bonus_10_referrals : 0,
                            'gift' => $subscriptionPlan ? $subscriptionPlan->gift_10_referrals : null,
                        ],
                        '20_referrals' => [
                            'target' => 20,
                            'current' => $totalReferrals,
                            'progress' => $milestone20Progress,
                            'achieved' => $totalReferrals >= 20,
                            'bonus_amount' => $subscriptionPlan ? $subscriptionPlan->bonus_20_referrals : 0,
                            'gift' => $subscriptionPlan ? $subscriptionPlan->gift_20_referrals : null,
                        ],
                    ],
                    
                    // Recent Transactions
                    'recent_transactions' => $recentBonusTransactions,
                ]
            ];

            return response()->json($response);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch referral bonuses',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get transaction type label for display
     */
    private function getTransactionTypeLabel($type)
    {
        switch ($type) {
            case 'referral_earning':
                return 'Referral Earning';
            case 'bonus_10_referrals':
                return '10 Referrals Bonus';
            case 'bonus_20_referrals':
                return '20 Referrals Bonus';
            case 'daily_limit_bonus':
                return 'Daily Limit Bonus';
            default:
                return ucfirst(str_replace('_', ' ', $type));
        }
    }
}