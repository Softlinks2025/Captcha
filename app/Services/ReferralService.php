<?php

namespace App\Services;

use App\Models\User;
use App\Models\SubscriptionPlan;
use App\Models\WalletTransaction;
use App\Models\UserReferral;
use Illuminate\Support\Facades\DB;

class ReferralService
{
    /**
     * Process referral earnings when a new user is referred
     */
    public function processReferralEarning(User $referrer, User $referredUser)
    {
        // Get referrer's subscription plan
        $subscriptionPlan = $this->getUserSubscriptionPlan($referrer);
        
        if (!$subscriptionPlan) {
            return false;
        }

        $referralAmount = $subscriptionPlan->referral_earning_per_ref;
        
        if ($referralAmount <= 0) {
            return false;
        }

        // Create wallet transaction for referral earning
        WalletTransaction::create([
            'user_id' => $referrer->id,
            'amount' => $referralAmount,
            'type' => 'referral_earning',
            'description' => "Referral earning from user {$referredUser->name}",
        ]);
       

        // Update referrer's wallet balance
        $referrer->increment('wallet_balance', $referralAmount);
         /* 
         // Send notification about milestone bonus
    try {
        if ($user->fcm_token) {
            $title = "🎉 {$milestone} Referrals Milestone!";
            $message = "Congratulations! You've received a ₹{$bonusAmount} bonus for reaching {$milestone} referrals.";
            if ($gift) {
                $message .= " You've also unlocked a special gift: {$gift}";
            }
            
            \App\Helpers\FcmHelper::sendV1($user->fcm_token, $title, $message);
            
            \Log::info('Milestone notification sent to user', [
                'user_id' => $user->id,
                'milestone' => $milestone,
                'bonus_amount' => $bonusAmount
            ]);
        }
    } catch (\Exception $e) {
        \Log::error('Failed to send milestone notification', [
            'user_id' => $user->id,
            'milestone' => $milestone,
            'error' => $e->getMessage()
        ]);
        */

        // Check for milestone bonuses
        $this->checkMilestoneBonuses($referrer);

        return true;
    }

    /**
     * Check and process milestone bonuses (10 and 20 referrals)
     */
    public function checkMilestoneBonuses(User $user)
    {
        $subscriptionPlan = $this->getUserSubscriptionPlan($user);
        
        if (!$subscriptionPlan) {
            return;
        }

        $totalReferrals = UserReferral::where('referrer_id', $user->id)->count();

        // Check 10 referrals milestone
        if ($totalReferrals == 10 && $subscriptionPlan->bonus_10_referrals > 0) {
            $this->processMilestoneBonus($user, 10, $subscriptionPlan->bonus_10_referrals, $subscriptionPlan->gift_10_referrals);
        }

        // Check 20 referrals milestone
        if ($totalReferrals == 20 && $subscriptionPlan->bonus_20_referrals > 0) {
            $this->processMilestoneBonus($user, 20, $subscriptionPlan->bonus_20_referrals, $subscriptionPlan->gift_20_referrals);
        }

    }

    /**
     * Process milestone bonus
     */
    private function processMilestoneBonus(User $user, int $milestone, float $bonusAmount, ?string $gift)
    {
        // Create wallet transaction for milestone bonus
        WalletTransaction::create([
            'user_id' => $user->id,
            'amount' => $bonusAmount,
            'type' => 'milestone_bonus',
            'description' => "Milestone bonus for {$milestone} referrals" . ($gift ? " + {$gift}" : ''),
        ]);

        // Update user's wallet balance
        $user->increment('wallet_balance', $bonusAmount);

        // TODO: Send notification about milestone bonus and gift
    }

    /**
     * Process daily captcha earning with referral bonus
     */
    public function processDailyCaptchaEarning(User $user, float $baseEarning)
    {
        $subscriptionPlan = $this->getUserSubscriptionPlan($user);
        
        if (!$subscriptionPlan) {
            return $baseEarning;
        }

        // Check if user has referrals
        $hasReferrals = UserReferral::where('referrer_id', $user->id)->exists();
        
        if ($hasReferrals && $subscriptionPlan->daily_captcha_earning_with_ref > 0) {
            $bonusAmount = $subscriptionPlan->daily_captcha_earning_with_ref - $baseEarning;
            
            if ($bonusAmount > 0) {
                // Create wallet transaction for referral bonus
                WalletTransaction::create([
                    'user_id' => $user->id,
                    'amount' => $bonusAmount,
                    'type' => 'referral_daily_bonus',
                    'description' => 'Daily captcha earning bonus with referrals',
                ]);

                // Update user's wallet balance
                $user->increment('wallet_balance', $bonusAmount);
                
                return $subscriptionPlan->daily_captcha_earning_with_ref;
            }
        }

        return $baseEarning;
    }

