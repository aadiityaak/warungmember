<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PushSubscription extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'member_id',
        'endpoint',
        'auth',
        'p256dh',
        'fcm_token',
        'platform',
        'user_agent',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
