<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AgentReferralController extends Controller
{
    /**
     * Get agent referral code and list of referred users
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function referrals(Request $request)
    {
        $agent = Auth::guard('agent')->user();
        if (!$agent) {
            return response()->json([
                'status' => 'error',
                'message' => 'Agent not authenticated.'
            ], 401);
        }

        // Update referral count to ensure accuracy
        $agent->updateReferralCount();

        $referralCode = $agent->referral_code;
        $referredUsers = User::where('agent_id', $agent->id)->get();

        $users = $referredUsers->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'phone' => $this->maskPhone($user->phone),
                'profile_completed' => (bool) $user->profile_completed,
                'registered_at' => $user->created_at->toDateTimeString(),
            ];
        });
        // If no real users, add a dummy user
        if ($users->isEmpty()) {
            $users = collect([
                [
                    'id' => 0,
                    'name' => 'Dummy User',
                    'phone' => '9999*****00',
                    'profile_completed' => false,
                    'registered_at' => now()->toDateTimeString(),
                ]
            ]);
        }

        // Get milestone status and available bonuses
        $milestoneStatus = $agent->getMilestoneStatus();
        $availableBonuses = $agent->getAvailableBonuses();

        return response()->json([
            'status' => 'success',
            'referral_code' => $referralCode,
            'users' => $users,
            'milestone_status' => $milestoneStatus,
            'available_bonuses' => $availableBonuses,
            // 'total_referral_earnings' => $totalReferralEarnings,
        ]);
    }

    /**
     * Mask phone number for privacy (e.g., +91 992*****12)
     */
    private function maskPhone($phone)
    {
        if (!$phone) return null;
        // Show first 4 and last 2 digits, mask the rest
        return substr($phone, 0, 4) . str_repeat('*', max(0, strlen($phone) - 6)) . substr($phone, -2);
    }

    /**
     * Get total referral earnings for an agent
     */
    public function referralEarnings(Request $request)
    {
        // Check for agent authentication
        $authAgent = \Auth::guard('agent')->user();
        if (!$authAgent) {
            return response()->json([
                'status' => 'error',
                'message' => 'Agent not authenticated.'
            ], 401);
        }

        // Accept agent_id from body (POST/JSON)
        $agentId = $request->input('agent_id');
        if (!$agentId) {
            return response()->json([
                'status' => 'error',
                'message' => 'agent_id is required.'
            ], 422);
        }
        $agent = \App\Models\Agent::find($agentId);
        if (!$agent) {
            return response()->json([
                'status' => 'error',
                'message' => 'Agent not found',
            ], 404);
        }

        // Sum all wallet transactions for this agent where description contains 'referral'
        $totalReferralEarnings = $agent->walletTransactions()
            ->where('type', 'credit')
            ->where('description', 'like', '%referral%')
            ->sum('amount');

        $transactions = $agent->walletTransactions()
            ->where('type', 'credit')
            ->where('description', 'like', '%referral%')
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_referral_earnings' => $totalReferralEarnings,
                'transactions' => $transactions
            ]
        ]);
    }
} 