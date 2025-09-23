<?php

namespace App\Http\Controllers\Api\Agent;

use App\Http\Controllers\Controller;
use App\Models\AgentPlan;
use App\Models\AgentPlanSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlanController extends Controller
{
    /**
     * Get all active plans
     */
    public function index()
    {
        try {
            $plans = AgentPlan::active()
                ->orderBy('sort_order')
                ->get()
                ->map(function ($plan) {
                    return [
                        'id' => $plan->id,
                        'name' => $plan->name,
                        'description' => $plan->description,
                        'price' => $plan->price,
                        'duration' => $plan->duration,
                        'icon' => $plan->icon ?? null
                    ];
                });

            return response()->json([
                'status' => 'success',
                'message' => 'Plans retrieved successfully',
                'data' => [
                    'plans' => $plans
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve plans',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get a specific plan by ID
     */
    public function show($id)
    {
        try {
            $plan = AgentPlan::active()->findOrFail($id);

            $planData = [
                'id' => $plan->id,
                'name' => $plan->name,
                'description' => $plan->description,
                'price' => $plan->price,
                'duration' => $plan->duration,
                'icon' => $plan->icon ?? null
            ];

            return response()->json([
                'status' => 'success',
                'message' => 'Plan retrieved successfully',
                'data' => [
                    'plan' => $planData
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Plan not found or not available',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 404);
        }
    }

    /**
     * Purchase a plan
     */
    public function purchase(Request $request)
    {
        try {
            $request->validate([
                'plan_id' => 'required|exists:agent_plans,id',
                'agent_id' => 'required|integer|exists:agents,id'
            ]);

            $agent = auth('agent')->user();
            if ($request->agent_id != $agent->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Agent ID does not match the authenticated agent.'
                ], 403);
            }

            $plan = AgentPlan::findOrFail($request->plan_id);

            // Check if plan is active
            if (!$plan->is_active) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This plan is not available for purchase'
                ], 400);
            }

            // Check if agent already has an active plan
            if ($agent->hasActivePlan()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You already have an active plan'
                ], 400);
            }

            DB::beginTransaction();

            try {
                // Instead of activating, create a pending purchase request
                
                \App\Models\AgentPlanPurchaseRequest::create([
                    'agent_id' => $agent->id,
                    'plan_id' => $plan->id,
                    'status' => 'pending',
                    'requested_at' => now(),
                ]);

                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Plan purchase request submitted and is pending admin approval.'
                ]);

            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to purchase plan',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get agent's current plan
     */
    public function myPlan(Request $request)
    {
        try {
            // Accept agent_id from body (POST/JSON), then query param, then default to authenticated agent
            $agentId = $request->input('agent_id') ?? $request->query('agent_id');
            if ($agentId) {
                $agent = \App\Models\Agent::find($agentId);
                if (!$agent) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Agent not found',
                    ], 404);
                }
            } else {
                $agent = auth('agent')->user();
            }

            // Try to get the latest active subscription first
            $subscription = $agent->activePlanSubscription()->latest('started_at')->first();
            $debug = [];
            if (!$subscription) {
                $subscription = $agent->planSubscriptions()->orderByDesc('started_at')->first();
                $debug['all_subscriptions_count'] = $agent->planSubscriptions()->count();
                $debug['latest_subscription'] = $subscription;
            }

            if (!$subscription) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No plan found for this agent',
                    'debug' => $debug,
                ], 404);
            }

            $plan = $subscription->plan;

            return response()->json([
                'status' => 'success',
                'message' => 'Current plan retrieved successfully',
                'data' => [
                    'subscription' => [
                        'id' => $subscription->id,
                        'started_at' => $subscription->started_at,
                        'expires_at' => $subscription->expires_at,
                        'total_logins' => $subscription->total_logins,
                        'total_earnings' => $subscription->total_earnings,
                        'current_earning_rate' => $subscription->getCurrentEarningRate(),
                        'status' => $subscription->status
                    ],
                    'plan' => $plan ? [
                        'id' => $plan->id,
                        'name' => $plan->name,
                        'description' => $plan->description,
                        'price' => $plan->price,
                        'duration' => $plan->duration,
                        'earning_rates' => [
                            '1-50_logins' => $plan->rate_1_50,
                            '51-100_logins' => $plan->rate_51_100,
                            'after_100_logins' => $plan->rate_after_100
                        ],
                        'bonuses' => [
                            '10_logins' => $plan->bonus_10_logins,
                            '50_logins' => $plan->bonus_50_logins,
                            '100_logins' => $plan->bonus_100_logins
                        ],
                        'withdrawal_settings' => [
                            'min_withdrawal' => $plan->min_withdrawal,
                            'max_withdrawal' => $plan->max_withdrawal,
                            'withdrawal_time' => $plan->withdrawal_time
                        ]
                    ] : null
                ],
                'debug' => $debug,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve current plan',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get agent's detailed information including current plan
     */
    public function agentDetails()
    {
        try {
            $agent = auth('agent')->user();
            $subscription = $agent->activePlanSubscription()->latest('started_at')->first();
            $plan = $subscription ? $subscription->plan : null;

            $response = [
                'agent' => [
                    'id' => $agent->id,
                    'name' => $agent->name,
                    'phone_number' => $agent->phone_number,
                    'email' => $agent->email,
                    'referral_code' => $agent->referral_code,
                    'wallet_balance' => $agent->wallet_balance,
                    'total_earnings' => $agent->total_earnings,
                    'total_withdrawals' => $agent->total_withdrawals,
                    'profile_completed' => $agent->profile_completed,
                    'status' => $agent->status,
                    'last_login_at' => $agent->last_login_at
                ],
                'current_plan' => null
            ];

            if ($subscription && $plan) {
                $response['current_plan'] = [
                    'subscription_id' => $subscription->id,
                    'plan_id' => $plan->id,
                    'plan_name' => $plan->name,
                    'plan_description' => $plan->description,
                    'plan_icon' => $plan->icon,
                    'amount_paid' => $subscription->amount_paid,
                    'started_at' => $subscription->started_at,
                    'expires_at' => $subscription->expires_at,
                    'total_logins' => $subscription->total_logins,
                    'total_earnings' => $subscription->total_earnings,
                    'current_earning_rate' => $subscription->getCurrentEarningRate(),
                    'status' => $subscription->status,
                    'earning_rates' => [
                        '1-50_logins' => $plan->rate_1_50,
                        '51-100_logins' => $plan->rate_51_100,
                        'after_100_logins' => $plan->rate_after_100
                    ],
                    'bonuses' => [
                        '10_logins' => $plan->bonus_10_logins,
                        '50_logins' => $plan->bonus_50_logins,
                        '100_logins' => $plan->bonus_100_logins
                    ],
                    'withdrawal_settings' => [
                        'min_withdrawal' => $plan->min_withdrawal,
                        'max_withdrawal' => $plan->max_withdrawal,
                        'withdrawal_time' => $plan->withdrawal_time
                    ]
                ];
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Agent details retrieved successfully',
                'data' => $response
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve agent details',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get all plans purchased by the agent
     */
    public function planHistory()
    {
        try {
            $agent = auth('agent')->user();
            $subscriptions = $agent->planSubscriptions()->with('plan')->orderByDesc('started_at')->get();

            $data = $subscriptions->map(function ($subscription) {
                return [
                    'subscription_id' => $subscription->id,
                    'plan_id' => $subscription->plan_id,
                    'plan_name' => $subscription->plan ? $subscription->plan->name : null,
                    'plan_description' => $subscription->plan ? $subscription->plan->description : null,
                    'plan_icon' => $subscription->plan ? $subscription->plan->icon : null,
                    'amount_paid' => $subscription->amount_paid,
                    'payment_method' => $subscription->payment_method,
                    'transaction_id' => $subscription->transaction_id,
                    'started_at' => $subscription->started_at,
                    'expires_at' => $subscription->expires_at,
                    'total_logins' => $subscription->total_logins,
                    'total_earnings' => $subscription->total_earnings,
                    'current_earning_rate' => $subscription->getCurrentEarningRate(),
                    'status' => $subscription->status
                ];
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Plan history retrieved successfully',
                'data' => [
                    'plans' => $data
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve plan history',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Debug: Get all plan subscriptions for the authenticated agent
     */
    public function debugAllPlans()
    {
        $agent = auth('agent')->user();
        $subs = $agent->planSubscriptions()->with('plan')->get();
        return response()->json([
            'agent_id' => $agent->id,
            'subscriptions' => $subs
        ]);
    }

    /**
     * Get plan details by agent ID
     * POST /api/v1/agent/plans/by-agent-id
     * Body: { agent_id: int }
     */
    public function getPlanByAgentId(Request $request)
    {
        $agentId = $request->input('agent_id');
        if (!$agentId) {
            return response()->json(['status' => 'error', 'message' => 'Agent ID is required.'], 400);
        }
        $agent = \App\Models\Agent::find($agentId);
        if (!$agent) {
            return response()->json(['status' => 'error', 'message' => 'Agent not found.'], 404);
        }
        // 1. Check for active subscription
        $subscription = $agent->activePlanSubscription()->latest('started_at')->first();
        if ($subscription && $subscription->plan) {
            $plan = $subscription->plan;
            return response()->json([
                'status' => 'success',
                'agent_id' => $agent->id,
                'plan' => [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'price' => $plan->price,
                    'duration' => $plan->duration,
                    'icon' => $plan->icon,
                    'description' => $plan->description,
                    'started_at' => $subscription->started_at,
                    'expires_at' => $subscription->expires_at,
                    'status' => 'active',
                ],
            ]);
        }
        // 2. Check for pending purchase request
        $pendingRequest = \App\Models\AgentPlanPurchaseRequest::where('agent_id', $agent->id)
            ->where('status', 'pending')
            ->latest('requested_at')
            ->first();
        if ($pendingRequest && $pendingRequest->plan) {
            $plan = $pendingRequest->plan;
            return response()->json([
                'status' => 'success',
                'agent_id' => $agent->id,
                'plan' => [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'price' => $plan->price,
                    'duration' => $plan->duration,
                    'icon' => $plan->icon,
                    'description' => $plan->description,
                    'status' => 'pending_approval',
                ],
            ]);
        }
        // 3. Check for rejected request
        $rejectedRequest = \App\Models\AgentPlanPurchaseRequest::where('agent_id', $agent->id)
            ->where('status', 'rejected')
            ->latest('rejected_at')
            ->first();
        if ($rejectedRequest && $rejectedRequest->plan) {
            $plan = $rejectedRequest->plan;
            return response()->json([
                'status' => 'success',
                'agent_id' => $agent->id,
                'plan' => [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'price' => $plan->price,
                    'duration' => $plan->duration,
                    'icon' => $plan->icon,
                    'description' => $plan->description,
                    'status' => 'rejected',
                ],
            ]);
        }
        // 4. No plan
        return response()->json([
            'status' => 'success',
            'agent_id' => $agent->id,
            'plan' => null,
            'plan_status' => 'none',
        ]);
    }
}
