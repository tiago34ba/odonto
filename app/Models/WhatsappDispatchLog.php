<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappDispatchLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'scheduling_id',
        'message_type',
        'provider',
        'destination',
        'status',
        'reason',
        'response_code',
        'response_body',
        'context',
        'dispatched_at',
    ];

    protected $casts = [
        'context' => 'array',
        'dispatched_at' => 'datetime',
    ];
}
