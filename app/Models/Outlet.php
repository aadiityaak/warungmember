<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Outlet extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'address', 'phone', 'gallery', 'is_active', 'user_id'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'gallery' => 'array',
        ];
    }

    public function kasir(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
