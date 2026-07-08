<?php

namespace Database\Seeders;

use App\Models\Member;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MemberSeeder extends Seeder
{
    public function run(): void
    {
        $members = [
            ['name' => 'Budi Santoso', 'email' => 'budi@member.test', 'password' => 'password', 'total_points' => 1250, 'deposit_balance' => 50000, 'birth_date' => '1995-03-15'],
            ['name' => 'Siti Nurhaliza', 'email' => 'siti@member.test', 'password' => 'password', 'total_points' => 3400, 'deposit_balance' => 150000, 'birth_date' => '1998-07-22'],
            ['name' => 'Agus Wijaya', 'email' => 'agus@member.test', 'password' => 'password', 'total_points' => 600, 'deposit_balance' => 0, 'birth_date' => '1990-11-08'],
            ['name' => 'Dewi Lestari', 'email' => 'dewi@member.test', 'password' => 'password', 'total_points' => 8900, 'deposit_balance' => 350000, 'birth_date' => '2000-01-30'],
            ['name' => 'Rudi Hartono', 'email' => 'rudi@member.test', 'password' => 'password', 'total_points' => 150, 'deposit_balance' => 20000, 'birth_date' => '1988-05-19'],
            ['name' => 'Anisa Rahma', 'email' => 'anisa@member.test', 'password' => 'password', 'total_points' => 0, 'deposit_balance' => 0, 'birth_date' => '2002-09-12'],
            ['name' => 'Dimas Ardian', 'email' => 'dimas@member.test', 'password' => 'password', 'total_points' => 2100, 'deposit_balance' => 100000, 'birth_date' => '1993-12-25'],
            ['name' => 'Rina Amelia', 'email' => 'rina@member.test', 'password' => 'password', 'total_points' => 780, 'deposit_balance' => 45000, 'birth_date' => '1997-06-03'],
            ['name' => 'Hendra Gunawan', 'email' => 'hendra@member.test', 'password' => 'password', 'total_points' => 4500, 'deposit_balance' => 200000, 'birth_date' => '1985-08-17'],
            ['name' => 'Fitri Handayani', 'email' => 'fitri@member.test', 'password' => 'password', 'total_points' => 320, 'deposit_balance' => 15000, 'birth_date' => '2001-04-10'],
            ['name' => 'Andre Putra', 'email' => 'andre@member.test', 'password' => 'password', 'total_points' => 0, 'deposit_balance' => 75000, 'birth_date' => '1994-02-28'],
            ['name' => 'Maya Sari', 'email' => 'maya@member.test', 'password' => 'password', 'total_points' => 6700, 'deposit_balance' => 125000, 'birth_date' => '1996-10-05'],
            ['name' => 'Fajar Setiawan', 'email' => 'fajar@member.test', 'password' => 'password', 'total_points' => 180, 'deposit_balance' => 0, 'birth_date' => '1991-07-14'],
            ['name' => 'Linda Permata', 'email' => 'linda@member.test', 'password' => 'password', 'total_points' => 1200, 'deposit_balance' => 60000, 'birth_date' => '1999-03-21'],
            ['name' => 'Bayu Saputra', 'email' => 'bayu@member.test', 'password' => 'password', 'total_points' => 550, 'deposit_balance' => 30000, 'birth_date' => '1987-11-30'],
        ];

        foreach ($members as $data) {
            DB::transaction(function () use ($data) {
                $user = User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => bcrypt($data['password']),
                    'role' => 'member',
                    'email_verified_at' => now(),
                ]);

                Member::create([
                    'user_id' => $user->id,
                    'member_code' => 'WM'.strtoupper(substr(bin2hex(random_bytes(4)), 0, 8)),
                    'total_points' => $data['total_points'],
                    'deposit_balance' => $data['deposit_balance'],
                    'birth_date' => $data['birth_date'],
                ]);
            });
        }

        // Generate 15 more members using faker
        for ($i = 0; $i < 15; $i++) {
            DB::transaction(function () {
                $user = User::factory()->create([
                    'role' => 'member',
                    'password' => bcrypt('password'),
                ]);

                Member::create([
                    'user_id' => $user->id,
                    'member_code' => 'WM'.strtoupper(substr(bin2hex(random_bytes(4)), 0, 8)),
                    'total_points' => fake()->numberBetween(0, 5000),
                    'deposit_balance' => fake()->numberBetween(0, 300000),
                    'birth_date' => fake()->dateTimeBetween('-40 years', '-18 years')->format('Y-m-d'),
                ]);
            });
        }
    }
}
