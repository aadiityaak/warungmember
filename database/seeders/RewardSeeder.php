<?php

namespace Database\Seeders;

use App\Models\Reward;
use Illuminate\Database\Seeder;

class RewardSeeder extends Seeder
{
    public function run(): void
    {
        $rewards = [
            ['name' => 'Diskon 10% Pembelian', 'description' => 'Dapatkan diskon 10% untuk semua menu di outlet manapun.', 'image' => 'https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?w=600&h=400&fit=crop', 'points_required' => 500, 'stock' => 50, 'is_active' => true],
            ['name' => 'Gratis Ayam Geprek', 'description' => 'Tukarkan poin kamu untuk 1 porsi Ayam Geprek Original gratis!', 'image' => 'https://images.unsplash.com/photo-1626645738196-c2a7c87a8f58?w=600&h=400&fit=crop', 'points_required' => 1500, 'stock' => 30, 'is_active' => true],
            ['name' => 'Tumbler Eksklusif WM', 'description' => 'Tumbler stainless steel edisi terbatas dengan logo Warung Mas Mbull.', 'image' => 'https://images.unsplash.com/photo-1602143407151-7111542de6e8?w=600&h=400&fit=crop', 'points_required' => 3000, 'stock' => 20, 'is_active' => true],
            ['name' => 'Tote Bag WM', 'description' => 'Tas belanja kain eksklusif Warung Mas Mbull. Stylish dan ramah lingkungan.', 'image' => 'https://images.unsplash.com/photo-1622560480605-d83c853bc5c3?w=600&h=400&fit=crop', 'points_required' => 2000, 'stock' => 25, 'is_active' => true],
            ['name' => 'Gratis Es Teh Manis', 'description' => 'Tukar poin kamu dengan 1 gelas Es Teh Manis segar.', 'image' => 'https://images.unsplash.com/photo-1571934811356-5cc061b6821f?w=600&h=400&fit=crop', 'points_required' => 300, 'stock' => 100, 'is_active' => true],
            ['name' => 'Sticker Pack WM', 'description' => 'Paket stiker lucu edisi Warung Mas Mbull untuk koleksi kamu.', 'image' => 'https://images.unsplash.com/photo-1572375992501-4b0892d50c31?w=600&h=400&fit=crop', 'points_required' => 200, 'stock' => 200, 'is_active' => true],
            ['name' => 'Voucher Belanja Rp 10.000', 'description' => 'Potongan langsung Rp 10.000 untuk pembelian minimal Rp 50.000.', 'image' => 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?w=600&h=400&fit=crop', 'points_required' => 1000, 'stock' => 40, 'is_active' => true],
            ['name' => 'Kaos Eksklusif WM', 'description' => 'Kaos cotton premium edisi terbatas Warung Mas Mbull.', 'image' => 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=600&h=400&fit=crop', 'points_required' => 5000, 'stock' => 10, 'is_active' => false],
        ];

        foreach ($rewards as $data) {
            Reward::create($data);
        }

        // Generate 22 more rewards using faker
        $rewardItems = [
            ['name' => 'Gratis Ayam Geprek Sambal Matah', 'desc' => '1 porsi Ayam Geprek Sambal Matah gratis.'],
            ['name' => 'Gratis Lele Penyet', 'desc' => '1 porsi Lele Penyet gratis dengan sambal terasi.'],
            ['name' => 'Gratis Nasi Putih', 'desc' => '1 porsi Nasi Putih hangat gratis.'],
            ['name' => 'Gratis Tempe Goreng', 'desc' => '2 potong Tempe Goreng tepung gratis.'],
            ['name' => 'Gratis Tahu Goreng', 'desc' => '2 potong Tahu Goreng crispy gratis.'],
            ['name' => 'Diskon 20% Pembelian', 'desc' => 'Diskon 20% untuk total pembelian di outlet manapun.'],
            ['name' => 'Diskon 15% Pembelian', 'desc' => 'Diskon 15% untuk semua menu di outlet manapun.'],
            ['name' => 'Voucher Belanja Rp 25.000', 'desc' => 'Potongan langsung Rp 25.000 untuk pembelian minimal Rp 75.000.'],
            ['name' => 'Voucher Belanja Rp 50.000', 'desc' => 'Potongan langsung Rp 50.000 untuk pembelian minimal Rp 150.000.'],
            ['name' => 'Paket Hemat Gratis', 'desc' => '1 Paket Hemat Ayam Geprek gratis untuk kamu.'],
            ['name' => 'Mug Eksklusif WM', 'desc' => 'Mug keramik edisi terbatas Warung Mas Mbull.'],
            ['name' => 'Topi WM', 'desc' => 'Topi baseball keren dengan logo Warung Mas Mbull.'],
            ['name' => 'Gantungan Kunci WM', 'desc' => 'Gantungan kunci lucu edisi Warung Mas Mbull.'],
            ['name' => 'Notebook WM', 'desc' => 'Buku catatan kecil edisi Warung Mas Mbull.'],
            ['name' => 'Pulpen WM', 'desc' => 'Pulpen eksklusif Warung Mas Mbull.'],
            ['name' => 'Lunch Box WM', 'desc' => 'Kotak makan plastik premium Warung Mas Mbull.'],
            ['name' => 'Botol Minum WM', 'desc' => 'Botol minum BPA-free edisi Warung Mas Mbull.'],
            ['name' => 'Payung WM', 'desc' => 'Payung lipat branded Warung Mas Mbull.'],
            ['name' => 'Handuk Kecil WM', 'desc' => 'Handuk microfiber edisi Warung Mas Mbull.'],
            ['name' => 'Gratis Es Jeruk', 'desc' => '1 gelas Es Jeruk segar gratis.'],
            ['name' => 'Diskon 30% Weekend', 'desc' => 'Diskon 30% untuk pembelian di hari Sabtu-Minggu.'],
            ['name' => 'Cashback Rp 5.000', 'desc' => 'Cashback Rp 5.000 untuk pembelian berikutnya.'],
        ];

        $images = [
            'https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?w=600&h=400&fit=crop',
            'https://images.unsplash.com/photo-1626645738196-c2a7c87a8f58?w=600&h=400&fit=crop',
            'https://images.unsplash.com/photo-1602143407151-7111542de6e8?w=600&h=400&fit=crop',
            'https://images.unsplash.com/photo-1622560480605-d83c853bc5c3?w=600&h=400&fit=crop',
            'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?w=600&h=400&fit=crop',
            'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=600&h=400&fit=crop',
        ];

        foreach ($rewardItems as $item) {
            Reward::create([
                'name' => $item['name'],
                'description' => $item['desc'],
                'image' => fake()->randomElement($images),
                'points_required' => fake()->numberBetween(150, 10000),
                'stock' => fake()->numberBetween(5, 150),
                'is_active' => fake()->boolean(85),
            ]);
        }
    }
}
