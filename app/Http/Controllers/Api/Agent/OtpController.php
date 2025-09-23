<?php

namespace App\Http\Controllers\Api\Agent;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
//use App\Services\TwilioService;
use Illuminate\Http\JsonResponse;
use App\Services\Fast2smsService;

class OtpController extends Controller
{
    /**
     * Send OTP to agent's phone number
     */
        public function sendOtp(Request $request, Fast2smsService $fast2sms): JsonResponse

    {
        try {
            // Log the incoming request for debugging
            Log::info('Agent OTP request received', [
                'request_data' => $request->all(),
                'headers' => $request->headers->all()
            ]);

            $request->validate([
                'phone_number' => [
                    'required',
                    'string',
                    'min:6',
                    'max:20',
                    'regex:/^[+0-9\- ]+$/'
                ]
            ]);

            $phoneNumber = $request->phone_number;

            // Normalize phone number to E.164 format for Twilio
            if (!str_starts_with($phoneNumber, '+91')) {
                $phoneNumber = '+91' . ltrim($phoneNumber, '0');
            }
            
            Log::info('Normalized phone number', ['formatted' => $phoneNumber]);
                        
            // Check if agent exists
            $agent = Agent::where('phone_number', $phoneNumber)->first();
            
            // If agent doesn't exist, create a temporary one for registration
            if (!$agent) {
                $agent = Agent::create([
                    'phone_number' => $phoneNumber,
                    'name' => 'Temporary', // Will be updated during registration
                    'referral_code' => Agent::generateReferralCode(),
                    'is_verified' => false,
                    'status' => 'pending'
                ]);
                
                Log::info('Temporary agent created for OTP', [
                    'phone' => $phoneNumber,
                    'agent_id' => $agent->id
                ]);
            }

            // Generate OTP
            $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            
            // Hash the OTP and store it
            $agent->otp = Hash::make($otp);
            $agent->otp_expires_at = now()->addMinutes(10);
            $agent->save();
            Log::info('OTP expiration set at', [
                'expires_at' => $agent->otp_expires_at,
                'current_time' => now()
            ]);
            

            // Send OTP via Fast2sms
           $message = "Your OTP is: {$otp}";
            $fast2smsSent = $fast2sms->sendOtp($phoneNumber, $otp);
            
            if (!$fast2smsSent) {
                throw new \Exception("Failed to send OTP via SMS.");
            }
            

            Log::info('OTP sent to agent', [
                'phone' => $phoneNumber,
                'otp' => $otp,
                'agent_id' => $agent->id,
                'is_new_agent' => $agent->name === 'Temporary'
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'OTP sent successfully',
                'data' => [
                    'phone_number' => $phoneNumber,
                    //'otp' => $otp, // Remove this in production
                    'expires_at' => $agent->otp_expires_at,
                    'is_new_agent' => $agent->name === 'Temporary'
                ]
            ]);

        } catch (ValidationException $e) {
            Log::error('Agent OTP validation error', [
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Agent OTP send error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send OTP',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Verify OTP for agent registration
     */
    public function verifyOtp(Request $request)
    {
        try {
            $request->validate([
                'phone_number' => [
                    'required',
                    'string',
                    'min:6',
                    'max:20',
                    'regex:/^[+0-9\- ]+$/'
                ],
                'otp' => [
                    'required',
                    'string',
                    'size:6'
                ]
            ]);

            $phoneNumber = normalizePhoneNumber($request->phone_number);
            $otp = $request->otp;
           

            // Find the agent
            $agent = Agent::where('phone_number', $phoneNumber)->first();
            
            if (!$agent) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Agent not found. Please send OTP first.'
                ], 404);
            }

            // Check if OTP exists and is not expired
            if (empty($agent->otp) || !$agent->otp_expires_at || now()->gt($agent->otp_expires_at)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'OTP has expired or not found'
                ], 400);
            }

            // Verify OTP
            if (!Hash::check($otp, $agent->otp)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid OTP'
                ], 400);
            }

            // Don't mark as verified here - that happens during registration
            // Just return success for OTP verification
            return response()->json([
                'status' => 'success',
                'message' => 'OTP verified successfully',
                'data' => [
                    'agent_id' => $agent->id,
                    'phone_number' => $agent->phone_number,
                    'is_verified' => $agent->is_verified,
                    'can_proceed_to_registration' => true
                ]
            ]);

        } catch (ValidationException $e) {
            Log::error('Agent OTP verification validation error', [
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Agent OTP verification error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to verify OTP',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
} 