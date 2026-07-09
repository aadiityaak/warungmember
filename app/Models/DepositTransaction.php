<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DepositTransaction extends Model
{
    public $timestamps = false;

    protected $fillable = ['member_id', 'type', 'amount', 'reference_type', 'reference_id', 'note'];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
