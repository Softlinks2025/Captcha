<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'user_id',
        'agent_id',
        'order_id',
        'payment_id',
        'signature',
        'purpose',
        'amount',
        'currency',
        'status', // e.g. created, paid, failed
        'meta', // json for extra data
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }
} 