<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\FlightTime;
use App\Models\FlightType;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FlightDetail>
 */
class FlightDetailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $adult_price =  fake()->randomFloat(2, 1, 10000);
        return [
            'flight_time_id' => FlightTime::get()->random()->id,
            'flight_type_id' => FlightType::get()->random()->id,
            'available_tickets' => fake()->randomNumber(3, false),
            'adult_price' => $adult_price,
            'child_price' => fake()->randomFloat(2, 1, $adult_price),
        ];
    }

    public function EconomyPrice($first_class_adult_price, $first_class_child_price , $flight_time_id){

        return $this->state(function (array $attributes) use ($first_class_adult_price, $first_class_child_price, $flight_time_id){

            $price_for_adults = fake()->randomFloat(2, 1, $first_class_adult_price);
            return [
                'flight_time_id' => $flight_time_id,
                'flight_type_id' => FlightType::where('name', 'Economy')->first()->id,
                'available_tickets' => fake()->randomNumber(3,false),
                'adult_price' => $price_for_adults ,
                'child_price' => fake()->randomFloat(2, 1, min($first_class_child_price, $price_for_adults)),
            ];

        });
    }
}
