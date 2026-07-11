<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PushSubscription extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'member_id',
        'ntfy_topic',
        'ntfy_token',
        'subscribed',
        'platform',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'subscribed' => 'boolean',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
