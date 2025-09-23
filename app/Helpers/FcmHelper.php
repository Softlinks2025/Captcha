<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;

class FcmHelper
{
    public static function send($fcmToken, $title, $body, $data = [])
    {
        $serverKey = config('services.fcm.server_key', env('FCM_SERVER_KEY', 'dummy-key'));

        $response = Http::withHeaders([
            'Authorization' => 'key=' . $serverKey,
            'Content-Type' => 'application/json',
        ])->post('https://fcm.googleapis.com/fcm/send', [
            'to' => $fcmToken,
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
            'data' => $data,
        ]);

        return $response->json();
    }

    /**
     * Send a push notification using FCM HTTP v1 API.
     * Requires composer package: google/auth
     *
     * @param string $fcmToken
     * @param string $title
     * @param string $body
     * @param array $data
     * @return array|null
     */
    public static function sendV1($fcmToken, $title, $body, $data = [])
    {
        $serviceAccountPath = config('services.fcm.service_account_path');
        if (!file_exists($serviceAccountPath)) {
            throw new \InvalidArgumentException('FCM service account file does not exist: ' . $serviceAccountPath);
        }
        $projectId = 'captcha-pro5959';

        // Load Google Auth library
        if (!class_exists(\Google\Auth\Credentials\ServiceAccountCredentials::class)) {
            throw new \Exception('google/auth package not installed. Run: composer require google/auth');
        }
        $scopes = ['https://www.googleapis.com/auth/firebase.messaging'];
        $credentials = new \Google\Auth\Credentials\ServiceAccountCredentials($scopes, $serviceAccountPath);
        $accessToken = $credentials->fetchAuthToken()['access_token'] ?? null;
        if (!$accessToken) {
            throw new \Exception('Could not fetch access token for FCM.');
        }

        $message = [
            'message' => [
                'token' => $fcmToken,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
            ]
        ];
        if (!empty($data) && is_array($data) && array_values($data) !== $data) {
            // Only add data if it's a non-empty associative array
            $message['message']['data'] = $data;
        }

        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";
        
        // Log the request
        \Log::info('FCM sendV1 request', [
            'token' => $fcmToken,
            'title' => $title,
            'body' => $body,
            'data' => $data,
            'url' => $url,
            'service_account_path' => $serviceAccountPath,
        ]);

        $response = \Illuminate\Support\Facades\Http::withToken($accessToken)
            ->post($url, $message);

        // Log the response
        \Log::info('FCM sendV1 response', [
            'response' => $response->json(),
            'status' => $response->status(),
        ]);

        return $response->json();
    }
} 