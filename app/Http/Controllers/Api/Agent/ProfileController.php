<?php

namespace App\Http\Controllers\Api\Agent;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Get agent profile
     */
    public function getProfile()
    {
        try {
            $agent = auth('agent')->user();

            return response()->json([
                'status' => 'success',
                'message' => 'Profile retrieved successfully',
                'data' => [
                    'agent' => [
                        'id' => $agent->id,
                        'name' => $agent->name,
                        'phone_number' => $agent->phone_number,
                        'date_of_birth' => $agent->date_of_birth,
                        'email' => $agent->email,
                        'upi_id' => $agent->upi_id,
                        'profile_image' => $agent->profile_image,
                        'profile_image_url' => $agent->profile_image ? asset('storage/' . $agent->profile_image) : null,
                        'is_verified' => $agent->is_verified,
                        'profile_completed' => $agent->profile_completed,
                        'referral_code' => $agent->referral_code,
                        'wallet_balance' => $agent->wallet_balance,
                        'total_earnings' => $agent->total_earnings,
                        'total_withdrawals' => $agent->total_withdrawals,
                        'status' => $agent->status,
                        'created_at' => $agent->created_at,
                        'updated_at' => $agent->updated_at,
                        'referred_users' => $agent->referredUsers->map(function($user) {
                            return [
                                'id' => $user->id,
                                'name' => $user->name,
                                'phone' => $user->phone,
                                'profile_completed' => (bool) $user->profile_completed,
                                'registered_at' => $user->created_at ? $user->created_at->toDateTimeString() : null,
                            ];
                        }),
                        'referral_count' => $agent->referredUsers->count(),
                        'bank_account_number' => $agent->bank_account_number,
                        'bank_name' => $agent->bank_name,
                        'ifsc_code' => $agent->ifsc_code,
                        'account_holder_name' => $agent->account_holder_name,
                        'address' => $agent->address,
                        'city' => $agent->city,
                        'state' => $agent->state,
                        'pincode' => $agent->pincode,
                        'pan_number' => $agent->pan_number,
                        'additional_contact_number' => $agent->additional_contact_number,
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Agent profile retrieval error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve profile',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Complete agent profile
     */
    public function completeProfile(Request $request)
    {
        // Suppress any output that might be causing the headers issue
        ob_start();
        
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'date_of_birth' => 'required|date|before:today',
                'email' => 'required|email',
                'phone_number' => 'required|string',
                'address' => 'nullable|string',
                'city' => 'nullable|string',
                'state' => 'nullable|string',
                'pincode' => 'nullable|string',
                'bank_account_number' => 'required|string',
                'ifsc_code' => 'required|string',
                'account_holder_name' => 'nullable|string',
                'upi_id' => 'nullable|string|max:255',
                'profile_image' => 'nullable|image|max:2048|mimes:jpg,jpeg,png,webp',
                'pan_number' => 'required|string',
                'bank_name' => 'nullable|string',
                'additional_contact_number' => 'required|string',
            ]);

            $agent = auth('agent')->user();

            if ($agent->profile_completed) {
                // Clear any output buffer
                ob_end_clean();
                return response()->json([
                    'status' => 'error',
                    'message' => 'Profile is already completed. You can only update your profile.'
                ], 403);
            }

            $updateData = [
                'name' => $request->name,
                'date_of_birth' => $request->date_of_birth,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'pincode' => $request->pincode,
                'bank_account_number' => $request->bank_account_number,
                'ifsc_code' => $request->ifsc_code,
                'account_holder_name' => $request->account_holder_name,
                'upi_id' => $request->upi_id,
                'bank_name' => $request->bank_name,
                'pan_number' => $request->pan_number,
                'additional_contact_number' => $request->additional_contact_number,
                'profile_completed' => true
            ];

            // Handle profile image upload if present (file or URL)
            if ($request->hasFile('profile_image')) {
                try {
                    // Delete old image if exists
                    if ($agent->profile_image) {
                        Storage::disk('public')->delete($agent->profile_image);
                    }
                    $path = $request->file('profile_image')->store('profile-images/' . $agent->id, 'public');
                    $updateData['profile_image'] = $path;
                } catch (\Exception $e) {
                    Log::error('Agent profile image upload failed', [
                        'agent_id' => $agent->id,
                        'error' => $e->getMessage()
                    ]);
                    throw new \RuntimeException('Failed to upload profile image. Please try again.');
                }
            } else if ($request->filled('profile_image_url') && filter_var($request->input('profile_image_url'), FILTER_VALIDATE_URL)) {
                try {
                    if ($agent->profile_image) {
                        Storage::disk('public')->delete($agent->profile_image);
                    }
                    $url = $request->input('profile_image_url');
                    $imageContents = @file_get_contents($url);
                    if ($imageContents === false) {
                        throw new \RuntimeException('Failed to download image from URL.');
                    }
                    $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
                    $filename = 'profile-images/' . $agent->id . '/' . uniqid('agent_' . $agent->id . '_') . '.' . $extension;
                    Storage::disk('public')->put($filename, $imageContents);
                    $updateData['profile_image'] = $filename;
                    Log::info('Agent profile image downloaded from URL', [
                        'agent_id' => $agent->id,
                        'profile_image' => $filename,
                        'source_url' => $url
                    ]);
                } catch (\Exception $e) {
                    Log::error('Agent profile image download failed', [
                        'agent_id' => $agent->id,
                        'error' => $e->getMessage(),
                        'source_url' => $request->input('profile_image_url')
                    ]);
                    throw new \RuntimeException('Failed to download profile image from URL. Please try again.');
                }
            }

            $agent->update($updateData);

            Log::info('Agent profile completed', [
                'agent_id' => $agent->id,
                'phone_number' => $agent->phone_number,
                'name' => $agent->name
            ]);

            // Generate a new access token for the agent
            $token = auth('agent')->login($agent);
            // Clear any output buffer
            ob_end_clean();
            return response()->json([
                'status' => 'success',
                'message' => 'Profile completed successfully',
                'data' => [
                    'access_token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => auth('agent')->factory()->getTTL() * 60,
                    'agent' => [
                        'id' => $agent->id,
                        'name' => $agent->name,
                        'phone_number' => $agent->phone_number,
                        'date_of_birth' => $agent->date_of_birth,
                        'email' => $agent->email,
                        'upi_id' => $agent->upi_id,
                        'profile_image' => $agent->profile_image,
                        'profile_image_url' => $agent->profile_image ? asset('storage/' . $agent->profile_image) : null,
                        'is_verified' => $agent->is_verified,
                        'profile_completed' => $agent->profile_completed,
                        'referral_code' => $agent->referral_code,
                        'wallet_balance' => $agent->wallet_balance,
                        'total_earnings' => $agent->total_earnings,
                        'total_withdrawals' => $agent->total_withdrawals,
                        'status' => $agent->status,
                        'created_at' => $agent->created_at,
                        'updated_at' => $agent->updated_at,
                        'bank_account_number' => $agent->bank_account_number,
                        'bank_name' => $agent->bank_name,
                        'ifsc_code' => $agent->ifsc_code,
                        'account_holder_name' => $agent->account_holder_name,
                        'address' => $agent->address,
                        'city' => $agent->city,
                        'state' => $agent->state,
                        'pincode' => $agent->pincode,
                        'pan_number' => $agent->pan_number,
                        'additional_contact_number' => $agent->additional_contact_number,
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            // Clear any output buffer
            ob_end_clean();
            Log::error('Agent profile completion error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to complete profile',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Update agent profile
     */
    public function updateProfile(Request $request)
    {
        try {
            $request->validate([
                'name' => 'nullable|string|max:255',
                'date_of_birth' => 'nullable|date|before:today',
                'email' => 'nullable|email|unique:agents,email,' . auth('agent')->id(),
                'upi_id' => 'nullable|string|max:255',
                'profile_image' => 'nullable|image|max:2048|mimes:jpg,jpeg,png,webp',
                'bank_name' => 'nullable|string',
                'additional_contact_number' => 'required|string',
                'address' => 'nullable|string',
                'city' => 'nullable|string',
                'state' => 'nullable|string',
                'pincode' => 'nullable|string',
                'bank_account_number' => 'nullable|string',
                'ifsc_code' => 'nullable|string',
                'account_holder_name' => 'nullable|string',
            ]);

            $agent = auth('agent')->user();
            $updateData = $request->only([
                'name', 'date_of_birth', 'email', 'upi_id', 'bank_name', 'additional_contact_number', 'address', 'city', 'state', 'pincode', 'bank_account_number', 'ifsc_code', 'account_holder_name'
            ]);

            // Handle profile image upload if present (file or URL)
            if ($request->hasFile('profile_image')) {
                try {
                    if ($agent->profile_image) {
                        Storage::disk('public')->delete($agent->profile_image);
                    }
                    $path = $request->file('profile_image')->store('profile-images/' . $agent->id, 'public');
                    $updateData['profile_image'] = $path;
                } catch (\Exception $e) {
                    Log::error('Agent profile image upload failed', [
                        'agent_id' => $agent->id,
                        'error' => $e->getMessage()
                    ]);
                    throw new \RuntimeException('Failed to upload profile image. Please try again.');
                }
            } else if ($request->filled('profile_image_url') && filter_var($request->input('profile_image_url'), FILTER_VALIDATE_URL)) {
                try {
                    if ($agent->profile_image) {
                        Storage::disk('public')->delete($agent->profile_image);
                    }
                    $url = $request->input('profile_image_url');
                    $imageContents = @file_get_contents($url);
                    if ($imageContents === false) {
                        throw new \RuntimeException('Failed to download image from URL.');
                    }
                    $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
                    $filename = 'profile-images/' . $agent->id . '/' . uniqid('agent_' . $agent->id . '_') . '.' . $extension;
                    Storage::disk('public')->put($filename, $imageContents);
                    $updateData['profile_image'] = $filename;
                    Log::info('Agent profile image downloaded from URL', [
                        'agent_id' => $agent->id,
                        'profile_image' => $filename,
                        'source_url' => $url
                    ]);
                } catch (\Exception $e) {
                    Log::error('Agent profile image download failed', [
                        'agent_id' => $agent->id,
                        'error' => $e->getMessage(),
                        'source_url' => $request->input('profile_image_url')
                    ]);
                    throw new \RuntimeException('Failed to download profile image from URL. Please try again.');
                }
            }

            // In completeProfile and updateProfile, ensure $updateData['bank_name'] is handled if present
            if ($request->filled('bank_name')) {
                $updateData['bank_name'] = $request->bank_name;
            }

            // Remove null values
            $updateData = array_filter($updateData, function($value) {
                return $value !== null;
            });

            $agent->update($updateData);

            Log::info('Agent profile updated', [
                'agent_id' => $agent->id,
                'phone_number' => $agent->phone_number
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Profile updated successfully',
                'data' => [
                    'agent' => [
                        'id' => $agent->id,
                        'name' => $agent->name,
                        'phone_number' => $agent->phone_number,
                        'date_of_birth' => $agent->date_of_birth,
                        'email' => $agent->email,
                        'upi_id' => $agent->upi_id,
                        'address' => $agent->address,
                        'profile_image' => $agent->profile_image,
                        'profile_image_url' => $agent->profile_image ? asset('storage/' . $agent->profile_image) : null,
                        'is_verified' => $agent->is_verified,
                        'profile_completed' => $agent->profile_completed,
                        'referral_code' => $agent->referral_code,
                        'wallet_balance' => $agent->wallet_balance,
                        'total_earnings' => $agent->total_earnings,
                        'total_withdrawals' => $agent->total_withdrawals,
                        'status' => $agent->status,
                        'created_at' => $agent->created_at,
                        'updated_at' => $agent->updated_at,
                        'pan_number' => $agent->pan_number,
                        'additional_contact_number' => $agent->additional_contact_number,
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Agent profile update error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update profile',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Upload profile image
     */
    public function uploadProfileImage(Request $request)
    {
        try {
            $request->validate([
                'profile_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            $agent = auth('agent')->user();

            if ($request->hasFile('profile_image')) {
                // Delete old image if exists
                if ($agent->profile_image) {
                    Storage::disk('public')->delete($agent->profile_image);
                }

                // Store new image
                $path = $request->file('profile_image')->store('agent-profiles', 'public');
                
                $agent->update(['profile_image' => $path]);

                Log::info('Agent profile image uploaded', [
                    'agent_id' => $agent->id,
                    'image_path' => $path
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Profile image uploaded successfully',
                    'data' => [
                        'profile_image' => Storage::url($path)
                    ]
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'No image file provided'
            ], 400);

        } catch (\Exception $e) {
            Log::error('Agent profile image upload error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to upload profile image',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get agent joining fee status
     */
    public function joiningFeeStatus()
    {
        $agent = auth('agent')->user();
        if (!$agent) {
            return response()->json([
                'status' => 'error',
                'message' => 'Agent not authenticated.'
            ], 401);
        }

        $joiningFeePlan = null;
        if ($agent->joining_fee_plan_id) {
            $joiningFeePlan = \App\Models\AgentJoiningFee::find($agent->joining_fee_plan_id);
        }

        return response()->json([
            'status' => 'success',
            'joining_fee' => [
                'paid' => (bool) $agent->joining_fee_paid,
                'paid_at' => $agent->joining_fee_paid_at,
                'plan' => $joiningFeePlan ? [
                    'id' => $joiningFeePlan->id,
                    'name' => $joiningFeePlan->name,
                    'amount' => $joiningFeePlan->amount,
                    'validity' => $joiningFeePlan->validity,
                    'created_at' => $joiningFeePlan->created_at,
                    'updated_at' => $joiningFeePlan->updated_at
                ] : null,
                'last_updated' => $agent->updated_at
            ]
        ]);
    }

    public function updateFcmToken(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string',
        ]);
        $agent = auth()->user();
        $agent->fcm_token = $request->fcm_token;
        $agent->save();
        return response()->json(['message' => 'FCM token updated successfully']);
    }
} 