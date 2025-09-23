<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentWithdrawalRequest extends Model
{
    protected $fillable = [
        'agent_id',
        'amount',
        'fee',
        'final_withdrawal_amount',
        'upi_id',
        'account_number',
        'ifsc_code',
        'bank_name',
        'status',
        'request_date',
        'approved_at',
        'admin_id',
        'remarks',
    ];

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }
}