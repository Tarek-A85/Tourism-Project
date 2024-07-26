<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Company;
use App\Models\Airport;
use App\Models\Flight;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Flight>
 */
class FlightFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        $start = Airport::get()->random()->id;
        return [
            'company_id' => Company::get()->random()->id,
            'start_airport_id' => $start ,
            'end_airport_id' => Airport::where('id', '!=', $start)->get()->random()->id,
        ];
    }

    public function EnsureUniqueness($company_id){

        return $this->state(function (array $attributes) use ($company_id){

            $done = false;

           for($i=0; $i <= 100; $i++){

                $start_airport_id =  Airport::get()->random()->id;

                $end_airport_id = Airport::where('id', '!=',  $start_airport_id)->get()->random()->id;

                $exists = Flight::where('company_id', $company_id)->where('start_airport_id', $start_airport_id)->where('end_airport_id', $end_airport_id)->exists();

                if(!$exists){
                    $done = true;

                    break;
                }
            }

            if($done){

            return [
                'company_id' => $company_id,
                'start_airport_id' => $start_airport_id,
                'end_airport_id' => $end_airport_id,
            ];
        } else{
            return[ 
                'company_id' => null,
                'start_airport_id' => null,
                'end_airport_id' => null,
             ];
        }
        });

    }
}
