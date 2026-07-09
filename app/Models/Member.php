<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Member extends Model
{
    protected $fillable = ['user_id', 'member_code', 'birth_date', 'last_outlet_id'];

    protected $guarded = ['total_points', 'deposit_balance'];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'total_points' => 'integer',
            'deposit_balance' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pointTransactions(): HasMany
    {
        return $this->hasMany(PointTransaction::class);
    }

    public function rewardRedemptions(): HasMany
    {
        return $this->hasMany(RewardRedemption::class);
    }

    public function depositTransactions(): HasMany
    {
        return $this->hasMany(DepositTransaction::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function memberVouchers(): HasMany
    {
        return $this->hasMany(MemberVoucher::class);
    }

    public static function booted(): void
    {
        static::creating(function (Member $member) {
            if (empty($member->member_code)) {
                $member->member_code = 'WM'.strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
            }
        });
    }
}
