<?php

namespace App\Http\Controllers\Api\Agent;

use App\Http\Controllers\Controller;
use App\Http\Resources\AgentJoiningFeeResource;
use App\Models\AgentJoiningFee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class JoiningFeeController extends Controller
{
    // List all joining fees
    public function index(): JsonResponse
    {
        $fees = AgentJoiningFee::orderBy('sort_order')->get();
        return response()->json([
            'status' => 'success',
            'data' => AgentJoiningFeeResource::collection($fees)
        ]);
    }

    // Store new joining fee
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'validity' => 'required|string',
            'is_active' => 'boolean',
            'sort_order' => 'integer'
        ]);

        $fee = AgentJoiningFee::create($validated);
        return response()->json([
            'status' => 'success',
            'data' => new AgentJoiningFeeResource($fee)
        ], 201);
    }

    // Show specific joining fee
    public function show(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|exists:agent_joining_fees,id'
        ]);
    
        $joiningFee = AgentJoiningFee::findOrFail($validated['id']);
    
        return response()->json([
            'status' => 'success',
            'data' => new AgentJoiningFeeResource($joiningFee)
        ]);
    }

    // Update joining fee
    public function update(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|exists:agent_joining_fees,id',
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'sometimes|numeric|min:0',
            'validity' => ['sometimes', 'string', function ($attribute, $value, $fail) {
                if ($value !== 'lifetime' && !is_numeric($value)) {
                    $fail('The '.$attribute.' must be either "lifetime" or a number.');
                }
            }],
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'sometimes|integer'
        ]);
    
        $joiningFee = AgentJoiningFee::findOrFail($validated['id']);
        unset($validated['id']); // Remove id from the data to be updated
        
        $joiningFee->update($validated);
    
        return response()->json([
            'status' => 'success',
            'message' => 'Joining fee updated successfully',
            'data' => new AgentJoiningFeeResource($joiningFee)
        ]);
    }
    // Delete joining fee
    public function destroy(Request $request)
{
    $validated = $request->validate([
        'id' => 'required|exists:agent_joining_fees,id'
    ]);

    $joiningFee = AgentJoiningFee::findOrFail($validated['id']);

    // Check if any agents are using this fee
    if ($joiningFee->agents()->exists()) {
        return response()->json([
            'status' => 'error',
            'message' => 'Cannot delete: Fee is in use by one or more agents'
        ], 400);
    }

    $joiningFee->delete();

    return response()->json([
        'status' => 'success',
        'message' => 'Joining fee deleted successfully'
    ]);
}

    // Get active fees for dropdown
    public function activeFees(): JsonResponse
    {
        $fees = AgentJoiningFee::active()
            ->orderBy('sort_order')
            ->get(['id', 'name', 'amount', 'validity']);

        return response()->json(['status' => 'success', 'data' => $fees]);
    }

    /**
     * Update joining fee status after successful payment
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function updateStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'agent_id' => 'required|exists:agents,id',
            'payment_id' => 'required|string',
            'order_id' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string|in:razorpay,stripe,other',
        ]);

        try {
            DB::beginTransaction();

            // Find the agent
            $agent = \App\Models\Agent::findOrFail($validated['agent_id']);
            
            // Find the joining fee plan that matches the amount
            $joiningFee = AgentJoiningFee::where('amount', $validated['amount'])->first();
            
            if (!$joiningFee) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No joining fee plan found for the specified amount.'
                ], 404);
            }

            // Update agent's joining fee status
            $agent->update([
                'joining_fee_paid' => true,
                'joining_fee_paid_at' => now(),
                'joining_fee_plan_id' => $joiningFee->id,
                'joining_fee_amount' => $validated['amount'],
            ]);

            // Create a payment record
            $payment = new \App\Models\Payment([
                'agent_id' => $agent->id,
                'payment_id' => $validated['payment_id'],
                'order_id' => $validated['order_id'],
                'amount' => $validated['amount'],
                'currency' => 'INR',
                'payment_method' => $validated['payment_method'],
                'status' => 'completed',
                'purpose' => 'agent_joining_fee',
                'meta' => [
                    'plan_id' => $joiningFee->id,
                    'plan_name' => $joiningFee->name,
                    'validity' => $joiningFee->validity,
                ]
            ]);
            $payment->save();

            DB::commit();

            // Get the updated agent with relationships
            $agent->refresh();

            return response()->json([
                'status' => 'success',
                'message' => 'Joining fee status updated successfully',
                'data' => [
                    'agent_id' => $agent->id,
                    'joining_fee_paid' => (bool) $agent->joining_fee_paid,
                    'joining_fee_paid_at' => $agent->joining_fee_paid_at,
                    'joining_fee_plan' => new AgentJoiningFeeResource($joiningFee),
                    'payment_reference' => $payment->payment_id
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to update joining fee status: ' . $e->getMessage(), [
                'agent_id' => $validated['agent_id'] ?? null,
                'payment_id' => $validated['payment_id'] ?? null,
                'order_id' => $validated['order_id'] ?? null,
                'error' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update joining fee status: ' . $e->getMessage()
            ], 500);
        }
    }
}