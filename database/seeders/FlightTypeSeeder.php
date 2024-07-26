<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\FlightType;
class FlightTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $classes = ['First class', 'Economy'];

        foreach($classes as $class){

            FlightType::create([
                'name' => $class,
            ]);

        }
    }
}