    /**
     * Process daily limit bonus
     */
    public function processDailyLimitBonus(User $user, int $captchaCount)
    {
        $subscriptionPlan = $this->getUserSubscriptionPlan($user);
        
        if (!$subscriptionPlan || $subscriptionPlan->daily_limit_bonus <= 0) {
            return false;
        }

        $dailyLimit = $this->getDailyCaptchaLimit($subscriptionPlan);
        
        // Check if user reached daily limit
        if ($captchaCount >= $dailyLimit) {
            // Create wallet transaction for daily limit bonus
            WalletTransaction::create([
                'user_id' => $user->id,
                'amount' => $subscriptionPlan->daily_limit_bonus,
                'type' => 'daily_limit_bonus',
                'description' => "Daily limit bonus for completing {$dailyLimit} captchas",
            ]);

            // Update user's wallet balance
            $user->increment('wallet_balance', $subscriptionPlan->daily_limit_bonus);
            
            return true;
        }

        return false;
    }

    /**
     * Calculate earning for unlimited plans
     */
    public function calculateUnlimitedEarning(User $user, int $captchaCount)
    {
        $subscriptionPlan = $this->getUserSubscriptionPlan($user);
        
        if (!$subscriptionPlan || !$subscriptionPlan->is_unlimited) {
            return 0;
        }

        $earnings = json_decode($subscriptionPlan->earnings, true) ?: [];
        $totalEarning = 0;

        // Calculate earning based on ranges
        foreach ($earnings as $range => $rate) {
            if ($range === 'after_300') {
                // For unlimited plans, after 300 captchas, use unlimited earning rate
                if ($captchaCount > 300) {
                    $remainingCaptchas = $captchaCount - 300;
                    $totalEarning += $remainingCaptchas * $subscriptionPlan->unlimited_earning_rate;
                }
            } else {
                // Parse range like "1-100"
                $rangeParts = explode('-', $range);
                if (count($rangeParts) == 2) {
                    $min = (int)$rangeParts[0];
                    $max = (int)$rangeParts[1];
                    
                    if ($captchaCount >= $min && $captchaCount <= $max) {
                        $captchasInRange = min($captchaCount - $min + 1, $max - $min + 1);
                        $totalEarning += $captchasInRange * $rate;
                    } elseif ($captchaCount > $max) {
                        $totalEarning += ($max - $min + 1) * $rate;
                    }
                }
            }
        }

        return $totalEarning;
    }

    /**
     * Get user's subscription plan
     */
    private function getUserSubscriptionPlan(User $user)
    {
        if (!$user->subscription_name) {
            return null;
        }

        return SubscriptionPlan::where('name', $user->subscription_name)->first();
    }

    /**
     * Get daily captcha limit for a subscription plan
     */
    private function getDailyCaptchaLimit(SubscriptionPlan $plan)
    {
        if ($plan->is_unlimited) {
            return PHP_INT_MAX; // Unlimited
        }

        return (int)$plan->captcha_per_day;
    }

    /**
     * Get referral statistics for a user
     */
    public function getReferralStats(User $user)
    {
        $totalReferrals = UserReferral::where('referrer_id', $user->id)->count();
        $activeReferrals = UserReferral::where('referrer_id', $user->id)
            ->whereHas('referredUser', function($query) {
                $query->where('is_verified', true);
            })->count();

        $totalEarnings = WalletTransaction::where('user_id', $user->id)
            ->whereIn('type', ['referral_earning', 'milestone_bonus', 'referral_daily_bonus'])
            ->sum('amount');

        return [
            'total_referrals' => $totalReferrals,
            'active_referrals' => $activeReferrals,
            'total_earnings' => $totalEarnings,
            'next_milestone' => $this->getNextMilestone($totalReferrals),
        ];
    }

    /**
     * Get next milestone
     */
    private function getNextMilestone(int $currentReferrals)
    {
        if ($currentReferrals < 10) {
            return 10;
        } elseif ($currentReferrals < 20) {
            return 20;
        } else {
            return null; // No more milestones
        }
    }
} 