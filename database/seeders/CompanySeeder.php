<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $chamWings = Company::create([
            'name' => 'Cham wings',
            'description' => 'Cham Wings Airlines, the first Syrian private airline company was established at the end of 2007 with a national capital, as one of the commercial Shammout group companies. Its establishment came as a result of the economic openness and the new laws came out at that time by the Syrian government as an encouragement to the private sector to enter into the air transport field to meet the growing necessities of the travel market.'
        ]);

        $syrianairline = Company::create([
            'name' => 'Syrian Airlines',
            'description' => 'The Syrian Airlines is the messenger of Syria to the countries of the world, where the journey of the ascension has begun to its splendor, to be the diamond in the sky, and the sun fo Syria that reflects the golden rays in all the corners og the world,and to say we came from the cradle of civilization transporting Home Flage to the world, where civilization was born and transfomed to the whole world on the wings of the Syrian of the Syrain Bird, the Phoenix.'
        ]);

    }
}
