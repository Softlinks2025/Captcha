<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentCommissionTier extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'plan_id',
        'min_referrals',
        'max_referrals',
        'commission_amount',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'min_referrals' => 'integer',
        'max_referrals' => 'integer',
        'commission_amount' => 'decimal:2',
    ];

    /**
     * Get the subscription plan that owns the commission tier.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    /**
     * Get the range display for the commission tier.
     *
     * @return string
     */
    public function getReferralRangeAttribute(): string
    {
        if ($this->max_referrals === null) {
            return $this->min_referrals . '+';
        }
        
        if ($this->min_referrals === $this->max_referrals) {
            return (string) $this->min_referrals;
        }
        
        return $this->min_referrals . ' - ' . $this->max_referrals;
    }
}
