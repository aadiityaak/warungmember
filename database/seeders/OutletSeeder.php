<?php

namespace Database\Seeders;

use App\Models\Outlet;
use App\Models\User;
use Illuminate\Database\Seeder;

class OutletSeeder extends Seeder
{
    public function run(): void
    {
        $kasirs = [
            ['name' => 'Kasir Klaten Utara', 'email' => 'kasir.klaten@warungmember.test'],
            ['name' => 'Kasir Wedi', 'email' => 'kasir.wedi@warungmember.test'],
            ['name' => 'Kasir Pedan', 'email' => 'kasir.pedan@warungmember.test'],
            ['name' => 'Kasir Mojosongo', 'email' => 'kasir.mojosongo@warungmember.test'],
            ['name' => 'Kasir Kantor', 'email' => 'kasir.kantor@warungmember.test'],
        ];

        $outlets = [
            [
                'name' => 'Warung Mas Mbull (Pusat/Klaten Utara)',
                'address' => 'Jl. Mayor Kusmanto, Pondok, Gergunung, Kec. Klaten Utara, Klaten, Jateng 57428',
                'phone' => '0813-3540-5231',
                'latitude' => -7.6896356,
                'longitude' => 110.604709,
                'is_active' => true,
                'kasir_index' => 0,
            ],
            [
                'name' => 'Warung Mas Mbull Wedi',
                'address' => 'Jl. Raya Sel., Dusun II, Gadungan, Kec. Wedi, Klaten, Jateng 57461',
                'phone' => '0823-2334-3355',
                'latitude' => -7.7504551,
                'longitude' => 110.5800595,
                'is_active' => true,
                'kasir_index' => 1,
            ],
            [
                'name' => 'Warung Mas Mbull Pedan',
                'address' => 'Lapangan, Kedungbaru, Kedungan, Kec. Pedan, Klaten, Jateng 57468',
                'phone' => '0811-2954-866',
                'latitude' => -7.6916104,
                'longitude' => 110.702643,
                'is_active' => true,
                'kasir_index' => 2,
            ],
            [
                'name' => 'Warung Mas Mbull Mojosongo',
                'address' => 'Jl. Jaya Wijaya No.175, Mojosongo, Kec. Jebres, Surakarta, Jateng 57127',
                'phone' => '0822-2429-2039',
                'latitude' => -7.5342837,
                'longitude' => 110.8349218,
                'is_active' => true,
                'kasir_index' => 3,
            ],
            [
                'name' => 'Kantor Warung Mas Mbull',
                'address' => 'Ngingas Baru, Gg. Ketapang Perak YKP, RT.03/RW.04, Bareng Lor, Kec. Klaten Utara, Klaten, Jateng 57438',
                'phone' => '0823-2334-6727',
                'latitude' => -7.6901272,
                'longitude' => 110.6058317,
                'is_active' => true,
                'kasir_index' => 4,
            ],
        ];

        foreach ($outlets as $data) {
            $kasirData = $kasirs[$data['kasir_index']];
            unset($data['kasir_index']);

            $kasir = User::firstOrCreate(
                ['email' => $kasirData['email']],
                [
                    'name' => $kasirData['name'],
                    'password' => bcrypt('password'),
                    'role' => 'kasir',
                    'email_verified_at' => now(),
                ]
            );

            $data['user_id'] = $kasir->id;

            // Assign gallery
            $galleryItems = [
                'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1552566626-52f8b828add9?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1466978913421-dad2ebd01d17?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1514933651103-005eec06c04b?w=800&h=600&fit=crop',
                'https://images.unsplash.com/photo-1559339352-11d035aa65de?w=800&h=600&fit=crop',
            ];
            $randKeys = array_rand($galleryItems, rand(3, 5));
            $data['gallery'] = array_map(fn($k) => $galleryItems[$k], $randKeys);

            Outlet::create($data);
        }
    }
}
