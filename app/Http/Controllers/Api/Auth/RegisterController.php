<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    /**
     * Register a new user with OTP verification
     * 
     * @param RegisterUserRequest $request
     * @return JsonResponse
     */
    public function register(RegisterUserRequest $request): JsonResponse
    {
        // Enable detailed error reporting
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        
        // Log the incoming request
        \Log::info('Register API called', [
            'phone' => $request->phone,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'all_input' => $request->all()
        ]);
        
        try {
            $validated = $request->validated();
            \Log::debug('Request validated', ['validated' => $validated]);
            
            // Start database transaction
            return DB::transaction(function () use ($validated, $request) {
                \Log::debug('Transaction started', ['phone' => $validated['phone']]);
                \Log::debug('Starting database transaction');
                \Log::debug('Starting registration transaction', ['phone' => $validated['phone']]);
                
                // Check if user already exists and is verified
                $user = User::where('phone', $this->normalizePhoneNumber($validated['phone']))->first();
                \Log::debug('User lookup result', ['exists' => $user ? true : false, 'is_verified' => $user ? $user->is_verified : null]);
                
                if ($user && $user->is_verified) {
                    // Block registration for already verified users
                    return response()->json([
                        'status' => 'error',
                        'message' => 'This phone number is already registered. Please login instead.'
                    ], 409);
                }
                
                // Verify OTP for new or unverified user
                $user = User::where('phone', $this->normalizePhoneNumber($validated['phone']))
                    ->whereNotNull('otp')
                    ->where('otp_expires_at', '>', now())
                    ->first();
                
                \Log::debug('OTP verification check', [
                    'user_found' => $user ? true : false,
                    'has_otp' => $user ? !is_null($user->otp) : false,
                    'otp_expires_at' => $user ? $user->otp_expires_at : null,
                    'current_time' => now()
                ]);
                
                if (!$user) {
                    \Log::error('User not found or OTP expired', ['phone' => $validated['phone']]);
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Invalid or expired OTP. Please request a new OTP.'
                    ], 400);
                }
                
                try {
                    if (!Hash::check($validated['otp'], $user->otp)) {
                        \Log::error('Invalid OTP provided', [
                            'phone' => $validated['phone'],
                            'stored_otp_hash' => $user->otp,
                            'provided_otp' => $validated['otp']
                        ]);
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Invalid OTP. Please try again.'
                        ], 400);
                    }
                } catch (\Exception $e) {
                    \Log::error('OTP verification failed', [
                        'error' => $e->getMessage(),
                        'otp' => $validated['otp'],
                        'stored_otp' => $user->otp
                    ]);
                    throw $e;
                }
                
                // Generate referral code if not exists
                $referralCode = $user->referral_code ?? $this->generateUniqueReferralCode();
                
                // Update user verification status and set profile as incomplete
                $updateData = [
                    'is_verified' => true,
                    'phone_verified_at' => now(),
                    'otp' => null,
                    'otp_expires_at' => null,
                    'referral_code' => $referralCode,
                    'profile_completed' => false
                ];
                
                if (!$user->update($updateData)) {
                    throw new \RuntimeException('Failed to update user verification status');
                }
                
                // Generate JWT token
                if (!$token = auth('api')->login($user)) {
                    throw new \RuntimeException('Failed to generate authentication token');
                }
                
                // Assign role to user
                $role = $validated['role'] ?? 'user';
                if (!$user->hasRole($role)) {
                    $user->assignRole($role);
                }
                
                $response = $this->respondWithToken($token, $user, 'Register successfully');
                
                // Add profile completion status to response
                $responseData = $response->getData(true);
                $responseData['profile_completed'] = $user->profile_completed;
                $responseData['requires_profile_completion'] = !$user->profile_completed;
                
                return response()->json($responseData);
                
            });
            
        } catch (\Exception $e) {
            // Log detailed error information
            $errorContext = [
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ],
                'request' => [
                    'method' => $request->method(),
                    'url' => $request->fullUrl(),
                    'input' => $request->all(),
                    'headers' => $request->headers->all()
                ]
            ];
            
            \Log::error('Registration failed', $errorContext);
            
            // Return detailed error in development, generic error in production
            $response = [
                'status' => 'error',
                'message' => config('app.debug') ? $e->getMessage() : 'Failed to complete registration. Please try again.'
            ];
            
            if (config('app.debug')) {
                $response['debug'] = [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'code' => $e->getCode()
                ];
            }
            
            return response()->json($response, 500);
        }
    }
    
    /**
     * Format token response
     * 
     * @param string $token
     * @param User $user
     * @param string $message
     * @return JsonResponse
     */
    protected function respondWithToken($token, $user, $message): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'phone' => $user->phone,
                    'is_verified' => true,
                    'profile_completed' => $user->profile_completed ?? false,
                ],
                'token' => [
                    'access_token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => auth('api')->factory()->getTTL() * 60
                ],
                'redirect_to' => ($user->profile_completed ?? false) ? '/dashboard' : '/complete-profile'
            ]
        ], 201);
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
    private function normalizePhoneNumber($number)
{
    // Keep only digits
    $digits = preg_replace('/\D/', '', $number);

    // Extract last 10 digits (standard for Indian mobile)
    $last10 = substr($digits, -10);

    // Prepend country code if not present
    return '+91' . $last10;
}

}
