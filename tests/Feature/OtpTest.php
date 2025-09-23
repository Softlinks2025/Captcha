<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OtpTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_send_otp_to_phone()
    {
        $response = $this->postJson('/api/v1/send-otp', [
            'phone' => '1234567890'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'OTP sent successfully',
            ]);

        $this->assertDatabaseHas('users', [
            'phone' => '1234567890'
        ]);
    }

    /** @test */
    public function it_can_verify_otp()
    {
        // First, send OTP
        $this->postJson('/api/v1/send-otp', [
            'phone' => '1234567890'
        ]);

        // Get the OTP from the database (in production, this would come via SMS)
        $user = User::where('phone', '1234567890')->first();
        
        $response = $this->postJson('/api/v1/verify-otp', [
            'phone' => '1234567890',
            'otp' => '123456' // This is the default OTP set in the controller
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'OTP verified successfully',
            ]);

        $this->assertDatabaseHas('users', [
            'phone' => '1234567890',
            'is_verified' => true,
            'phone_verified_at' => now()
        ]);
    }
}
