<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'phone' => '+1234567890',
            'email' => 'admin@mail.com',
            'password' => 'password',
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);

        User::factory()->count(10)->create();
    }
}
