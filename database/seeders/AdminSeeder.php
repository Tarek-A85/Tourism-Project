<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            "name" => "Tarek",
            "email" => "tarek@email.com",
            "password" => bcrypt('12345678'),
            "is_admin" => true,
            "email_verified_at" => now(),
        ]);
        
    }
}
