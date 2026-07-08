<?php

namespace Database\Seeders;

use App\Models\Reward;
use Illuminate\Database\Seeder;

class RewardSeeder extends Seeder
{
    public function run(): void
    {
        $rewards = [
            [
                'name' => 'Diskon 10% Pembelian',
                'description' => 'Dapatkan diskon 10% untuk semua menu di outlet manapun.',
                'image' => 'https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?w=600&h=400&fit=crop',
                'points_required' => 500,
                'stock' => 50,
                'is_active' => true,
            ],
            [
                'name' => 'Gratis Ayam Geprek',
                'description' => 'Tukarkan poin kamu untuk 1 porsi Ayam Geprek Original gratis!',
                'image' => 'https://images.unsplash.com/photo-1626645738196-c2a7c87a8f58?w=600&h=400&fit=crop',
                'points_required' => 1500,
                'stock' => 30,
                'is_active' => true,
            ],
            [
                'name' => 'Tumbler Eksklusif WM',
                'description' => 'Tumbler stainless steel edisi terbatas dengan logo Warung Mas Mbull.',
                'image' => 'https://images.unsplash.com/photo-1602143407151-7111542de6e8?w=600&h=400&fit=crop',
                'points_required' => 3000,
                'stock' => 20,
                'is_active' => true,
            ],
            [
                'name' => 'Tote Bag WM',
                'description' => 'Tas belanja kain eksklusif Warung Mas Mbull. Stylish dan ramah lingkungan.',
                'image' => 'https://images.unsplash.com/photo-1622560480605-d83c853bc5c3?w=600&h=400&fit=crop',
                'points_required' => 2000,
                'stock' => 25,
                'is_active' => true,
            ],
            [
                'name' => 'Gratis Es Teh Manis',
                'description' => 'Tukar poin kamu dengan 1 gelas Es Teh Manis segar.',
                'image' => 'https://images.unsplash.com/photo-1571934811356-5cc061b6821f?w=600&h=400&fit=crop',
                'points_required' => 300,
                'stock' => 100,
                'is_active' => true,
            ],
            [
                'name' => 'Sticker Pack WM',
                'description' => 'Paket stiker lucu edisi Warung Mas Mbull untuk koleksi kamu.',
                'image' => 'https://images.unsplash.com/photo-1572375992501-4b0892d50c31?w=600&h=400&fit=crop',
                'points_required' => 200,
                'stock' => 200,
                'is_active' => true,
            ],
            [
                'name' => 'Voucher Belanja Rp 10.000',
                'description' => 'Potongan langsung Rp 10.000 untuk pembelian minimal Rp 50.000.',
                'image' => 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?w=600&h=400&fit=crop',
                'points_required' => 1000,
                'stock' => 40,
                'is_active' => true,
            ],
            [
                'name' => 'Kaos Eksklusif WM',
                'description' => 'Kaos cotton premium edisi terbatas Warung Mas Mbull.',
                'image' => 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=600&h=400&fit=crop',
                'points_required' => 5000,
                'stock' => 10,
                'is_active' => false,
            ],
        ];

        foreach ($rewards as $data) {
            Reward::create($data);
        }
    }
}
