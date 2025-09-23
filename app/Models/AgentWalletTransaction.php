<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentWalletTransaction extends Model
{
    protected $fillable = [
        'agent_id',
        'amount',
        'type',
        'description',
    ];

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }
} 