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
                'is_active' => true,
                'kasir_index' => 0,
            ],
            [
                'name' => 'Warung Mas Mbull Wedi',
                'address' => 'Jl. Raya Sel., Dusun II, Gadungan, Kec. Wedi, Klaten, Jateng 57461',
                'phone' => '0823-2334-3355',
                'is_active' => true,
                'kasir_index' => 1,
            ],
            [
                'name' => 'Warung Mas Mbull Pedan',
                'address' => 'Lapangan, Kedungbaru, Kedungan, Kec. Pedan, Klaten, Jateng 57468',
                'phone' => '0811-2954-866',
                'is_active' => true,
                'kasir_index' => 2,
            ],
            [
                'name' => 'Warung Mas Mbull Mojosongo',
                'address' => 'Jl. Jaya Wijaya No.175, Mojosongo, Kec. Jebres, Surakarta, Jateng 57127',
                'phone' => '0822-2429-2039',
                'is_active' => true,
                'kasir_index' => 3,
            ],
            [
                'name' => 'Kantor Warung Mas Mbull',
                'address' => 'Ngingas Baru, Gg. Ketapang Perak YKP, RT.03/RW.04, Bareng Lor, Kec. Klaten Utara, Klaten, Jateng 57438',
                'phone' => '0823-2334-6727',
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
            Outlet::create($data);
        }
    }
}
