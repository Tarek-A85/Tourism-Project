<?php

namespace Database\Seeders;

use App\Models\TypeOfPackage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PackageTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = ['food', 'learning', 'hiking', 'winter', 'summer', 'autumn', 'spring', 'natural views', 'modern archeticture', 'forests'];
        foreach ($types as $name)
            TypeOfPackage::create([
                'name' => $name
            ]);
    }
}
