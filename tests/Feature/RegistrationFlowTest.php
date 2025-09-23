<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegistrationFlowTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_registration_flow()
    {
        // Step 1: Request OTP for new user
        $phoneNumber = '9876543210';
        
        $response = $this->postJson('/api/v1/send-otp', [
            'phone' => $phoneNumber
        ]);
        
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'message' => 'OTP sent successfully'
        ]);
        
        // Get the OTP from the response (in development)
        $otp = $response->json('data.otp');
        
        // Step 2: Register with OTP
        $registerResponse = $this->postJson('/api/v1/register', [
            'phone' => $phoneNumber,
            'otp' => $otp
        ]);
        
        $registerResponse->assertStatus(201);
        $registerData = $registerResponse->json();
        
        // Verify the response structure
        $this->assertArrayHasKey('data', $registerData);
        $this->assertArrayHasKey('user', $registerData['data']);
        $this->assertArrayHasKey('token', $registerData['data']);
        
        // Verify the user is not marked as profile_completed
        $this->assertFalse($registerData['data']['user']['profile_completed']);
        
        // Verify the redirect is to complete-profile
        $this->assertEquals('/complete-profile', $registerData['data']['redirect_to']);
        
        // Get the token for authenticated requests
        $token = $registerData['data']['token']['access_token'];
        
        // Step 3: Complete profile
        $completeProfileResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'
        ])->postJson('/api/v1/profile/complete', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'date_of_birth' => '1990-01-01',
            'upi_id' => 'test@upi',
            // Referral code is optional
        ]);
        
        $completeProfileResponse->assertStatus(200);
        $profileData = $completeProfileResponse->json();
        
        // Verify the profile was completed successfully
        $this->assertEquals('success', $profileData['status']);
        $this->assertEquals('Profile completed successfully', $profileData['message']);
        
        // Verify the user is now marked as profile_completed
        $user = User::where('phone', $phoneNumber)->first();
        $this->assertTrue($user->profile_completed);
        
        // Verify the user can access protected routes
        $meResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'
        ])->getJson('/api/v1/me');
        
        $meResponse->assertStatus(200);
        $meData = $meResponse->json();
        $this->assertEquals($user->id, $meData['id']);
    }
}
