<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentPlanSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'agent_id',
        'plan_id',
        'amount_paid',
        'payment_method',
        'transaction_id',
        'status',
        'started_at',
        'expires_at',
        'total_logins',
        'total_earnings'
    ];

    protected $casts = [
        'amount_paid' => 'decimal:2',
        'total_earnings' => 'decimal:2',
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
        'total_logins' => 'integer'
    ];

    /**
     * Get the agent that owns the subscription
     */
    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    /**
     * Get the plan for this subscription
     */
    public function plan()
    {
        return $this->belongsTo(AgentPlan::class, 'plan_id');
    }

    /**
     * Scope to get only active subscriptions
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Check if subscription is expired
     */
    public function isExpired()
    {
        if ($this->expires_at === null) {
            return false; // Lifetime plan
        }
        
        return now()->isAfter($this->expires_at);
    }

    /**
     * Get current earning rate based on total logins
     */
    public function getCurrentEarningRate()
    {
        return $this->plan->getEarningRate($this->total_logins);
    }

    /**
     * Increment login count and calculate earnings
     */
    public function incrementLogin()
    {
        $this->total_logins++;
        $earningRate = $this->getCurrentEarningRate();
        if ($earningRate === INF) {
            $earningRate = 99999999;
        }
        $this->total_earnings += $earningRate;
        $this->save();

        // Check for bonuses
        $bonus = $this->plan->getBonus($this->total_logins);
        
        return [
            'login_count' => $this->total_logins,
            'earning_rate' => $earningRate,
            'total_earnings' => $this->total_earnings,
            'bonus' => $bonus
        ];
    }
}
