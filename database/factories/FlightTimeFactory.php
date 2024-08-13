<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Flight;
use App\Models\Date;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FlightTime>
 */
class FlightTimeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'flight_id' => Flight::get()->random()->id,
            'date_id' => Date::where('date', '>=', now()->toDateString())->get()->random()->id,
            'time' => fake()->time(),
           
        ];
    }
}
