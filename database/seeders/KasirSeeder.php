<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class KasirSeeder extends Seeder
{
    public function run(): void
    {
        $kasirs = [
            ['name' => 'Kasir Warung Mbak Sri', 'email' => 'kasir1@warungmember.test'],
            ['name' => 'Kasir Warung Mbak Ani', 'email' => 'kasir2@warungmember.test'],
            ['name' => 'Kasir Warung Mas Budi', 'email' => 'kasir3@warungmember.test'],
            ['name' => 'Kasir Warung Mbak Dewi', 'email' => 'kasir4@warungmember.test'],
            ['name' => 'Kasir Warung Mas Eko', 'email' => 'kasir5@warungmember.test'],
        ];

        foreach ($kasirs as $data) {
            User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => bcrypt('password'),
                    'role' => 'kasir',
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
