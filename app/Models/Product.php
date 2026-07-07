<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'description', 'image', 'price', 'discount_price',
        'discount_end_at', 'points_earned', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'price' => 'integer',
            'discount_price' => 'integer',
            'discount_end_at' => 'datetime',
            'points_earned' => 'integer',
        ];
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(ProductCategory::class, 'product_category');
    }

    public function getCurrentPriceAttribute(): int
    {
        if (! $this->discount_price) {
            return $this->price;
        }
        if ($this->discount_end_at && now()->gt($this->discount_end_at)) {
            return $this->price;
        }

        return $this->discount_price;
    }

    public function getIsOnDiscountAttribute(): bool
    {
        return $this->currentPrice < $this->price;
    }
}
