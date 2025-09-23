<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Agent;

class WithdrawalRequest extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'amount',
        'fee',
        'final_withdrawal_amount',
        'upi_id',
        'service_type',
        'status',
        'request_date',
        'approved_at',
        'admin_id',
        'remarks',
        'account_number',
        'ifsc_code',
        'bank_name',
        'agent_id',
    ];

    /**
     * Get the user that owns the withdrawal request.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who approved the withdrawal request.
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Get the agent that owns the withdrawal request.
     */
    public function agent()
    {
        return $this->belongsTo(Agent::class, 'agent_id');
    }
}
