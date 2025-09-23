<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'cost',
        'duration_days',
        'captcha_per_day',
        'caption_limit',
        'earnings',
        'status',
        'captchas_per_level',
        'reward_per_level',
        'referral_earnings',
        'bonus_10_referrals',
        'bonus_20_referrals',
        'daily_limit_bonus',
        'unlimited_earning_rate',
        'gift_10_referrals',
        'gift_20_referrals',
        'referral_earnings_type',
        'referral_earnings_value',
        'referral_earning_per_ref',
        'min_daily_earning',
        'daily_captcha_earning_with_ref',
        'min_withdrawal_limit',
        'plan_type',
        'earning_type',
        'is_unlimited',
        'caption_limit',
    ];

    /**
     * Get all of the commission tiers for the subscription plan.
     */
    public function agentCommissionTiers(): HasMany
    {
        return $this->hasMany(AgentCommissionTier::class, 'plan_id');
    }

    protected $casts = [
        'reward_per_level' => 'decimal:2',
        'earnings' => 'array',
        'cost' => 'decimal:2',
        'referral_earning_per_ref' => 'decimal:2',
        'daily_captcha_earning_with_ref' => 'decimal:2',
        'bonus_10_referrals' => 'decimal:2',
        'bonus_20_referrals' => 'decimal:2',
        'daily_limit_bonus' => 'decimal:2',
        'unlimited_earning_rate' => 'decimal:2',
        'gift_10_referrals' => 'string',
        'gift_20_referrals' => 'string',
        'referral_earning_per_ref' => 'decimal:2',
        'min_daily_earning' => 'decimal:2',
        'daily_captcha_earning_with_ref' => 'decimal:2',
        'min_withdrawal_limit' => 'decimal:2',
        'plan_type' => 'string',
        'earning_type' => 'string',
        'is_unlimited' => 'boolean',
        'caption_limit' => 'string',
    ];

    /**
     * Get the users associated with this subscription plan.
     */
    public function users()
    {
        return $this->hasMany(User::class, 'subscription_name', 'name');
    }
}
