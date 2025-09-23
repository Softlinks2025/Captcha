<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SubscriptionPlanController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    private function checkAdmin()
    {
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'You do not have permission to access this page.');
        }
    }

    public function index()
    {
        $this->checkAdmin();
        $plans = SubscriptionPlan::paginate(10);
        return view('admin.subscription_plans.index', compact('plans'));
    }

    /**
     * Show the form for creating a new subscription plan.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.subscription_plans.create');
    }

    public function edit(SubscriptionPlan $subscription_plan)
    {
        // Initialize formattedEarnings as an empty array
        $formattedEarnings = [];
        
        // If there are earnings, format them for the view
        if (!empty($subscription_plan->earnings)) {
            $earnings = is_string($subscription_plan->earnings) 
                ? json_decode($subscription_plan->earnings, true) 
                : $subscription_plan->earnings;
                
            if (is_array($earnings)) {
                $formattedEarnings = array_map(function($amount, $level) {
                    return [
                        'level' => $level,
                        'amount' => $amount
                    ];
                }, $earnings, array_keys($earnings));
            }
        }
        
        return view('admin.subscription_plans.edit', compact('subscription_plan', 'formattedEarnings'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'captcha_per_day' => ['required', 'regex:/^\\d+$|^unlimited$/i'],
            'min_withdrawal_limit' => 'nullable|integer',
            'cost' => 'required|numeric',
            'duration' => 'required|string',
            'plan_type' => 'required|string',
            'earning_type' => 'required|string',
            'is_unlimited' => 'boolean',
            'caption_limit' => ['nullable', 'regex:/^\\d+$|^unlimited$/i'],
            'min_daily_earning' => 'nullable|numeric',
            'referral_earning_per_ref' => 'nullable|numeric',
            'daily_captcha_earning_with_ref' => 'nullable|numeric',
            'bonus_10_referrals' => 'nullable|numeric',
            'gift_10_referrals' => 'nullable|string',
            'bonus_20_referrals' => 'nullable|numeric',
            'gift_20_referrals' => 'nullable|string',
            'daily_limit_bonus' => 'nullable|numeric',
            'unlimited_earning_rate' => 'nullable|numeric',
            'icon' => 'nullable|file|image|max:2048',
            'captchas_per_level' => 'nullable|numeric',
        ]);

        if ($request->hasFile('icon')) {
            $iconPath = $request->file('icon')->store('subscription_plan_icons', 'public');
            $validated['icon'] = $iconPath;
        }

       // Validate and store earnings JSON string
$earningsJson = $request->input('earnings');
$earningsArr = json_decode($earningsJson, true);
if ($earningsJson && is_null($earningsArr)) {
    return back()->withErrors(['earnings' => 'Earnings must be valid JSON.'])->withInput();
}
if (is_array($earningsArr)) {
    foreach ($earningsArr as $key => $val) {
        if (!preg_match('/^(\\d+|after_\\d+)$/', $key)) {
            return back()->withErrors(['earnings' => 'Earnings keys must be level numbers (e.g., "1") or "after_X".'])->withInput();
        }
        if (!is_numeric($val)) {
            return back()->withErrors(['earnings' => 'Earnings values must be numeric.'])->withInput();
        }
    }
}
$validated['earnings'] = $earningsJson;

        $subscription_plan = SubscriptionPlan::create($validated);

        return redirect()->route('admin.subscription_plans.index')
            ->with('success', 'Subscription plan created successfully!');
    }

    public function update(Request $request, SubscriptionPlan $subscription_plan)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'captcha_per_day' => ['required', 'regex:/^\\d+$|^unlimited$/i'],
            'min_withdrawal_limit' => 'nullable|integer',
            'cost' => 'required|numeric',
            'duration' => 'required|string',
            'plan_type' => 'required|string',
            'earning_type' => 'required|string',
            'is_unlimited' => 'boolean',
            'icon' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'caption_limit' => ['nullable', 'regex:/^\\d+$|^unlimited$/i'],
            'min_daily_earning' => 'nullable|numeric',
            'referral_earning_per_ref' => 'nullable|numeric',
            'daily_captcha_earning_with_ref' => 'nullable|numeric',
            'bonus_10_referrals' => 'nullable|numeric',
            'gift_10_referrals' => 'nullable|string',
            'bonus_20_referrals' => 'nullable|numeric',
            'gift_20_referrals' => 'nullable|string',
            'daily_limit_bonus' => 'nullable|numeric',
            'unlimited_earning_rate' => 'nullable|numeric',
            'captchas_per_level' => 'nullable|numeric',
            
            
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('plan-images', 'public');
            $validated['image'] = $imagePath;
        }

        // Validate and store earnings JSON string
$earningsJson = $request->input('earnings');
$earningsArr = json_decode($earningsJson, true);
if ($earningsJson && is_null($earningsArr)) {
    return back()->withErrors(['earnings' => 'Earnings must be valid JSON.'])->withInput();
}
if (is_array($earningsArr)) {
    foreach ($earningsArr as $key => $val) {
        if (!preg_match('/^(\\d+|after_\\d+)$/', $key)) {
            return back()->withErrors(['earnings' => 'Earnings keys must be level numbers (e.g., "1") or "after_X".'])->withInput();
        }
        if (!is_numeric($val)) {
            return back()->withErrors(['earnings' => 'Earnings values must be numeric.'])->withInput();
        }
    }
}
$validated['earnings'] = $earningsJson;

        $subscription_plan->update($validated);
        return redirect()->route('admin.subscription_plans.index')->with('success', 'Plan updated successfully.');
    }

    public function destroy(SubscriptionPlan $subscription_plan)
    {
        $subscription_plan->delete();
        return redirect()->route('admin.subscription_plans.index')->with('success', 'Plan deleted.');
    }

    /**
     * Display the specified subscription plan.
     */
    public function show($id)
    {
        $subscription_plan = SubscriptionPlan::findOrFail($id);
        return view('admin.subscription_plans.show', compact('subscription_plan'));
    }
}