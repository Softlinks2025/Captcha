{{ ... }}
    public function store(Request $request)
    {
        // Debug: Log the incoming request data
        \Log::info('Store Request Data:', $request->all());

        try {
            // Manually validate the request
            $validator = \Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:subscription_plans',
                'description' => 'nullable|string',
                'price' => 'required|numeric|min:0',
                'duration_days' => 'required|integer|min:1',
                'captcha_per_day' => 'required|string',
                'caption_limit' => 'nullable|string',
                'earnings' => 'required|json',
                'status' => 'required|in:active,inactive',
                'captchas_per_level' => 'required|integer|min:1',
                'referral_earnings' => 'nullable|json',
                'referral_earnings_type' => 'required|in:fixed,percentage',
                'referral_earnings_value' => 'required|numeric|min:0',
                'earning_ranges' => 'required|array|min:1',
                'earning_ranges.*.level' => 'required|integer|min:1',
                'earning_ranges.*.min' => 'required|integer|min:1',
                'earning_ranges.*.max' => 'nullable|integer|min:0',
                'earning_ranges.*.rate' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                \Log::error('Validation failed:', $validator->errors()->toArray());
                return back()->withErrors($validator)->withInput();
            }

            $validated = $validator->validated();

            // Map price to cost as the database expects
            if (isset($validated['price'])) {
                $validated['cost'] = $validated['price'];
                unset($validated['price']);
            }

        // Ensure captchas_per_level is set
        $captchasPerLevel = (int)$request->input('captchas_per_level', 1);
        $validated['captchas_per_level'] = $captchasPerLevel;

            // Log the earnings data for debugging
            \Log::info('Earnings JSON from request:', [
                'raw_earnings' => $request->input('earnings'),
                'decoded_earnings' => json_decode($request->input('earnings'), true)
            ]);

        // Validate that earnings ranges are multiples of captchas_per_level
        $earnings = [];
        
        foreach ($request->earning_ranges as $range) {
            $min = (int)$range['min'];
            $max = $range['max'] ? (int)$range['max'] : null;
            
            // Check if min and max are multiples of captchas_per_level
            if ($min % $captchasPerLevel !== 1) {
                return back()->withErrors([
                    'earning_ranges' => "The starting captcha count ({$min}) must be 1 more than a multiple of captchas_per_level ({$captchasPerLevel})."
                ])->withInput();
            }
            
            if ($max !== null && $max % $captchasPerLevel !== 0) {
                return back()->withErrors([
                    'earning_ranges' => "The ending captcha count ({$max}) must be a multiple of captchas_per_level ({$captchasPerLevel})."
                ])->withInput();
            }
            
            $key = $max ? "{$min}-{$max}" : "{$min}+";
            $earnings[$key] = (float)$range['rate'];
        }

        // Update the earnings in validated data
        $validated['earnings'] = json_encode($earnings);

            // Debug: Log the final data being saved
            \Log::info('Final Data Being Saved (Store):', [
                'captchas_per_level' => $validated['captchas_per_level'],
                'earnings' => $validated['earnings'],
                'all_data' => $validated
            ]);

            // Create the plan with the validated data
            try {
                $plan = SubscriptionPlan::create($validated);
                \Log::info('Plan Created Successfully:', [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'captchas_per_level' => $plan->captchas_per_level,
                    'earnings' => $plan->earnings,
                    'created_at' => $plan->created_at
                ]);

                return redirect()->route('admin.subscription_plans.index')
                    ->with('success', 'Subscription plan created successfully.');
            } catch (\Exception $e) {
                \Log::error('Failed to create subscription plan:', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return back()->withErrors(['error' => 'Failed to create subscription plan: ' . $e->getMessage()])->withInput();
            }
        } catch (\Exception $e) {
            \Log::error('Unexpected error in store method:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['error' => 'An unexpected error occurred: ' . $e->getMessage()])->withInput();
        }
    }

    public function update(Request $request, SubscriptionPlan $subscriptionPlan)
    {
        // Debug: Log the incoming request data
        \Log::info('Update Request Data:', $request->all());
        \Log::info('Current Plan Data:', $subscriptionPlan->toArray());

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:subscription_plans,name,' . $subscriptionPlan->id,
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'captcha_per_day' => 'required|string',
            'caption_limit' => 'nullable|string',
            'earnings' => 'required|json',
            'status' => 'required|in:active,inactive',
            'captchas_per_level' => 'required|integer|min:1',
            'referral_earnings' => 'nullable|json',
            'referral_earnings_type' => 'required|in:fixed,percentage',
            'referral_earnings_value' => 'required|numeric|min:0',
            'earning_ranges' => 'required|array|min:1',
            'earning_ranges.*.level' => 'required|integer|min:1',
            'earning_ranges.*.min' => 'required|integer|min:1',
            'earning_ranges.*.max' => 'nullable|integer|min:0',
            'earning_ranges.*.rate' => 'required|numeric|min:0',
        ]);

        // Debug: Log the validated data
        \Log::info('Validated Data:', $validated);

        // Ensure captchas_per_level is set
        $captchasPerLevel = (int)$request->input('captchas_per_level', 1);
        $validated['captchas_per_level'] = $captchasPerLevel;

        // Debug: Log the captchas_per_level value
        \Log::info('Captchas Per Level:', [
            'input_value' => $request->input('captchas_per_level'),
            'validated_value' => $validated['captchas_per_level']
        ]);

        // Validate that earnings ranges are multiples of captchas_per_level
        $earnings = [];
        
        foreach ($request->earning_ranges as $range) {
            $min = (int)$range['min'];
            $max = $range['max'] ? (int)$range['max'] : null;
            
            // Check if min and max are multiples of captchas_per_level
            if ($min % $captchasPerLevel !== 1) {
                return back()->withErrors([
                    'earning_ranges' => "The starting captcha count ({$min}) must be 1 more than a multiple of captchas_per_level ({$captchasPerLevel})."
                ])->withInput();
            }
            
            if ($max !== null && $max % $captchasPerLevel !== 0) {
                return back()->withErrors([
                    'earning_ranges' => "The ending captcha count ({$max}) must be a multiple of captchas_per_level ({$captchasPerLevel})."
                ])->withInput();
            }
            
            $key = $max ? "{$min}-{$max}" : "{$min}+";
            $earnings[$key] = (float)$range['rate'];
        }

        // Update the earnings in validated data
        $validated['earnings'] = json_encode($earnings);

        // Debug: Log the final data being saved
        \Log::info('Final Data Being Saved:', [
            'captchas_per_level' => $validated['captchas_per_level'],
            'earnings' => $validated['earnings']
        ]);

        // Update the plan with the validated data
        $subscriptionPlan->update($validated);

        // Debug: Log the updated plan data
        $updatedPlan = $subscriptionPlan->fresh();
        \Log::info('Plan Updated Successfully:', [
            'id' => $updatedPlan->id,
            'name' => $updatedPlan->name,
            'captchas_per_level' => $updatedPlan->captchas_per_level,
            'updated_at' => $updatedPlan->updated_at
        ]);

        return redirect()->route('admin.subscription-plans.index')
            ->with('success', 'Subscription plan updated successfully');
    }
{{ ... }}
