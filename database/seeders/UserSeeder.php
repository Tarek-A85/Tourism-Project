<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Mejeas',
            'email' => 'example@example.com',
            'password' => bcrypt('12345678'),
            'email_verified_at' => now(),
            'is_admin' => 0,
        ]);
    }
}
