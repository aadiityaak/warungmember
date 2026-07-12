<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Broadcast extends Model
{
    protected $fillable = ['type', 'title', 'body', 'data', 'sent_count', 'delivery_log', 'sent_at'];

    protected function casts(): array
    {
        return [
            'data' => 'json',
            'delivery_log' => 'json',
            'sent_at' => 'datetime',
        ];
    }

    public function getReadCount(): int
    {
        return \App\Models\Notification::where('broadcast_id', $this->id)
            ->whereNotNull('read_at')
            ->count();
    }
}
