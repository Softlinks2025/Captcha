<?php

namespace App\Http\Controllers\Api\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MilestoneController extends Controller
{
    /**
     * Get agent's milestone status
     */
    public function getMilestoneStatus()
    {
        try {
            $agent = Auth::guard('agent')->user();
            
            if (!$agent) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Agent not authenticated.'
                ], 401);
            }

            // Update referral count to ensure accuracy
            $agent->updateReferralCount();
            
            $milestoneStatus = $agent->getMilestoneStatus();
            $availableBonuses = $agent->getAvailableBonuses();

            return response()->json([
                'status' => 'success',
                'message' => 'Milestone status retrieved successfully',
                'data' => [
                    'milestone_status' => $milestoneStatus,
                    'available_bonuses' => $availableBonuses
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve milestone status',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Claim T-shirt bonus
     */
    public function claimTshirtBonus()
    {
        try {
            $agent = Auth::guard('agent')->user();
            
            if (!$agent) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Agent not authenticated.'
                ], 401);
            }

            $result = $agent->claimTshirtBonus();

            if ($result['success']) {
                return response()->json([
                    'status' => 'success',
                    'message' => $result['message'],
                    'data' => [
                        'bonus_type' => 'tshirt',
                        'milestone' => 50,
                        'claimed_at' => now()
                    ]
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => $result['message']
                ], 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to claim T-shirt bonus',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Claim Bag bonus
     */
    public function claimBagBonus()
    {
        try {
            $agent = Auth::guard('agent')->user();
            
            if (!$agent) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Agent not authenticated.'
                ], 401);
            }

            $result = $agent->claimBagBonus();

            if ($result['success']) {
                return response()->json([
                    'status' => 'success',
                    'message' => $result['message'],
                    'data' => [
                        'bonus_type' => 'bag',
                        'milestone' => 100,
                        'claimed_at' => now()
                    ]
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => $result['message']
                ], 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to claim Bag bonus',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get detailed referral statistics
     */
    public function getReferralStats()
    {
        try {
            $agent = Auth::guard('agent')->user();
            
            if (!$agent) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Agent not authenticated.'
                ], 401);
            }

            // Update referral count
            $agent->updateReferralCount();
            
            $referredUsers = $agent->referredUsers()->count();
            $milestoneStatus = $agent->getMilestoneStatus();
            
            // Calculate next milestone
            $nextMilestone = null;
            $milestones = [10, 50, 100];
            
            foreach ($milestones as $milestone) {
                if ($referredUsers < $milestone) {
                    $nextMilestone = [
                        'target' => $milestone,
                        'remaining' => $milestone - $referredUsers,
                        'progress' => ($referredUsers / $milestone) * 100
                    ];
                    break;
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Referral statistics retrieved successfully',
                'data' => [
                    'total_referrals' => $referredUsers,
                    'milestone_status' => $milestoneStatus,
                    'next_milestone' => $nextMilestone,
                    'available_bonuses' => $agent->getAvailableBonuses()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve referral statistics',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
