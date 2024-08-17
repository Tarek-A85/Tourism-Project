<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Folder;
use App\Models\Region;
use App\Models\Photo;
use App\Models\Flight;
use App\Models\FlightDetail;
use App\Models\FlightType;

use App\Models\FlightTime;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $description = ['Cham Wings Airlines, the first Syrian private airline company was established at the end of 2007 with a national capital, as one of the commercial Shammout group companies. Its establishment came as a result of the economic openness and the new laws came out at that time by the Syrian government as an encouragement to the private sector to enter into the air transport field to meet the growing necessities of the travel market.',
                         'The Syrian Airlines is the messenger of Syria to the countries of the world, where the journey of the ascension has begun to its splendor, to be the diamond in the sky, and the sun fo Syria that reflects the golden rays in all the corners og the world,and to say we came from the cradle of civilization transporting Home Flage to the world, where civilization was born and transfomed to the whole world on the wings of the Syrian of the Syrain Bird, the Phoenix.'];
        $i = 0;

       foreach(['Cham wings', 'Syrian airlines'] as $company){

         $instance = Company::create([
            'name' => $company,
            'description' => $description[$i++],
            'country_id' => Region::where('name', 'Syria')->first()->id,
        ]);

        $parent = Folder::firstOrCreate([
            'name' => 'Syria',
            'folder_id' => 6,
        ]);

        $child = Folder::create([
            'name' => $company,
            'folder_id' => $parent->id,
        ]);

        Storage::makeDirectory('Companies/Syria/' . $company);
        for($j = 1; $j <= 3; $j++){
            Photo::create([
                'name' => $company . '_pic_' . $j . '.png',
                'folder_id' => $child->id,

            ]);
        Storage::copy('seeding_pictures/' . $company . '_pic_' . $j . '.png', 'Companies/Syria/' . $company . '/' . $company . '_pic_' . $j . '.png');
        }

        $flights = Flight::factory()->count(3)->create([
            'company_id' =>  $instance->id,
        ]);

        foreach($flights as $flight){

          $times =  FlightTime::factory()->count(10)->create([
                'flight_id' => $flight->id,
            ]);

            foreach($times as $time){

                $first_class = FlightDetail::factory()->create([
                    'flight_time_id' => $time->id,
                    'flight_type_id' => FlightType::where('name', 'First class')->first()->id, 
                 ]);
        
                 FlightDetail::factory()->EconomyPrice($first_class->adult_price,
                                                              $first_class->child_price,
                                                              $time->id)->create();
            }
        }

    }


    /////////////////////////////////////////

    $second_description = "An amazing airline whic makes you comfortable with every trip";

    foreach(['Neos', 'ITA airways'] as $company){

        $instance = Company::create([
            'name' => $company,
            'description' => $second_description,
            'country_id' => Region::where('name', 'Italy')->first()->id,
        ]);

        $parent = Folder::firstOrCreate([
            'name' => 'Italy',
            'folder_id' => 6,
        ]);

        $child = Folder::create([
            'name' => $company,
            'folder_id' => $parent->id,
        ]);

        Storage::makeDirectory('Companies/Italy/' . $company);

        for($j = 1; $j <= 3; $j++){
            Photo::create([
                'name' => $company . '_pic_' . $j . '.png',
                'folder_id' => $child->id,

            ]);
        Storage::copy('seeding_pictures/' . $company . '_pic_' . $j . '.png', 'Companies/Italy/' . $company . '/' . $company . '_pic_' . $j . '.png');
        }

        $flights = Flight::factory()->count(3)->create([
            'company_id' =>  $instance->id,
        ]);

        foreach($flights as $flight){

            $times =  FlightTime::factory()->count(10)->create([
                'flight_id' => $flight->id,
            ]);

            foreach($times as $time){

                $first_class = FlightDetail::factory()->create([
                    'flight_time_id' => $time->id,
                    'flight_type_id' => FlightType::where('name', 'First class')->first()->id, 
                 ]);

                 FlightDetail::factory()->EconomyPrice($first_class->adult_price,
                 $first_class->child_price,
                 $time->id)->create();

            }

        }

    }

    }
}
