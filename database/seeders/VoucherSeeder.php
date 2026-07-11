<?php

namespace Database\Seeders;

use App\Models\Voucher;
use Illuminate\Database\Seeder;

class VoucherSeeder extends Seeder
{
    public function run(): void
    {
        $vouchers = [
            ['code' => 'WELCOME10', 'type' => 'manual', 'discount_type' => 'percent', 'discount_value' => 10, 'min_purchase' => 50000, 'max_discount' => 10000, 'valid_from' => now(), 'valid_until' => now()->addMonths(3), 'is_active' => true],
            ['code' => 'HEMAT20', 'type' => 'manual', 'discount_type' => 'percent', 'discount_value' => 20, 'min_purchase' => 100000, 'max_discount' => 25000, 'valid_from' => now(), 'valid_until' => now()->addMonths(1), 'is_active' => true],
            ['code' => 'FLAT5K', 'type' => 'manual', 'discount_type' => 'fixed', 'discount_value' => 5000, 'min_purchase' => 20000, 'max_discount' => null, 'valid_from' => now(), 'valid_until' => now()->addMonths(6), 'is_active' => true],
            ['code' => 'FLAT10K', 'type' => 'manual', 'discount_type' => 'fixed', 'discount_value' => 10000, 'min_purchase' => 50000, 'max_discount' => null, 'valid_from' => now(), 'valid_until' => now()->addMonths(3), 'is_active' => true],
            ['code' => 'PAKETHEMAT', 'type' => 'manual', 'discount_type' => 'percent', 'discount_value' => 15, 'min_purchase' => 75000, 'max_discount' => 15000, 'valid_from' => now(), 'valid_until' => now()->addWeeks(2), 'is_active' => true],
            ['code' => 'ULTAHWM', 'type' => 'birthday', 'discount_type' => 'percent', 'discount_value' => 25, 'min_purchase' => 0, 'max_discount' => 50000, 'valid_from' => now(), 'valid_until' => now()->addYear(), 'is_active' => true],
            ['code' => 'GOLDEN50', 'type' => 'golden_hour', 'discount_type' => 'percent', 'discount_value' => 50, 'min_purchase' => 20000, 'max_discount' => 15000, 'valid_from' => now(), 'valid_until' => now()->addDays(7), 'is_active' => true],
            ['code' => 'NEWYEAR25', 'type' => 'manual', 'discount_type' => 'percent', 'discount_value' => 25, 'min_purchase' => 80000, 'max_discount' => 30000, 'valid_from' => now(), 'valid_until' => now()->addMonths(2), 'is_active' => false],
        ];

        foreach ($vouchers as $data) {
            Voucher::create($data);
        }

        // Generate 22 more vouchers using faker
        $types = ['manual', 'birthday', 'golden_hour'];
        $discountTypes = ['percent', 'fixed'];

        for ($i = 0; $i < 22; $i++) {
            $type = $types[array_rand($types)];
            $discountType = $discountTypes[array_rand($discountTypes)];
            $minPurchase = rand(0, 150000);

            Voucher::create([
                'code' => 'VC'.strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 6)),
                'type' => $type,
                'discount_type' => $discountType,
                'discount_value' => $discountType === 'percent' ? rand(5, 50) : [3000, 5000, 7500, 10000, 15000, 20000][array_rand([3000, 5000, 7500, 10000, 15000, 20000])],
                'min_purchase' => $minPurchase,
                'max_discount' => $discountType === 'percent' ? rand(5000, 50000) : null,
                'valid_from' => now()->subDays(rand(0, 7)),
                'valid_until' => now()->addDays(rand(7, 365)),
                'is_active' => rand(1, 100) <= 80,
            ]);
        }
    }
}
