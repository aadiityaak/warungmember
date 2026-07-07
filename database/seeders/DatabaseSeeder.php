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

        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@warungmember.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        User::factory()->create([
            'name' => 'Kasir',
            'email' => 'kasir@warungmember.test',
            'password' => bcrypt('password'),
            'role' => 'kasir',
            'email_verified_at' => now(),
        ]);

        User::factory()->create([
            'name' => 'Member Demo',
            'email' => 'member@warungmember.test',
            'password' => bcrypt('password'),
            'role' => 'member',
            'email_verified_at' => now(),
        ]);
    }
}
