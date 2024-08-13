<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use App\Models\Date;
class DateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currentDate = Carbon::now()->subYears(1);

        for ($i = 0; $i < 365 *2; $i++) {
           
          Date::create([
                'date' => $currentDate->toDateString()
            ]);

            $currentDate->addDay();
        }
    }
}
