<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Agent;
use App\Models\User;

class TestMilestoneSystem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:milestone-system {--agent-id= : Test specific agent by ID} {--milestone= : Test specific milestone (10, 50, 100)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the referral milestone system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $agentId = $this->option('agent-id');
        $milestone = $this->option('milestone');

        if ($agentId) {
            $agent = Agent::find($agentId);
            if (!$agent) {
                $this->error("Agent with ID {$agentId} not found");
                return 1;
            }
            $agents = collect([$agent]);
        } else {
            $agents = Agent::all();
        }

        $this->info("Testing milestone system for {$agents->count()} agents");
        $this->line('');

        foreach ($agents as $agent) {
            $this->testAgent($agent, $milestone);
        }

        $this->info('Milestone system test completed!');
    }

    private function testAgent($agent, $specificMilestone = null)
    {
        $this->line("Testing Agent: {$agent->name} (ID: {$agent->id})");
        $this->line("Current referrals: {$agent->total_referrals}");
        
        // Show current milestone status
        $this->showCurrentStatus($agent);
        
        if ($specificMilestone) {
            $this->testSpecificMilestone($agent, (int)$specificMilestone);
        } else {
            $this->testAllMilestones($agent);
        }
        
        $this->line('');
    }

    private function showCurrentStatus($agent)
    {
        $this->line("  Current Milestone Status:");
        $this->line("    10 referrals: " . ($agent->milestone_10_reached ? '✅' : '❌'));
        $this->line("    50 referrals: " . ($agent->milestone_50_reached ? '✅' : '❌'));
        $this->line("    100 referrals: " . ($agent->milestone_100_reached ? '✅' : '❌'));
        
        if ($agent->earnings_cap) {
            $this->line("    Earnings cap: ₹{$agent->earnings_cap}");
        }
        
        if ($agent->bonus_tshirt_claimed) {
            $this->line("    T-shirt bonus: ✅ Claimed");
        } elseif ($agent->milestone_50_reached) {
            $this->line("    T-shirt bonus: ⏳ Available");
        }
        
        if ($agent->bonus_bag_claimed) {
            $this->line("    Bag bonus: ✅ Claimed");
        } elseif ($agent->milestone_100_reached) {
            $this->line("    Bag bonus: ⏳ Available");
        }
    }

    private function testSpecificMilestone($agent, $milestone)
    {
        $this->line("  Testing milestone: {$milestone} referrals");
        
        // Simulate reaching the milestone
        $oldCount = $agent->total_referrals;
        $agent->total_referrals = $milestone;
        $agent->save();
        
        // Process milestone
        $agent->checkAndProcessMilestones($oldCount, $milestone);
        
        $this->line("  ✅ Milestone {$milestone} processed");
        
        // Show updated status
        $this->showCurrentStatus($agent);
        
        // Test bonus claiming if applicable
        $this->testBonusClaiming($agent);
    }

    private function testAllMilestones($agent)
    {
        $milestones = [10, 50, 100];
        
        foreach ($milestones as $milestone) {
            if ($agent->total_referrals < $milestone) {
                $this->line("  Testing milestone: {$milestone} referrals");
                
                // Simulate reaching the milestone
                $oldCount = $agent->total_referrals;
                $agent->total_referrals = $milestone;
                $agent->save();
                
                // Process milestone
                $agent->checkAndProcessMilestones($oldCount, $milestone);
                
                $this->line("  ✅ Milestone {$milestone} processed");
                
                // Show updated status
                $this->showCurrentStatus($agent);
                
                // Test bonus claiming if applicable
                $this->testBonusClaiming($agent);
                
                break; // Only test the next unreached milestone
            }
        }
    }

    private function testBonusClaiming($agent)
    {
        $this->line("  Testing bonus claiming:");
        
        // Test T-shirt bonus
        if ($agent->milestone_50_reached && !$agent->bonus_tshirt_claimed) {
            $result = $agent->claimTshirtBonus();
            if ($result['success']) {
                $this->line("    ✅ T-shirt bonus claimed successfully");
            } else {
                $this->line("    ❌ T-shirt bonus claim failed: " . $result['message']);
            }
        }
        
        // Test Bag bonus
        if ($agent->milestone_100_reached && !$agent->bonus_bag_claimed) {
            $result = $agent->claimBagBonus();
            if ($result['success']) {
                $this->line("    ✅ Bag bonus claimed successfully");
            } else {
                $this->line("    ❌ Bag bonus claim failed: " . $result['message']);
            }
        }
    }

    private function simulateReferrals($agent, $count)
    {
        $this->line("  Simulating {$count} new referrals...");
        
        // Create dummy users for testing
        for ($i = 0; $i < $count; $i++) {
            $user = User::create([
                'name' => "Test User " . ($agent->total_referrals + $i + 1),
                'phone' => '999999' . str_pad($agent->total_referrals + $i + 1, 4, '0', STR_PAD_LEFT),
                'email' => 'test' . ($agent->total_referrals + $i + 1) . '@example.com',
                'agent_id' => $agent->id,
                'profile_completed' => true,
                'is_verified' => true
            ]);
        }
        
        // Update referral count
        $oldCount = $agent->total_referrals;
        $newCount = $agent->getReferralCount();
        $agent->updateReferralCount();
        
        $this->line("  ✅ Added {$count} referrals (Total: {$newCount})");
        
        return $newCount;
    }
}
