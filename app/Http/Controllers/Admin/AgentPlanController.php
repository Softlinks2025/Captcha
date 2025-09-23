<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgentCommissionTier;
use App\Models\AgentJoiningFee;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AgentPlanController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * Display a listing of all subscription plans with links to manage their commission tiers.
     */
    public function index()
    {
        $plans = SubscriptionPlan::with('agentCommissionTiers')
            ->orderBy('name')
            ->get();
            
            
        $joiningFees = \App\Models\AgentJoiningFee::orderBy('sort_order')->get();

        return view('admin.agent-plans.index', compact('plans', 'joiningFees'));
    }

    /**
     * Show the form for editing commission tiers for a subscription plan.
     */
    public function edit(SubscriptionPlan $plan)
    {
        $tiers = $plan->agentCommissionTiers->sortBy('min_referrals');
        return view('admin.agent-plans.edit', compact('plan', 'tiers'));
    }

    /**
     * Update the commission tiers for a subscription plan.
     */
    public function update(Request $request, SubscriptionPlan $plan)
    {
        \Log::info('Update request received', [
            'plan_id' => $plan->id,
            'input' => $request->all()
        ]);

        // Custom validation for non-overlapping ranges
        $validator = Validator::make($request->all(), [
            'tiers' => 'required|array|min:1',
            'tiers.*.min_referrals' => [
                'required',
                'integer',
                'min:0',
                function ($attribute, $value, $fail) use ($request, $plan) {
                    $index = explode('.', $attribute)[1];
                    $tierId = $request->input("tiers.{$index}.id");
                    $maxReferrals = $request->input("tiers.{$index}.max_referrals");
                    
                    // Convert empty string to null for max_referrals
                    if ($maxReferrals === '') {
                        $maxReferrals = null;
                    }
                    
                    // Validate that min_referrals is less than max_referrals if max is set
                    if ($maxReferrals !== null && $value >= $maxReferrals) {
                        return $fail('Min referrals must be less than max referrals.');
                    }
                    
                    // Get all other tiers (excluding the current one being updated)
                    $otherTiers = $plan->agentCommissionTiers()
                        ->where('id', '!=', $tierId)
                        ->get();
                    
                    // Check for overlapping ranges with other tiers
                    foreach ($otherTiers as $existingTier) {
                        $existingMin = $existingTier->min_referrals;
                        $existingMax = $existingTier->max_referrals;
                        
                        // Check if the new range overlaps with existing range
                        $overlaps = false;
                        
                        // Case 1: New range starts within existing range
                        if ($value >= $existingMin && 
                            ($existingMax === null || $value <= $existingMax)) {
                            $overlaps = true;
                        }
                        
                        // Case 2: New range ends within existing range
                        if ($maxReferrals !== null && 
                            $maxReferrals >= $existingMin && 
                            ($existingMax === null || $maxReferrals <= $existingMax)) {
                            $overlaps = true;
                        }
                        
                        // Case 3: New range completely encompasses existing range
                        if ($value <= $existingMin && 
                            ($maxReferrals === null || 
                            ($existingMax !== null && $maxReferrals >= $existingMax))) {
                            $overlaps = true;
                        }
                        
                        if ($overlaps) {
                            return $fail("The referral range overlaps with an existing tier (ID: {$existingTier->id}).");
                        }
                    }
                },
            ],
            'tiers.*.max_referrals' => [
                'nullable',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) {
                    if ($value !== null && $value <= 0) {
                        $fail('Max referrals must be greater than 0 or left empty for no maximum.');
                    }
                },
            ],
            'tiers.*.commission_amount' => [
                'required',
                'numeric',
                'min:0',
               /* function ($attribute, $value, $fail) use ($plan) {
                    if ($value > $plan->cost * 0.5) {
                        $fail('Commission amount cannot exceed 50% of the plan price.');
                    }
                },*/
            ]
        ]);

        if ($validator->fails()) {
            \Log::warning('Validation failed', [
                'errors' => $validator->errors()->toArray(),
                'input' => $request->all()
            ]);
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // Process the tiers data
        $tierIds = [];
        
        foreach ($request->input('tiers') as $tierData) {
            // Convert empty strings to null for max_referrals
            $tierData['max_referrals'] = !empty($tierData['max_referrals']) ? (int)$tierData['max_referrals'] : null;
            $tierData['min_referrals'] = (int)$tierData['min_referrals'];
            $tierData['commission_amount'] = (float)$tierData['commission_amount'];
            
            try {
                // Update or create the tier
                if (!empty($tierData['id'])) {
                    $tier = $plan->agentCommissionTiers()->find($tierData['id']);
                    if ($tier) {
                        $tier->update([
                            'min_referrals' => $tierData['min_referrals'],
                            'max_referrals' => $tierData['max_referrals'],
                            'commission_amount' => $tierData['commission_amount']
                        ]);
                        $tierIds[] = $tier->id;
                        \Log::info('Tier updated', ['tier_id' => $tier->id, 'data' => $tierData]);
                    }
                } else {
                    $tier = $plan->agentCommissionTiers()->create([
                        'min_referrals' => $tierData['min_referrals'],
                        'max_referrals' => $tierData['max_referrals'],
                        'commission_amount' => $tierData['commission_amount']
                    ]);
                    $tierIds[] = $tier->id;
                    \Log::info('Tier created', ['tier_id' => $tier->id, 'data' => $tierData]);
                }
            } catch (\Exception $e) {
                \Log::error('Error processing tier', [
                    'error' => $e->getMessage(),
                    'tier_data' => $tierData,
                    'trace' => $e->getTraceAsString()
                ]);
                continue;
            }
        }
        
        // Delete any tiers that weren't included in the update
        if (!empty($tierIds)) {
            try {
                $deleted = $plan->agentCommissionTiers()
                    ->whereNotIn('id', $tierIds)
                    ->delete();
                \Log::info('Deleted old tiers', ['count' => $deleted]);
            } catch (\Exception $e) {
                \Log::error('Error deleting old tiers', [
                    'error' => $e->getMessage(),
                    'tier_ids' => $tierIds,
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        return redirect()->route('admin.agent-plans.index')
            ->with('success', 'Commission tiers updated successfully.');

    }
    // In AgentPlanController.php

// Store a new joining fee
public function storeJoiningFee(Request $request)
{
    // Prevent creating new joining fees
    return redirect()->back()->with('error', 'Creating new joining fees is not allowed. Please edit existing fees instead.');
}

    // ------------------- UPDATE JOINING FEE ------------------- //
    public function updateJoiningFee(Request $request, $id)
{
    \Log::info('Updating joining fee', ['id' => $id, 'request' => $request->all()]);
    
    $feeId = $request->input('id');
    $fee = AgentJoiningFee::find($feeId);
    
    if (!$fee) {
        \Log::error('Joining fee not found', ['id' => $id]);
        return response()->json([
            'success' => false,
            'message' => 'Joining fee not found'
        ], 404);
    }
   
    $request->validate([
        'amount' => 'required|numeric|min:1',
    ]);

    $fee = AgentJoiningFee::findOrFail($feeId);                                
    $fee->update([
        'amount' => $request->amount,
        'name' => $request->name ?? $fee->name,
        'validity' => $request->validity ?? $fee->validity,
        'description' => $request->description ?? $fee->description,
        'is_active' => $request->has('is_active'),
    ]);

    // If AJAX, return JSON
    if ($request->ajax()) {
        return response()->json([
            'success' => true,
            'message' => 'Joining fee updated successfully!',
        ]);
    }

    // Fallback: normal redirect
    return redirect()->back()->with('success', 'Joining fee updated successfully!');
}

// Delete a joining fee
public function destroyJoiningFee($id)
{
    $fee = AgentJoiningFee::findOrFail($id);
    
    if ($fee->agents()->exists()) {
        return response()->json([
            'success' => false,
            'message' => 'Cannot delete: This fee is in use by one or more agents'
        ], 400);
    }

    $fee->delete();
    
    return response()->json([
        'success' => true,
        'message' => 'Joining fee deleted successfully'
    ]);
}
}