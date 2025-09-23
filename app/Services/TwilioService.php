<?php

namespace App\Services;

use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;

class TwilioService
{
    protected $twilio;

    public function __construct()
    {
        $this->twilio = new Client(
            config('services.twilio.sid'),
            config('services.twilio.token')
        );
    }

    public function sendOtp(string $to, string $message): bool
    {
        try {
            $this->twilio->messages->create($to, [
                'from' => config('services.twilio.from'),
                'body' => $message
            ]);
            Log::info("Twilio OTP sent successfully to {$to}");
            return true;
        } catch (\Exception $e) {
            Log::error("Twilio OTP failed to send to {$to}", [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
