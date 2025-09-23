<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentPlan extends Model
{
    use HasFactory;

    protected $table = 'agent_plans';

    protected $fillable = [
        'name',
        'description',
        'icon',
        'price',
        'duration',
        'is_active',
        'rate_1_50',
        'rate_51_100',
        'rate_after_100',
        'bonus_10_logins',
        'bonus_50_logins',
        'bonus_100_logins',
        'min_withdrawal',
        'max_withdrawal',
        'withdrawal_time',
        'unlimited_earning',
        'unlimited_logins',
        'max_logins_per_day',
        'sort_order',
        'earning_ranges',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'min_withdrawal' => 'decimal:2',
        'max_withdrawal' => 'decimal:2',
        'is_active' => 'boolean',
        'unlimited_earning' => 'boolean',
        'unlimited_logins' => 'boolean',
        'sort_order' => 'integer',
        'max_logins_per_day' => 'integer',
    ];

    /**
     * Get the subscriptions for this plan
     */
    public function subscriptions()
    {
        return $this->hasMany(AgentPlanSubscription::class, 'plan_id');
    }

    /**
     * Get active subscriptions for this plan
     */
    public function activeSubscriptions()
    {
        return $this->subscriptions()->where('status', 'active');
    }

    /**
     * Scope to get only active plans
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the earning rate based on login count
     */
    public function getEarningRate($loginCount)
    {
        // If any earning rate is 'unlimited', return a very high value (or handle as needed)
        if (strtolower((string)$this->rate_1_50) === 'unlimited' || strtolower((string)$this->rate_51_100) === 'unlimited' || strtolower((string)$this->rate_after_100) === 'unlimited') {
            return INF; // or a very high value
        }
        if ($loginCount <= 50) {
            return $this->rate_1_50;
        } elseif ($loginCount <= 100) {
            return $this->rate_51_100;
        } else {
            return $this->rate_after_100;
        }
    }

    /**
     * Get bonus for specific login count
     */
    public function getBonus($loginCount)
    {
        if ($loginCount == 10) {
            return $this->bonus_10_logins;
        } elseif ($loginCount == 50) {
            return $this->bonus_50_logins;
        } elseif ($loginCount == 100) {
            return $this->bonus_100_logins;
        }
        
        return null;
    }

    /**
     * Check if agent has unlimited referrals
     */
    public function hasUnlimitedReferrals()
    {
        return false; // Referral reward is no longer tracked in the model
    }
}
