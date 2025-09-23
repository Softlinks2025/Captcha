<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MilestoneController extends Controller
{
    /**
     * Get user's milestone status
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMilestoneStatus()
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated.'
                ], 401);
            }

            // Get user's subscription plan
            $subscription = $user->subscription;
            
            if (!$subscription) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active subscription found.'
                ], 400);
            }

            // Get milestone configuration from subscription
            $milestones = [
                10 => [
                    'reached' => (bool)$user->milestone_10_reached,
                    'claimed' => (bool)$user->bonus_claimed_10,
                    'bonus_amount' => $subscription->bonus_10_referrals ?? 0,
                    'gift' => $subscription->gift_10_referrals ?? null,
                    'description' => '10 Referrals Bonus'
                ],
                50 => [
                    'reached' => (bool)$user->milestone_50_reached,
                    'claimed' => (bool)$user->bonus_claimed_50,
                    'bonus_amount' => $subscription->bonus_50_referrals ?? 0,
                    'gift' => $subscription->gift_50_referrals ?? null,
                    'description' => '50 Referrals Bonus'
                ],
                100 => [
                    'reached' => (bool)$user->milestone_100_reached,
                    'claimed' => (bool)$user->bonus_claimed_100,
                    'bonus_amount' => $subscription->bonus_100_referrals ?? 0,
                    'gift' => $subscription->gift_100_referrals ?? null,
                    'description' => '100 Referrals Bonus'
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'total_referrals' => $user->total_referrals,
                    'milestones' => $milestones
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get milestone status',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Claim a milestone bonus
     *
     * @param Request $request
     * @param int $milestone
     * @return \Illuminate\Http\JsonResponse
     */
    public function claimMilestoneBonus(Request $request, $milestone)
    {
        try {
            $user = Auth::user();
            $milestone = (int)$milestone;
            
            if (!in_array($milestone, [10, 50, 100])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid milestone.'
                ], 422);
            }

            // Get milestone fields
            $reachedField = "milestone_{$milestone}_reached";
            $claimedField = "bonus_claimed_{$milestone}";
            $bonusField = "bonus_{$milestone}_referrals";
            $giftField = "gift_{$milestone}_referrals";

            // Check if milestone is reached
            if (!$user->$reachedField) {
                return response()->json([
                    'success' => false,
                    'message' => 'Milestone not reached yet.'
                ], 400);
            }

            // Check if already claimed
            if ($user->$claimedField) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bonus already claimed.'
                ], 400);
            }

            // Get subscription details
            $subscription = $user->subscription;
            if (!$subscription) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active subscription found.'
                ], 400);
            }

            // Start transaction
            DB::beginTransaction();

            // Get bonus amount
            $bonusAmount = $subscription->$bonusField ?? 0;
            $gift = $subscription->$giftField ?? null;

            // Update user wallet if bonus amount is positive
            if ($bonusAmount > 0) {
                $user->wallet_balance += $bonusAmount;
                
                // Record transaction
                $user->walletTransactions()->create([
                    'amount' => $bonusAmount,
                    'type' => 'milestone_bonus',
                    'status' => 'completed',
                    'description' => "Milestone {$milestone} Referrals Bonus",
                    'reference_id' => 'MILESTONE_' . $milestone . '_' . now()->timestamp,
                ]);
            }

            // Update milestone status
            $user->$claimedField = true;
            $user->last_bonus_claimed_at = now();
            $user->save();

            // Create notification for gift if applicable
            if ($gift) {
                $user->notifications()->create([
                    'type' => 'milestone_gift',
                    'data' => [
                        'title' => 'Milestone Achievement!',
                        'message' => "Congratulations! You've earned a gift for reaching {$milestone} referrals: {$gift}",
                        'milestone' => $milestone,
                        'gift' => $gift,
                    ],
                    'read_at' => null,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Bonus claimed successfully!',
                'data' => [
                    'bonus_amount' => $bonusAmount,
                    'gift' => $gift,
                    'new_balance' => $user->wallet_balance
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to claim bonus',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
