<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Agent;
use App\Models\SubscriptionPlan;
use App\Models\AgentPlan;
use App\Models\AgentPlanSubscription;
use Illuminate\Support\Facades\DB;

class TestAgentRewardSystem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:agent-reward-system';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the agent reward system for subscription plan purchases';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Agent Reward System...');
        
        // Check if we have the required data
        $agents = Agent::count();
        $users = User::count();
        $subscriptionPlans = SubscriptionPlan::count();
        $agentPlans = AgentPlan::count();
        
        $this->info("Found {$agents} agents, {$users} users, {$subscriptionPlans} subscription plans, {$agentPlans} agent plans");
        
        if ($agents === 0 || $users === 0 || $subscriptionPlans === 0 || $agentPlans === 0) {
            $this->error('Missing required data. Please ensure you have agents, users, subscription plans, and agent plans.');
            return 1;
        }
        
        // Get a test agent with an active plan
        $agent = Agent::whereHas('activePlanSubscription')->first();
        if (!$agent) {
            $this->error('No agent found with active plan. Please activate a plan for an agent first.');
            return 1;
        }
        
        $this->info("Using agent: {$agent->name} (ID: {$agent->id})");
        $this->info("Agent referral code: {$agent->referral_code}");
        
        $agentPlan = $agent->currentPlan();
        $this->info("Agent plan: {$agentPlan->name} with referral reward: {$agentPlan->referral_reward}");
        
        // Get a test user
        $user = User::whereNull('agent_id')->first();
        if (!$user) {
            $this->error('No user found without agent referral. Please create a user without agent_id.');
            return 1;
        }
        
        $this->info("Using user: {$user->name} (ID: {$user->id})");
        
        // Get a subscription plan
        $subscriptionPlan = SubscriptionPlan::first();
        $this->info("Using subscription plan: {$subscriptionPlan->name} (Cost: ₹{$subscriptionPlan->cost})");
        
        // Test the reward system
        $this->info("\nTesting reward system...");
        
        // Step 1: Assign user to agent
        $user->agent_id = $agent->id;
        $user->agent_referral_code = $agent->referral_code;
        $user->save();
        
        $this->info("Assigned user to agent");
        
        // Step 2: Get initial wallet balance
        $initialBalance = $agent->wallet_balance;
        $this->info("Agent initial wallet balance: ₹{$initialBalance}");
        
        // Step 3: Test the reward method
        $rewardResult = $user->handleAgentRewardForSubscription($subscriptionPlan);
        
        // Step 4: Check results
        $agent->refresh();
        $newBalance = $agent->wallet_balance;
        $balanceIncrease = $newBalance - $initialBalance;
        
        $this->info("Reward result: " . json_encode($rewardResult, JSON_PRETTY_PRINT));
        $this->info("Agent new wallet balance: ₹{$newBalance}");
        $this->info("Balance increase: ₹{$balanceIncrease}");
        
        if ($rewardResult['rewarded']) {
            $this->info('✅ Agent reward system is working correctly!');
            
            // Check if transaction was logged
            $transaction = \App\Models\AgentWalletTransaction::where('agent_id', $agent->id)
                ->where('description', 'like', '%Subscription plan purchase reward%')
                ->latest()
                ->first();
                
            if ($transaction) {
                $this->info("✅ Transaction logged: {$transaction->description}");
            } else {
                $this->warn("⚠️ Transaction not found in logs");
            }
        } else {
            $this->error('❌ Agent reward system failed: ' . $rewardResult['message']);
        }
        
        // Clean up - remove agent assignment
        $user->agent_id = null;
        $user->agent_referral_code = null;
        $user->save();
        
        $this->info("\nTest completed!");
        return 0;
    }
}
