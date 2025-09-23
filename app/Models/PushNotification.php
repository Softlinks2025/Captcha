<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PushNotification extends Model
{
    protected $fillable = [
        'title',
        'message',
        'image_path',
        'recipient_type',
        'recipient_id',
        'sent_at',
        'status',
    ];
    public $timestamps = true;
} 