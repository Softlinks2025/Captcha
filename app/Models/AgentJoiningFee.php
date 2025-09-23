<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AgentJoiningFee extends Model
{
    protected $fillable = [
        'name',
        'description',
        'amount',
        'validity',
        'features',
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'validity' => 'string',
        'features' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer'
    ];

    public function plans()
    {
        return $this->hasMany(AgentPlan::class);
    }

    public function agents()
    {
        return $this->hasMany(Agent::class, 'joining_fee_plan_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getFormattedAmountAttribute()
    {
        return '₹' . number_format($this->amount, 2);
    }

    public function getValidityInYearsAttribute()
    {
        return $this->validity;
    }
}