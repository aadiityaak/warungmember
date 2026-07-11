<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call(ProductSeeder::class);
        $this->call(MemberSeeder::class);
        $this->call(OutletSeeder::class);
        $this->call(RewardSeeder::class);
        $this->call(VoucherSeeder::class);
        $this->call(OrderSeeder::class);
        $this->call(NotificationSeeder::class);
        User::create([
            'name' => 'Admin',
            'email' => 'admin@warungmember.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);
    }
}
