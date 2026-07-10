<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = ['user_id', 'outlet_id', 'payment_method', 'status', 'total_amount', 'discount_amount', 'paid_amount', 'change', 'notes', 'member_voucher_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function memberVoucher(): BelongsTo
    {
        return $this->belongsTo(MemberVoucher::class);
    }
}
