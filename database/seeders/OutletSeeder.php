<?php

namespace Database\Seeders;

use App\Models\Outlet;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OutletSeeder extends Seeder
{
    public function run(): void
    {
        $originalKasirs = [
            ['name' => 'Kasir Warung Mbak Sri', 'email' => 'kasir1@warungmember.test', 'password' => 'password'],
            ['name' => 'Kasir Warung Mbak Ani', 'email' => 'kasir2@warungmember.test', 'password' => 'password'],
            ['name' => 'Kasir Warung Mas Budi', 'email' => 'kasir3@warungmember.test', 'password' => 'password'],
            ['name' => 'Kasir Warung Mbak Dewi', 'email' => 'kasir4@warungmember.test', 'password' => 'password'],
            ['name' => 'Kasir Warung Mas Eko', 'email' => 'kasir5@warungmember.test', 'password' => 'password'],
        ];

        $originalOutlets = [
            ['name' => 'Warung Mas Mbull - Pasar Minggu', 'address' => 'Jl. Pasar Minggu No. 42, Jakarta Selatan', 'phone' => '0812-1111-1001', 'is_active' => true, 'kasir_index' => 0],
            ['name' => 'Warung Mas Mbull - Depok', 'address' => 'Jl. Margonda Raya No. 88, Depok', 'phone' => '0812-1111-1002', 'is_active' => true, 'kasir_index' => 1],
            ['name' => 'Warung Mas Mbull - Bogor', 'address' => 'Jl. Pajajaran No. 15, Bogor', 'phone' => '0812-1111-1003', 'is_active' => true, 'kasir_index' => 2],
            ['name' => 'Warung Mas Mbull - Tangerang', 'address' => 'Jl. MH Thamrin No. 77, Tangerang', 'phone' => '0812-1111-1004', 'is_active' => true, 'kasir_index' => 3],
            ['name' => 'Warung Mas Mbull - Bekasi', 'address' => 'Jl. Ahmad Yani No. 55, Bekasi', 'phone' => '0812-1111-1005', 'is_active' => false, 'kasir_index' => 4],
        ];

        $areas = [
            'Jakarta Selatan' => ['Kemang', 'Cilandak', 'Tebet', 'Pancoran', 'Mampang', 'Setiabudi'],
            'Jakarta Pusat' => ['Menteng', 'Senen', 'Cempaka Putih', 'Tanah Abang', 'Johar Baru'],
            'Jakarta Timur' => ['Cakung', 'Duren Sawit', 'Jatinegara', 'Kramat Jati', 'Pasar Rebo'],
            'Jakarta Utara' => ['Kelapa Gading', 'Koja', 'Tanjung Priok', 'Penjaringan', 'Cilincing'],
            'Jakarta Barat' => ['Grogol', 'Taman Sari', 'Kebon Jeruk', 'Kembangan', 'Cengkareng'],
        ];

        // Create original outlets
        foreach ($originalOutlets as $data) {
            $kasirData = $originalKasirs[$data['kasir_index']];
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

        // Generate 25 more outlets with faker
        DB::transaction(function () use ($areas) {
            $flatAreas = [];
            foreach ($areas as $city => $subdistricts) {
                foreach ($subdistricts as $sub) {
                    $flatAreas[] = ['city' => $city, 'subdistrict' => $sub];
                }
            }

            $used = [];
            for ($i = 0; $i < 25; $i++) {
                $kasir = User::factory()->kasir()->create([
                    'password' => bcrypt('password'),
                ]);

                $area = fake()->unique()->randomElement($flatAreas);

                Outlet::create([
                    'name' => 'Warung Mas Mbull - '.$area['subdistrict'],
                    'address' => 'Jl. Raya '.$area['subdistrict'].' No. '.fake()->numberBetween(1, 200).', '.$area['city'],
                    'phone' => '0812-'.fake()->numerify('####-').str_pad(1000 + $i + 5, 4, '0', STR_PAD_LEFT),
                    'is_active' => fake()->boolean(85),
                    'user_id' => $kasir->id,
                ]);
            }
        });
    }
}
