<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Broadcast extends Model
{
    protected $fillable = ['type', 'title', 'body', 'data', 'sent_count', 'sent_at'];

    protected function casts(): array
    {
        return [
            'data' => 'json',
            'sent_at' => 'datetime',
        ];
    }
}
