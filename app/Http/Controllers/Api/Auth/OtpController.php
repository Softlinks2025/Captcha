<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
//use App\Services\TwilioService;
use App\Services\Fast2smsService;


class OtpController extends Controller
{
    /**
     * Send OTP to the provided phone number
     *
     * @param Request $request
     * @return JsonResponse
     */
    /**
     * Send OTP to the provided phone number
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sendOtp(Request $request, Fast2smsService $fast2sms ): JsonResponse
    {
        // Start timing the request
        $startTime = microtime(true);
        $logContext = [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'request_id' => Str::uuid()->toString()
        ];

        try {
            Log::info('OTP Request Received', array_merge($logContext, [
                'data' => $request->all()
            ]));

            // Validate the request
            $validated = $request->validate([
                'phone' => [
                    'required',
                    'string',
                    'min:6',
                    'max:20',
                    'regex:/^[+0-9\- ]+$/'
                ]
            ]);
            
           // $phone = $validated['phone'];
           $phone = $this->normalizePhoneNumber($request->phone);

// Normalize phone to E.164 format (+91XXXXXXXXXX)
if (!str_starts_with($phone, '+91')) {
    $phone = '+91' . ltrim($phone, '0'); // Remove leading 0 if present
}

Log::info('Phone number normalized to E.164', array_merge($logContext, [
    'normalized_phone' => $phone
]));

            Log::info('Phone number validated', array_merge($logContext, [
                'phone' => $phone
            ]));

            // Generate a random 6-digit OTP
            $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $otpExpiresAt = now()->addMinutes(10);
            
            Log::debug('OTP generated', array_merge($logContext, [
                'otp' => $otp,
                'expires_at' => $otpExpiresAt->toDateTimeString()
            ]));
            
            // Find the user by phone or create a new one
            $user = User::firstOrNew(['phone' => $phone]);
            
            // If it's a new user, set some default values
            if (!$user->exists) {
                $user->name = 'User-' . substr($phone, -4); // Default name based on last 4 digits of phone
                $user->password = bcrypt(Str::random(12)); // Temporary password
                $user->is_verified = false;
                Log::info('New user created for OTP', array_merge($logContext, [
                    'phone' => $phone,
                    'is_new_user' => true
                ]));
            }
            
            // Update user with new OTP
            $user->otp = Hash::make($otp);
            $user->otp_expires_at = $otpExpiresAt;
            
            if (!$user->save()) {
                $error = 'Failed to update OTP in database';
                Log::error($error, array_merge($logContext, [
                    'user_id' => $user->id,
                    'elapsed_ms' => round((microtime(true) - $startTime) * 1000, 2)
                ]));
                
                throw new \RuntimeException($error);
            }
            
            Log::info('OTP saved successfully', array_merge($logContext, [
                'user_id' => $user->id,
                'otp_expires_at' => $otpExpiresAt->toDateTimeString()
            ]));
            
                    //Send OTP via Fast2sms
        $message = "Your OTP is: {$otp}";
            $fast2smsSent = $fast2sms->sendOtp($phone, $otp);
            
            if (!$fast2smsSent) {
                throw new \Exception("Failed to send OTP via SMS.");
            }
            
            Log::info("OTP for {$phone}: {$otp}", $logContext);
            
            $response = [
                'status' => 'success',
                'message' => 'OTP sent successfully',
                'data' => [
                    'phone' => $phone,
                    'otp_expires_in' => 10, // minutes
                    'otp_expires_at' => $otpExpiresAt->toIso8601String(),
                    // Include OTP in development for testing
                  //'otp' => config('app.env') === 'local' ? $otp : null,
                    'request_id' => $logContext['request_id']
                ]
            ];
            
            Log::info('OTP response prepared', array_merge($logContext, [
                'elapsed_ms' => round((microtime(true) - $startTime) * 1000, 2)
            ]));
            
            return response()->json($response);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->errors();
            Log::warning('Validation failed', array_merge($logContext, [
                'errors' => $errors,
                'input' => $request->all(),
                'elapsed_ms' => round((microtime(true) - $startTime) * 1000, 2)
            ]));
            
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $errors,
                'request_id' => $logContext['request_id'],
                'debug' => config('app.debug') ? [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ] : null
            ], 422);
            
        } catch (\Exception $e) {
            $errorId = Str::uuid();
            Log::error('OTP send error', array_merge($logContext, [
                'error_id' => $errorId,
                'message' => $e->getMessage(),
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null,
                'elapsed_ms' => round((microtime(true) - $startTime) * 1000, 2)
            ]));
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send OTP. Please try again.',
                'error_id' => $errorId,
                'request_id' => $logContext['request_id'],
                'debug' => config('app.debug') ? [
                    'message' => $e->getMessage(),
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ] : null
            ], 500);
        }
    }

    /**
     * Verify OTP and return user status
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'phone' => [
                    'required',
                    'string',
                    'min:6',
                    'max:20',
                    'regex:/^[+0-9\- ]+$/'
                ],
                'otp' => [
                    'required',
                    'string',
                    'size:6',
                ]
            ]);

            // Find user by phone (if exists)
            $user = User::where('phone', $this->normalizePhoneNumber($request->phone))->first();
              // Normalize phone number to E.164 format for Twilio
              if (!str_starts_with($request->phone, '+91')) {
                $request->phone = '+91' . ltrim($request->phone, '0');
            }
            
            Log::info('Normalized phone number', ['formatted' => $request->phone]);
            
            // If user doesn't exist, just verify OTP is valid (for registration)
            if (!$user) {
                // In a real app, you might want to verify OTP from session/cache
                // For now, we'll just return success if OTP is 6 digits
                if (strlen($request->otp) !== 6) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Invalid OTP format',
                    ], 400);
                }

                return response()->json([
                    'status' => 'success',
                    'message' => 'OTP verified. Please register.',
                    'data' => [
                        'user_exists' => false,
                        'phone' => $request->phone,
                        'otp_verified' => true
                    ]
                ]);
            }

            // For existing users, verify OTP from database
            if (!Hash::check($request->otp, $user->otp)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid OTP',
                ], 400);
            }

            if (now()->gt($user->otp_expires_at)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'OTP has expired',
                ], 400);
            }

            // Mark user as verified
            $user->is_verified = true;
            $user->phone_verified_at = now();
            $user->otp = null;
            $user->otp_expires_at = null;
            $user->save();

            // Generate JWT token for the user
            $token = auth('api')->login($user);

            return response()->json([
                'status' => 'success',
                'message' => 'OTP verified successfully',
                'data' => [
                    'user_exists' => true,
                    'user' => [
                        'id' => $user->id,
                        'phone' => $user->phone,
                        'is_verified' => true,
                        'profile_completed' => (bool) $user->name,
                    ],
                    'token' => [
                        'access_token' => $token,
                        'token_type' => 'bearer',
                        'expires_in' => auth('api')->factory()->getTTL() * 60
                    ],
                    'redirect_to' => $user->name ? '/dashboard' : '/complete-profile'
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('OTP verification error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to verify OTP',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
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
