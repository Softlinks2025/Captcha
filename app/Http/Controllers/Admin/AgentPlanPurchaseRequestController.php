<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AgentPlanPurchaseRequest;
use App\Models\AgentPlanSubscription;
use App\Models\AgentPlan;
use Illuminate\Support\Facades\DB;

class AgentPlanPurchaseRequestController extends Controller
{
    // List all requests
    public function index()
    {
        $requests = AgentPlanPurchaseRequest::with('agent', 'plan')->orderByDesc('created_at')->get();
        return view('admin.agent-plan-purchase-requests.index', compact('requests'));
    }

    // Approve a request
    public function approve($id)
    {
        $request = AgentPlanPurchaseRequest::findOrFail($id);
        if ($request->status !== 'pending') {
            return back()->with('error', 'Request is not pending.');
        }
        DB::beginTransaction();
        try {
            $request->status = 'approved';
            $request->approved_at = now();
            $request->save();
            // Activate the plan for the agent
            AgentPlanSubscription::create([
                'agent_id' => $request->agent_id,
                'plan_id' => $request->plan_id,
                'amount_paid' => AgentPlan::find($request->plan_id)->price,
                'payment_method' => 'admin_approval',
                'transaction_id' => 'ADMIN_' . uniqid($request->agent_id . '_'),
                'status' => 'active',
                'started_at' => now(),
                'expires_at' => null, // Set based on plan duration if needed
                'total_logins' => 0,
                'total_earnings' => 0.00
            ]);

            // Retroactively credit referral rewards for users referred before plan activation
            $agent = \App\Models\Agent::find($request->agent_id);
            $plan = \App\Models\AgentPlan::find($request->plan_id);
            if ($agent && $plan) {
                $referredUsers = \App\Models\User::where('agent_id', $agent->id)->get();
                foreach ($referredUsers as $user) {
                    // Check if this referral has already been credited
                    $referral = \App\Models\UserReferral::where('referred_id', $user->id)
                        ->where('referrer_id', $agent->id)
                        ->where('reward_credited', false)
                        ->first();
                    if ($referral) {
                        // Calculate reward based on earning range
                        $referralCount = \App\Models\User::where('agent_id', $agent->id)
                            ->where('id', '<=', $user->id)
                            ->count();
                        $reward = 0;
                        $earningRanges = $plan->earning_ranges ? json_decode($plan->earning_ranges, true) : [];
                        foreach ($earningRanges as $range) {
                            $min = (int)$range['min'];
                            $max = strtolower($range['max']) === 'unlimited' ? PHP_INT_MAX : (int)$range['max'];
                            if ($referralCount >= $min && $referralCount <= $max) {
                                $reward = (float)$range['rate'];
                                break;
                            }
                        }
                        $agent->wallet_balance += $reward;
                        $agent->total_earnings += $reward;
                        $agent->save();
                        // Log the transaction
                        \App\Models\AgentWalletTransaction::create([
                            'agent_id' => $agent->id,
                            'amount' => $reward,
                            'type' => 'credit',
                            'description' => 'Referral reward for user #' . $user->id,
                        ]);
                        // Mark referral as credited
                        $referral->reward_credited = true;
                        $referral->save();
                    }
                }
            }
            DB::commit();
            return back()->with('success', 'Request approved and plan activated.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to approve: ' . $e->getMessage());
        }
    }

    // Reject a request
    public function reject(Request $request, $id)
    {
        $purchaseRequest = AgentPlanPurchaseRequest::findOrFail($id);
        if ($purchaseRequest->status !== 'pending') {
            return back()->with('error', 'Request is not pending.');
        }
        $purchaseRequest->status = 'rejected';
        $purchaseRequest->rejected_at = now();
        $purchaseRequest->admin_note = $request->input('admin_note');
        $purchaseRequest->save();
        return back()->with('success', 'Request rejected.');
    }
} 