<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Voucher extends Model
{
    protected $fillable = ['code', 'type', 'discount_type', 'discount_value', 'min_purchase', 'max_discount', 'valid_from', 'valid_until', 'is_active'];

    protected function casts(): array
    {
        return [
            'valid_from' => 'datetime',
            'valid_until' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function memberVouchers(): HasMany
    {
        return $this->hasMany(MemberVoucher::class);
    }

    public static function booted(): void
    {
        static::creating(function (Voucher $voucher) {
            if (empty($voucher->code)) {
                $voucher->code = 'VC'.strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
            }
        });
    }
}
