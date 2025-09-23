<?php

namespace App\Http\Controllers\Api\Agent;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Agent login
     */
    public function login(Request $request )
    {
        try {
            $request->validate([
                'phone_number' => 'required|string|regex:/^[0-9]{10}$/',
                'otp' => 'required|string|size:6',
            ]);

                // Normalize the phone number using a local method
    $normalizedPhone = $this->normalizePhoneNumber($request->phone_number);


            // Find agent by phone number
            $agent = Agent::where('phone_number', $normalizedPhone)->first();

            if (!$agent) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Agent not found with this phone number'
                ], 404);
            }

            // Check if OTP exists
            if (empty($agent->otp)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No OTP found. Please request an OTP first.'
                ], 422);
            }

            // Check if OTP is expired
            if ($agent->otp_expires_at && $agent->otp_expires_at->isPast()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'OTP has expired'
                ], 422);
            }

            // Verify OTP using Hash::check since OTP is stored as hash
            if (!Hash::check($request->otp, $agent->otp)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid OTP'
                ], 422);
            }

            // Clear OTP after successful verification
            $agent->otp = null;
            $agent->otp_expires_at = null;
            $agent->last_login_at = now();
            // Save FCM token if provided
            if ($request->filled('fcm_token')) {
                $agent->fcm_token = $request->fcm_token;
            }
            $agent->save();

            // Generate JWT token
            $token = auth('agent')->login($agent);

            // Send push notification for successful login
            \Log::info('Attempting to send login push notification to agent', [
                'agent_id' => $agent->id,
                'fcm_token' => $agent->fcm_token,
                'title' => 'Login Successful',
                'body' => 'You have logged in successfully.'
            ]);
            try {
                $fcmResponse = \App\Helpers\FcmHelper::sendV1(
                    $agent->fcm_token,
                    'Login Successful',
                    'You have logged in successfully.'
                );
                \Log::info('Login push notification sent to agent', [
                    'agent_id' => $agent->id,
                    'fcm_token' => $agent->fcm_token,
                    'fcm_response' => $fcmResponse
                ]);
            } catch (\Exception $e) {
                \Log::error('Failed to send login push notification to agent', [
                    'agent_id' => $agent->id,
                    'error' => $e->getMessage()
                ]);
            }

            Log::info('Agent logged in successfully with OTP', [
                'agent_id' => $agent->id,
                'phone_number' => $agent->phone_number
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Login successful',
                'data' => [
                    'access_token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => auth('agent')->factory()->getTTL() * 60,
                    'agent' => [
                        'id' => $agent->id,
                        'name' => $agent->name,
                        'phone_number' => $agent->phone_number,
                        'email' => $agent->email,
                        'referral_code' => $agent->referral_code,
                        'is_verified' => $agent->is_verified,
                        'profile_completed' => $agent->profile_completed,
                        'wallet_balance' => $agent->wallet_balance,
                        'total_earnings' => $agent->total_earnings,
                        'total_withdrawals' => $agent->total_withdrawals,
                        'status' => $agent->status,
                        'profile_image' => $agent->profile_image,
                        'profile_image_url' => $agent->profile_image ? asset('storage/' . $agent->profile_image) : null,
                        'upi_id' => $agent->upi_id,
                        'fcm_token' => $agent->fcm_token,
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Agent login error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Login failed',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Agent logout
     */
    public function logout()
    {
        try {
            auth('agent')->logout();

            return response()->json([
                'status' => 'success',
                'message' => 'Successfully logged out'
            ]);
        } catch (\Exception $e) {
            Log::error('Agent logout error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Logout failed',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Refresh agent token
     */
    public function refresh()
    {
        try {
            $token = auth('agent')->refresh();
            $agent = auth('agent')->user();

            return response()->json([
                'status' => 'success',
                'message' => 'Token refreshed successfully',
                'data' => [
                    'token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => auth('agent')->factory()->getTTL() * 60,
                    'agent' => [
                        'id' => $agent->id,
                        'name' => $agent->name,
                        'phone_number' => $agent->phone_number,
                        'email' => $agent->email,
                        'referral_code' => $agent->referral_code,
                        'is_verified' => $agent->is_verified,
                        'profile_completed' => $agent->profile_completed,
                        'wallet_balance' => $agent->wallet_balance,
                        'total_earnings' => $agent->total_earnings,
                        'total_withdrawals' => $agent->total_withdrawals,
                        'status' => $agent->status,
                        'profile_image' => $agent->profile_image,
                        'profile_image_url' => $agent->profile_image ? asset('storage/' . $agent->profile_image) : null,
                        'upi_id' => $agent->upi_id,
                        'fcm_token' => $agent->fcm_token,
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Agent token refresh error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Token refresh failed',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get current agent profile
     */
    public function me()
    {
        try {
            $agent = auth('agent')->user();

            return response()->json([
                'status' => 'success',
                'message' => 'Agent profile retrieved successfully',
                'data' => [
                    'agent' => [
                        'id' => $agent->id,
                        'name' => $agent->name,
                        'phone_number' => $agent->phone_number,
                        'email' => $agent->email,
                        'referral_code' => $agent->referral_code,
                        'is_verified' => $agent->is_verified,
                        'profile_completed' => $agent->profile_completed,
                        'wallet_balance' => $agent->wallet_balance,
                        'total_earnings' => $agent->total_earnings,
                        'total_withdrawals' => $agent->total_withdrawals,
                        'status' => $agent->status,
                        'profile_image' => $agent->profile_image,
                        'profile_image_url' => $agent->profile_image ? asset('storage/' . $agent->profile_image) : null,
                        'upi_id' => $agent->upi_id,
                        'fcm_token' => $agent->fcm_token,
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
                'message' => 'Failed to retrieve agent profile',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    private function normalizePhoneNumber($phone_number)
{
    // Remove all non-numeric characters
    $phone = preg_replace('/[^0-9]/', '', $phone_number);

    // If 10-digit Indian number, prefix with +91
    if (strlen($phone_number) === 10) {
        return '+91' . $phone_number;
    }

    // If already starts with 91 (without +), add +
    if (strlen($phone_number) === 12 && substr($phone_number, 0, 2) === '91') {
        return '+' . $phone_number;
    }

    // If already in correct +91 format, return as-is
    if (strlen($phone_number) === 13 && substr($phone_number, 0, 3) === '+91') {
        return $phone_number;
    }

    // Fallback (may need customization)
    return '+' . $phone_number;
}

} 