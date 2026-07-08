<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

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
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@warungmember.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
    }
}
