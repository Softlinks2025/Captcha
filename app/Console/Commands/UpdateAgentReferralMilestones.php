<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Agent;

class UpdateAgentReferralMilestones extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'agents:update-referral-milestones {--agent-id= : Update specific agent by ID} {--dry-run : Show what would be updated without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update existing agents referral counts and check for milestones';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $agentId = $this->option('agent-id');
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('DRY RUN MODE - No changes will be made');
        }

        $query = Agent::query();
        
        if ($agentId) {
            $query->where('id', $agentId);
            $this->info("Processing agent ID: {$agentId}");
        } else {
            $this->info('Processing all agents...');
        }

        $agents = $query->get();
        $this->info("Found {$agents->count()} agents to process");

        $updatedCount = 0;
        $milestoneCount = 0;

        foreach ($agents as $agent) {
            $this->line("Processing agent: {$agent->name} (ID: {$agent->id})");
            
            $oldReferralCount = $agent->total_referrals;
            $actualReferralCount = $agent->getReferralCount();
            
            $this->line("  Current stored referrals: {$oldReferralCount}");
            $this->line("  Actual referrals: {$actualReferralCount}");
            
            if ($oldReferralCount != $actualReferralCount) {
                $this->line("  ⚠️  Referral count mismatch detected!");
                
                if (!$dryRun) {
                    $agent->total_referrals = $actualReferralCount;
                    $agent->save();
                    
                    // Check for milestones
                    $milestonesReached = $this->checkMilestones($agent, $oldReferralCount, $actualReferralCount);
                    
                    if (!empty($milestonesReached)) {
                        $milestoneCount += count($milestonesReached);
                        $this->line("  🎉 Milestones reached: " . implode(', ', $milestonesReached));
                    }
                } else {
                    $this->line("  Would update referral count to: {$actualReferralCount}");
                    
                    // Check what milestones would be reached
                    $milestonesReached = $this->checkMilestones($agent, $oldReferralCount, $actualReferralCount);
                    if (!empty($milestonesReached)) {
                        $this->line("  Would reach milestones: " . implode(', ', $milestonesReached));
                    }
                }
                
                $updatedCount++;
            } else {
                $this->line("  ✓ Referral count is accurate");
            }
            
            // Show current milestone status
            $this->showMilestoneStatus($agent);
            $this->line('');
        }

        if ($dryRun) {
            $this->info("DRY RUN COMPLETED");
            $this->info("Would update {$updatedCount} agents");
            if ($milestoneCount > 0) {
                $this->info("Would process {$milestoneCount} milestone achievements");
            }
        } else {
            $this->info("COMPLETED");
            $this->info("Updated {$updatedCount} agents");
            if ($milestoneCount > 0) {
                $this->info("Processed {$milestoneCount} milestone achievements");
            }
        }
    }

    /**
     * Check for milestone achievements
     */
    private function checkMilestones($agent, $oldCount, $newCount)
    {
        $milestones = [10, 50, 100];
        $reached = [];
        
        foreach ($milestones as $milestone) {
            if ($oldCount < $milestone && $newCount >= $milestone) {
                $reached[] = $milestone;
                
                if (!$this->option('dry-run')) {
                    $this->processMilestone($agent, $milestone);
                }
            }
        }
        
        return $reached;
    }

    /**
     * Process milestone achievement
     */
    private function processMilestone($agent, $milestone)
    {
        switch ($milestone) {
            case 10:
                if (!$agent->milestone_10_reached) {
                    $agent->milestone_10_reached = true;
                    $agent->applyEarningsCap();
                    $this->line("    Applied earnings cap: ₹{$agent->earnings_cap}");
                }
                break;
                
            case 50:
                if (!$agent->milestone_50_reached) {
                    $agent->milestone_50_reached = true;
                    $this->line("    T-shirt bonus unlocked");
                }
                break;
                
            case 100:
                if (!$agent->milestone_100_reached) {
                    $agent->milestone_100_reached = true;
                    $this->line("    Bag bonus unlocked");
                }
                break;
        }
        
        $agent->save();
    }

    /**
     * Show current milestone status for an agent
     */
    private function showMilestoneStatus($agent)
    {
        $this->line("  Milestone Status:");
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
}
