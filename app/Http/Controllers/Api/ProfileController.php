<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompleteProfileRequest;
use App\Models\User;
use App\Models\UserReferral;
use App\Models\WalletTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Complete user profile
     * 
     * @param CompleteProfileRequest $request
     * @return JsonResponse
     */
    public function completeProfile(CompleteProfileRequest $request): JsonResponse
    {
        \Log::info('Profile complete request', [
            'all' => $request->all(),
            'files' => $request->files->all(),
            'headers' => $request->headers->all(),
            'content_type' => $request->header('content-type'),
        ]);

        try {
            return DB::transaction(function () use ($request) {
                $user = Auth::user();
                
                if (!$user) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'User not authenticated.'
                    ], 401);
                }
                
                if ($user->profile_completed) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Profile is already completed. You can only update your profile.'
                    ], 403);
                }
                
                $data = $request->validated();
                
                // Handle profile photo upload if present (file or URL)
                if ($request->hasFile('profile_photo')) {
                    try {
                        if ($user->profile_photo_path) {
                            Storage::disk('public')->delete($user->profile_photo_path);
                        }
                        $path = $request->file('profile_photo')->store('profile-photos', 'public');
                        $data['profile_photo_path'] = $path;
                        \Log::info('Profile photo uploaded', [
                            'user_id' => $user->id,
                            'profile_photo_path' => $path
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('Profile photo upload failed', [
                            'user_id' => $user->id,
                            'error' => $e->getMessage()
                        ]);
                        throw new \RuntimeException('Failed to upload profile photo. Please try again.');
                    }
                } else if ($request->filled('profile_photo') && filter_var($request->input('profile_photo'), FILTER_VALIDATE_URL)) {
                    try {
                        if ($user->profile_photo_path) {
                            Storage::disk('public')->delete($user->profile_photo_path);
                        }
                        $url = $request->input('profile_photo');
                        $imageContents = @file_get_contents($url);
                        if ($imageContents === false) {
                            throw new \RuntimeException('Failed to download image from URL.');
                        }
                        $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
                        $filename = 'profile-photos/' . uniqid('user_' . $user->id . '_') . '.' . $extension;
                        Storage::disk('public')->put($filename, $imageContents);
                        $data['profile_photo_path'] = $filename;
                        \Log::info('Profile photo downloaded from URL', [
                            'user_id' => $user->id,
                            'profile_photo_path' => $filename,
                            'source_url' => $url
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('Profile photo download failed', [
                            'user_id' => $user->id,
                            'error' => $e->getMessage(),
                            'source_url' => $request->input('profile_photo')
                        ]);
                        throw new \RuntimeException('Failed to download profile photo from URL. Please try again.');
                    }
                }
                
                // Handle user referral code if provided (takes precedence over agent referral)
                if ($request->has('user_referral_code') && $request->input('user_referral_code')) {
                    $data['user_referral_code'] = strtoupper($request->input('user_referral_code'));
                    
                    // Find the user who owns this referral code
                    $referrer = User::where('referral_code', $data['user_referral_code'])
                        ->where('id', '!=', $user->id) // Prevent self-referral
                        ->first();
                        
                    if ($referrer) {
                        // Check if this user has already used a referral code
                        if (!$user->user_referral_code) {
                            try {
                                // Start a database transaction
                                \DB::beginTransaction();
                                
                                // Set the referred_by field on the user
                                $user->referred_by = $referrer->id;
                                $user->save();
                                
                                // Create a referral record
                                $userReferral = UserReferral::create([
                                    'referrer_id' => $referrer->id,
                                    'referred_id' => $user->id,
                                    'referral_code' => $data['user_referral_code'],
                                    'status' => 'pending', // Set to pending until subscription is purchased
                                    'used_at' => now(),
                                    'reward_credited' => false
                                ]);
                                
                                // Get referrer's subscription plan
                                $referrerPlan = $referrer->subscriptionPlan;
                                
                                if ($referrerPlan) {
                                    // Get the referral reward from the plan
                                    $referralReward = strtolower((string)$referrerPlan->referral_reward) === 'unlimited' 
                                        ? $referrerPlan->referral_reward_amount ?? 0
                                        : $referrerPlan->referral_reward;
                                    
                                    // If we have a valid reward amount, credit the referrer's wallet
                                    if ($referralReward > 0) {
                                        $referrer->increment('wallet_balance', $referralReward);
                                        
                                        // Create a transaction record
                                        $referrer->transactions()->create([
                                            'amount' => $referralReward,
                                            'type' => 'referral_earning',
                                            'description' => 'Referral reward for user #' . $user->id,
                                            'reference_id' => $userReferral->id,
                                            'reference_type' => get_class($userReferral)
                                        ]);
                                        
                                        // Log successful reward
                                        \Log::info('Referral reward credited', [
                                            'referrer_id' => $referrer->id,
                                            'referred_id' => $user->id,
                                            'reward_amount' => $referralReward,
                                            'plan' => $referrerPlan->name,
                                            'referral_id' => $userReferral->id
                                        ]);
                                    }
                                } else {
                                    // Log that no reward was given due to missing subscription
                                    \Log::info('No referral reward - referrer does not have an active subscription', [
                                        'referrer_id' => $referrer->id,
                                        'referred_id' => $user->id,
                                        'has_plan' => $referrerPlan ? true : false,
                                        'has_active_subscription' => $referrer->hasSubscription()
                                    ]);
                                }
                                
                                // Update referrer's total referrals count
                                $referrer->increment('total_referrals');
                                
                                // Check and update referral milestones
                                $this->checkReferralMilestones($referrer);
                                
                                // Commit the transaction
                                \DB::commit();
                                
                                // Send notification to referrer
                                $this->sendReferralNotification($referrer, $user, 'user');
                                
                                \Log::info('User referral processed successfully', [
                                    'referrer_id' => $referrer->id,
                                    'referred_id' => $user->id,
                                    'referral_code' => $data['user_referral_code']
                                ]);
                                
                            } catch (\Exception $e) {
                                // Rollback the transaction in case of error
                                \DB::rollBack();
                                \Log::error('Failed to process user referral', [
                                    'referrer_id' => $referrer->id ?? null,
                                    'referred_id' => $user->id,
                                    'referral_code' => $data['user_referral_code'],
                                    'error' => $e->getMessage(),
                                    'trace' => $e->getTraceAsString()
                                ]);
                                // Continue without failing the entire request
                            }
                        } else {
                            \Log::warning('User already used a referral code', [
                                'user_id' => $user->id,
                                'referral_code' => $data['user_referral_code']
                            ]);
                        }
                    }
                }
                
                // Handle agent referral if provided in the request (from CompleteProfileRequest)
                if ($request->has('referred_by_agent_id') && !$user->agent_id) {
                    try {
                        $agent = \App\Models\Agent::find($request->input('referred_by_agent_id'));
                        
                        // Ensure agent exists and is active
                        if ($agent && $agent->status === 'active') {
                            // Update user with agent referral
                            $user->agent_id = $agent->id;
                            
                            // If user already has a subscription, process the commission
                            if ($user->subscription_name) {
                                $plan = \App\Models\SubscriptionPlan::where('name', $user->subscription_name)->first();
                                if ($plan) {
                                    $result = $user->handleAgentRewardForSubscription($plan);
                                    \Log::info('Agent commission processed during profile completion', [
                                        'user_id' => $user->id,
                                        'agent_id' => $agent->id,
                                        'plan_id' => $plan->id,
                                        'result' => $result
                                    ]);
                                }
                            }
                            
                            // Update referral count and check for milestones
                            $agent->updateReferralCount();
                            
                            \Log::info('Agent referral recorded', [
                                'user_id' => $user->id,
                                'agent_id' => $agent->id,
                                'total_referrals' => $agent->total_referrals
                            ]);
                        } else {
                            // Agent not found or not active
                            \Log::info('Agent not found or not active', [
                                'user_id' => $user->id,
                                'agent_id' => $request->input('referred_by_agent_id'),
                                'agent_status' => $agent ? $agent->status : 'not_found'
                            ]);
                        }
                    } catch (\Exception $e) {
                        \Log::error('Error processing agent referral', [
                            'user_id' => $user->id,
                            'agent_id' => $request->input('referred_by_agent_id'),
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                        // Continue without failing the entire request if referral processing fails
                    }
                }
                
                // Mark profile as completed
                $data['profile_completed'] = true;
                // If referred_by_agent_id is present, save the agent_id to the user
                if ($request->has('referred_by_agent_id')) {
                    $data['agent_id'] = $request->input('referred_by_agent_id');
                    \Log::info('User assigned to agent', [
                        'user_id' => $user->id,
                        'agent_id' => $data['agent_id']
                    ]);
                }
                
                // Update user profile
                if (!$user->update($data)) {
                    throw new \RuntimeException('Failed to update user profile.');
                }
                \Log::info('User updated after profile completion', [
                    'user_id' => $user->id,
                    'profile_photo_path' => $user->profile_photo_path
                ]);
                $user->refresh();
                
                // Generate new token with updated user data
                if (!$token = auth('api')->login($user)) {
                    throw new \RuntimeException('Failed to generate authentication token.');
                }
                
                return response()->json([
                    'status' => 'success',
                    'message' => 'Profile completed successfully',
                    'data' => [
                        'access_token' => $token,
                        'token_type' => 'bearer',
                        'expires_in' => auth('api')->factory()->getTTL() * 60,
                        'user' => [
                            'id' => $user->id,
                            'name' => $user->name,
                            'email' => $user->email,
                            'phone' => $user->phone,
                            'is_verified' => (bool)$user->is_verified,
                            'requires_profile_completion' => !$user->isProfileComplete(),
                            'upi_id' => $user->upi_id,
                            'agent_referral_code' => $user->agent_referral_code,
                            'referral_code' => $user->referral_code,
                            'bank_account_number' => $user->bank_account_number,
                            'bank_name' => $user->bank_name,
                            'ifsc_code' => $user->ifsc_code,
                            'account_holder_name' => $user->account_holder_name,
                            'address' => $user->address,
                            'city' => $user->city,
                            'state' => $user->state,
                            'pincode' => $user->pincode,
                            'pan_number' => $user->pan_number,
                            'additional_contact_number' => $user->additional_contact_number,
                        ],
                        'profile_photo_url' => $user->profile_photo_path ? asset('storage/' . $user->profile_photo_path) : null,
                        'profile_completed' => (bool) $user->name,
                        'redirect_to' => $user->name ? '/dashboard' : '/complete-profile'
                    ]
                ]);
            });
        } catch (\Exception $e) {
            \Log::error('Profile completion failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to complete profile. ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Generate a unique referral code
     * 
     * @return string
     */
    protected function generateUniqueReferralCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (User::where('referral_code', $code)->exists());
        
        return $code;
    }
    
    /**
     * Get user profile
     * 
     * @return JsonResponse
     */
    public function getProfile(): JsonResponse
    {
        $user = Auth::user();
        return response()->json([
            'user' => $user->makeVisible(['agent_id', 'agent_referral_code', 'bank_name']),
            'profile_photo_url' => $user->profile_photo_path ? asset('storage/' . $user->profile_photo_path) : null,
            'requires_profile_completion' => !$user->isProfileComplete(),
            'bank_account_number' => $user->bank_account_number,
            'bank_name' => $user->bank_name,
            'ifsc_code' => $user->ifsc_code,
            'account_holder_name' => $user->account_holder_name,
            'address' => $user->address,
            'city' => $user->city,
            'state' => $user->state,
            'pincode' => $user->pincode,
            'pan_number' => $user->pan_number,
            'additional_contact_number' => $user->additional_contact_number,
            ]);
    }

    /**
     * Update user profile (edit profile)
     *
     * @param CompleteProfileRequest $request
     * @return JsonResponse
     *
     * Note: This endpoint requires 'multipart/form-data' if uploading a profile photo.
     */
    public function updateProfile(CompleteProfileRequest $request): JsonResponse
    {
        \Log::info('Profile update request', [
            'all' => $request->all(),
            'files' => $request->files->all(),
            'headers' => $request->headers->all(),
            'content_type' => $request->header('content-type'),
        ]);

        try {
            return DB::transaction(function () use ($request) {
                $user = Auth::user();
                if (!$user) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'User not authenticated.'
                    ], 401);
                }
                $data = $request->validated();
                // Handle profile photo upload if present (file or URL)
                if ($request->hasFile('profile_photo')) {
                    try {
                        if ($user->profile_photo_path) {
                            Storage::disk('public')->delete($user->profile_photo_path);
                        }
                        $path = $request->file('profile_photo')->store('profile-photos', 'public');
                        $data['profile_photo_path'] = $path;
                    } catch (\Exception $e) {
                        \Log::error('Profile photo upload failed', [
                            'user_id' => $user->id,
                            'error' => $e->getMessage()
                        ]);
                        throw new \RuntimeException('Failed to upload profile photo. Please try again.');
                    }
                } else if ($request->filled('profile_photo') && filter_var($request->input('profile_photo'), FILTER_VALIDATE_URL)) {
                    try {
                        if ($user->profile_photo_path) {
                            Storage::disk('public')->delete($user->profile_photo_path);
                        }
                        $url = $request->input('profile_photo');
                        $imageContents = @file_get_contents($url);
                        if ($imageContents === false) {
                            throw new \RuntimeException('Failed to download image from URL.');
                        }
                        $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
                        $filename = 'profile-photos/' . uniqid('user_' . $user->id . '_') . '.' . $extension;
                        Storage::disk('public')->put($filename, $imageContents);
                        $data['profile_photo_path'] = $filename;
                        \Log::info('Profile photo downloaded from URL', [
                            'user_id' => $user->id,
                            'profile_photo_path' => $filename,
                            'source_url' => $url
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('Profile photo download failed', [
                            'user_id' => $user->id,
                            'error' => $e->getMessage(),
                            'source_url' => $request->input('profile_photo')
                        ]);
                        throw new \RuntimeException('Failed to download profile photo from URL. Please try again.');
                    }
                }
                // Handle agent referral code if provided
                if (isset($data['agent_referral_code'])) {
                    try {
                        $agent = \App\Models\Agent::where('referral_code', $data['agent_referral_code'])->first();
                        if ($agent && $agent->status === 'active') {
                            if (!$user->agent_id) {
                                $data['agent_id'] = $agent->id;
                            }
                        } else {
                            \Log::warning('Invalid or inactive agent referral code used in update', [
                                'user_id' => $user->id,
                                'agent_referral_code' => $data['agent_referral_code']
                            ]);
                        }
                    } catch (\Exception $e) {
                        \Log::error('Agent referral processing failed in update', [
                            'user_id' => $user->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
                // Do not change profile_completed flag here
                if (!$user->update($data)) {
                    throw new \RuntimeException('Failed to update user profile.');
                }
                $user->refresh();
                // Always include both the relative path and the full URL in the response
                return response()->json([
                    'status' => 'success',
                    'message' => 'Profile updated successfully',
                    'user' => $user->makeHidden(['otp', 'otp_expires_at'])->makeVisible(['agent_id', 'agent_referral_code']),
                    // 'profile_photo_path' is the relative path, use with your own base URL if needed
                    'profile_photo_path' => $user->profile_photo_path,
                    // 'profile_photo_url' is the full URL to access the image directly
                    'profile_photo_url' => $user->profile_photo_path ? asset('storage/' . $user->profile_photo_path) : null,
                    'bank_account_number' => $user->bank_account_number,
                    'bank_name' => $user->bank_name,
                    'ifsc_code' => $user->ifsc_code,
                    'account_holder_name' => $user->account_holder_name,
                    'address' => $user->address,
                    'city' => $user->city,
                    'state' => $user->state,
                    'pincode' => $user->pincode,
                    'pan_number' => $user->pan_number,
                    'additional_contact_number' => $user->additional_contact_number,
                ]);
            });
        } catch (\Exception $e) {
            \Log::error('Profile update failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update profile. ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateFcmToken(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string',
        ]);
        $user = auth()->user();
        $user->fcm_token = $request->fcm_token;
        $user->save();
        return response()->json(['message' => 'FCM token updated successfully']);
    }

    public function sendTestNotification(Request $request)
    {
        $user = auth()->user();
        if (!$user->fcm_token) {
            return response()->json(['error' => 'No FCM token set for user'], 400);
        }
        $result = \App\Helpers\FcmHelper::sendV1($user->fcm_token, 'Test Notification', 'This is a test push notification.');
        return response()->json(['result' => $result]);
    }

    /**
     * Check and update referral milestones for a user
     *
     * @param User $user
     * @return void
     */
    private function checkReferralMilestones(User $user): void
    {
        $totalReferrals = $user->total_referrals;
        
        // Check 10 referrals milestone
        if ($totalReferrals >= 10 && !$user->milestone_10_reached) {
            $user->milestone_10_reached = true;
            $user->wallet_balance += 100; // Bonus for 10 referrals
            $user->save();
            
            // Log milestone achievement
            \Log::info('User reached 10 referrals milestone', [
                'user_id' => $user->id,
                'total_referrals' => $totalReferrals
            ]);
        }
        
        // Check 50 referrals milestone
        if ($totalReferrals >= 50 && !$user->milestone_50_reached) {
            $user->milestone_50_reached = true;
            $user->wallet_balance += 500; // Bonus for 50 referrals
            $user->save();
            
            // Log milestone achievement
            \Log::info('User reached 50 referrals milestone', [
                'user_id' => $user->id,
                'total_referrals' => $totalReferrals
            ]);
        }
        
        // Check 100 referrals milestone
        if ($totalReferrals >= 100 && !$user->milestone_100_reached) {
            $user->milestone_100_reached = true;
            $user->wallet_balance += 1000; // Bonus for 100 referrals
            $user->save();
            
            // Log milestone achievement
            \Log::info('User reached 100 referrals milestone', [
                'user_id' => $user->id,
                'total_referrals' => $totalReferrals
            ]);
        }
    }
    
    /**
     * Send notification to referrer
     *
     * @param mixed $referrer
     * @param User $referredUser
     * @param string $type 'user' or 'agent'
     * @return void
     */
    private function sendReferralNotification($referrer, User $referredUser, string $type): void
    {
        try {
            $title = 'New Referral Registration';
            $body = "A new user has registered using your referral code!";
            
            if ($type === 'user') {
                $body = "You've earned 50 points! {$referredUser->name} joined using your referral code.";
            }
            
            if (!empty($referrer->fcm_token)) {
                $fcmResponse = \App\Helpers\FcmHelper::sendV1(
                    $referrer->fcm_token,
                    $title,
                    $body
                );
                
                \Log::info("Referral push notification sent to {$type}", [
                    'referrer_id' => $referrer->id,
                    'referred_id' => $referredUser->id,
                    'type' => $type,
                    'fcm_response' => $fcmResponse
                ]);
            }
        } catch (\Exception $e) {
            \Log::error("Failed to send referral notification to {$type}", [
                'referrer_id' => $referrer->id ?? null,
                'referred_id' => $referredUser->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
