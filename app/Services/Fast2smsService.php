<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Fast2smsService
{
    protected $apiKey;
    protected $baseUrl = 'https://www.fast2sms.com/dev/bulkV2?';

    public function __construct()
    {
        $this->apiKey = config('services.fast2sms.api_key');
    }

    /**
     * Send OTP via Fast2SMS
     *
     * @param string $phoneNumber
     * @param string $message
     * @return bool
     */
    public function sendOtp(string $phoneNumber, string $otp): bool
    {
        try {
            // Remove any spaces or special characters from the phone number
            $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
            
            // Ensure the phone number starts with a country code (assuming India +91)
            if (strlen($phoneNumber) === 10) {
                $phoneNumber = '91' . $phoneNumber;
            }

            $response = Http::withHeaders([
                'authorization' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl, [
                'route' => 'dlt',
                'sender_id' => 'SOFAUT', // You can change this to your sender ID if you have one
                'message' => '195269',
                'variables_values' => $otp,
                'flash' => "0",
                //'language' => 'english',
                'numbers' => $phoneNumber,

            ]);

            $result = $response->json();
            
            // Log the response for debugging
            Log::info('Fast2SMS API Response:', [
                'phone' => $phoneNumber,
                'status' => $response->status(),
                'response' => $result
                
            ]);

            // Check if the request was successful
            if ($response->successful() && isset($result['return']) && $result['return'] === true) {
                return true;
            }

            return false;

        } catch (\Exception $e) {
            Log::error('Fast2SMS Error: ' . $e->getMessage(), [
                'phone' => $phoneNumber,
                'exception' => $e
            ]);
            return false;
        }
    }
}