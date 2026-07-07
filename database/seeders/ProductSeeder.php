<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => 'Ayam Geprek Original',
                'description' => 'Ayam geprek crispy dengan sambal bawang pedas. Disajikan dengan nasi putih dan lalapan segar.',
                'image' => 'https://images.unsplash.com/photo-1626645738196-c2a7c87a8f58?w=600&h=400&fit=crop',
                'price' => 15000,
                'points_earned' => 15,
            ],
            [
                'name' => 'Ayam Geprek Sambal Matah',
                'description' => 'Ayam geprek renyah dengan sambal matah khas Bali. Perpaduan pedas dan segar yang menggugah selera.',
                'image' => 'https://images.unsplash.com/photo-1604909052743-94e838986d24?w=600&h=400&fit=crop',
                'price' => 18000,
                'points_earned' => 18,
            ],
            [
                'name' => 'Ayam Geprek Mozzarella',
                'description' => 'Ayam geprek crispy dengan lelehan keju mozzarella. Gurih, pedas, dan creamy dalam satu gigitan.',
                'image' => 'https://images.unsplash.com/photo-1562967914-608f82629710?w=600&h=400&fit=crop',
                'price' => 22000,
                'points_earned' => 22,
            ],
            [
                'name' => 'Ayam Geprek Sambal Bawang',
                'description' => 'Ayam geprek dengan sambal bawang melimpah. Extra pedas dengan aroma bawang yang khas.',
                'image' => 'https://images.unsplash.com/photo-1606755962773-d324e0a13086?w=600&h=400&fit=crop',
                'price' => 17000,
                'points_earned' => 17,
            ],
            [
                'name' => 'Lele Penyet',
                'description' => 'Lele goreng crispy yang dipenyet dengan sambal terasi. Disajikan dengan nasi hangat dan lalapan.',
                'image' => 'https://images.unsplash.com/photo-1559847844-5315695dadae?w=600&h=400&fit=crop',
                'price' => 16000,
                'points_earned' => 16,
            ],
            [
                'name' => 'Nasi Putih',
                'description' => 'Nasi putih pulen hangat untuk pendamping sempurna.',
                'image' => 'https://images.unsplash.com/photo-1598515214211-89d3c73ae83b?w=600&h=400&fit=crop',
                'price' => 5000,
                'points_earned' => 5,
            ],
            [
                'name' => 'Es Teh Manis',
                'description' => 'Teh manis segar dengan es batu. Pelepas dahaga yang pas untuk makanan pedas.',
                'image' => 'https://images.unsplash.com/photo-1571934811356-5cc061b6821f?w=600&h=400&fit=crop',
                'price' => 5000,
                'points_earned' => 5,
            ],
            [
                'name' => 'Tempe Goreng',
                'description' => 'Tempe goreng tepung renyah. Bumbu meresap sampai ke dalam. Camilan atau lauk favorit.',
                'image' => 'https://images.unsplash.com/photo-1590034231428-f1db5e57eff1?w=600&h=400&fit=crop',
                'price' => 3000,
                'points_earned' => 3,
            ],
            [
                'name' => 'Tahu Goreng',
                'description' => 'Tahu putih goreng crispy di luar lembut di dalam. Pelengkap sempurna ayam geprek.',
                'image' => 'https://images.unsplash.com/photo-1541696432-82c6da8ce7bf?w=600&h=400&fit=crop',
                'price' => 3000,
                'points_earned' => 3,
            ],
            [
                'name' => 'Ayam Geprek Keju',
                'description' => 'Ayam geprek dengan taburan keju parut melimpah. Pedas bertemu gurih yang bikin nagih.',
                'image' => 'https://images.unsplash.com/photo-1595219198098-62b8dad4b6a3?w=600&h=400&fit=crop',
                'price' => 20000,
                'points_earned' => 20,
            ],
            [
                'name' => 'Paket Hemat Ayam Geprek',
                'description' => 'Paket 1 ayam geprek + nasi + es teh manis. Pilihan favorit makan siang.',
                'image' => 'https://images.unsplash.com/photo-1604570636833-0351e4b83607?w=600&h=400&fit=crop',
                'price' => 20000,
                'points_earned' => 25,
            ],
            [
                'name' => 'Paket Double Geprek',
                'description' => '2 ayam geprek + 2 nasi + 2 es teh. Cocok buat makan berdua, lebih hemat!',
                'image' => 'https://images.unsplash.com/photo-1626645738196-c2a7c87a8f58?w=600&h=400&fit=crop',
                'price' => 35000,
                'points_earned' => 40,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
