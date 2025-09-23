<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentPlanPurchaseRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'agent_id',
        'plan_id',
        'status',
        'requested_at',
        'approved_at',
        'rejected_at',
        'admin_note',
    ];

    public function agent() {
        return $this->belongsTo(Agent::class);
    }
    public function plan() {
        return $this->belongsTo(AgentPlan::class, 'plan_id');
    }
} 