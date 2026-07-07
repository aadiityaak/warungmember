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
            ['name' => 'Kasir Warung Mbak Sri', 'email' => 'kasir1@warungmember.test', 'password' => 'password'],
            ['name' => 'Kasir Warung Mbak Ani', 'email' => 'kasir2@warungmember.test', 'password' => 'password'],
            ['name' => 'Kasir Warung Mas Budi', 'email' => 'kasir3@warungmember.test', 'password' => 'password'],
            ['name' => 'Kasir Warung Mbak Dewi', 'email' => 'kasir4@warungmember.test', 'password' => 'password'],
            ['name' => 'Kasir Warung Mas Eko', 'email' => 'kasir5@warungmember.test', 'password' => 'password'],
        ];

        $outlets = [
            [
                'name' => 'Warung Mas Mbull - Pasar Minggu',
                'address' => 'Jl. Pasar Minggu No. 42, Jakarta Selatan',
                'phone' => '0812-1111-1001',
                'is_active' => true,
                'kasir_index' => 0,
            ],
            [
                'name' => 'Warung Mas Mbull - Depok',
                'address' => 'Jl. Margonda Raya No. 88, Depok',
                'phone' => '0812-1111-1002',
                'is_active' => true,
                'kasir_index' => 1,
            ],
            [
                'name' => 'Warung Mas Mbull - Bogor',
                'address' => 'Jl. Pajajaran No. 15, Bogor',
                'phone' => '0812-1111-1003',
                'is_active' => true,
                'kasir_index' => 2,
            ],
            [
                'name' => 'Warung Mas Mbull - Tangerang',
                'address' => 'Jl. MH Thamrin No. 77, Tangerang',
                'phone' => '0812-1111-1004',
                'is_active' => true,
                'kasir_index' => 3,
            ],
            [
                'name' => 'Warung Mas Mbull - Bekasi',
                'address' => 'Jl. Ahmad Yani No. 55, Bekasi',
                'phone' => '0812-1111-1005',
                'is_active' => false,
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
                    'password' => bcrypt($kasirData['password']),
                    'role' => 'kasir',
                    'email_verified_at' => now(),
                ]
            );

            $data['user_id'] = $kasir->id;

            Outlet::create($data);
        }
    }
}
