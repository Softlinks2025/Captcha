<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\CaptchaSolve;
use App\Models\SubscriptionPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

class DailyLevelResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_daily_level_reset_command()
    {
        // Create a user with some level
        $user = User::factory()->create(['level' => 10]);
        
        // Verify initial level
        $this->assertEquals(10, $user->fresh()->level);
        
        // Run the reset command
        Artisan::call('levels:reset-daily');
        
        // Verify level is reset to 0
        $this->assertEquals(0, $user->fresh()->level);
    }

    public function test_captcha_solve_uses_daily_level()
    {
        // Create a subscription plan
        $plan = SubscriptionPlan::create([
            'name' => 'Test Plan',
            'captcha_per_day' => '100',
            'cost' => 999.00,
            'earnings' => [
                '1-50' => 15,
                '51-100' => 25
            ]
        ]);

        // Create a user with the plan
        $user = User::factory()->create([
            'subscription_name' => 'Test Plan',
            'level' => 0
        ]);

        // Solve 5 captchas today
        for ($i = 0; $i < 5; $i++) {
            CaptchaSolve::create(['user_id' => $user->id]);
        }

        // Verify today's level is 5
        $todayLevel = CaptchaSolve::where('user_id', $user->id)
            ->whereDate('created_at', now()->toDateString())
            ->count();
        $this->assertEquals(5, $todayLevel);

        // Verify total level is also 5 (since no previous solves)
        $totalLevel = CaptchaSolve::where('user_id', $user->id)->count();
        $this->assertEquals(5, $totalLevel);
    }

    public function test_level_api_returns_daily_level()
    {
        // Create a subscription plan
        $plan = SubscriptionPlan::create([
            'name' => 'Test Plan',
            'captcha_per_day' => '100',
            'cost' => 999.00,
            'earnings' => [
                '1-50' => 15,
                '51-100' => 25
            ]
        ]);

        // Create a user with the plan
        $user = User::factory()->create([
            'subscription_name' => 'Test Plan',
            'level' => 0
        ]);

        // Solve 3 captchas today
        for ($i = 0; $i < 3; $i++) {
            CaptchaSolve::create(['user_id' => $user->id]);
        }

        // Make API request to get level
        $response = $this->actingAs($user, 'api')
            ->getJson('/api/v1/captcha/level');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'level' => 3, // Today's level
                'total_level' => 3, // Total level
            ]);
    }

    public function test_level_resets_daily()
    {
        // Create a user
        $user = User::factory()->create(['level' => 5]);
        
        // Create some captcha solves for today
        CaptchaSolve::create(['user_id' => $user->id]);
        CaptchaSolve::create(['user_id' => $user->id]);
        
        // Verify today's level is 2
        $todayLevel = CaptchaSolve::where('user_id', $user->id)
            ->whereDate('created_at', now()->toDateString())
            ->count();
        $this->assertEquals(2, $todayLevel);
        
        // Run reset command
        Artisan::call('levels:reset-daily');
        
        // Verify user level is reset to 0
        $this->assertEquals(0, $user->fresh()->level);
        
        // But total captcha solves remain the same
        $totalSolves = CaptchaSolve::where('user_id', $user->id)->count();
        $this->assertEquals(2, $totalSolves);
    }
}
