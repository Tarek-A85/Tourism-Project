<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\User;
use App\Models\Company;
use App\Models\Flight;
use App\Models\FlightTime;
use App\Models\FlightDetail;
use App\Models\FlightType;
use Carbon\Carbon;
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::command('sanctum:prune-expired')->everyTwoMinutes();
Schedule::command('otp:clean')->everySecond();

Artisan::command('delete_unverified_users', function () {
    User::where('email_verified_at', null)->where('created_at', '<=', now()->subHour(1))->forceDelete();
})->everySecond();

Artisan::command('update_tickets_number', function () {

  $companies = Company::with('flights')->get();

  foreach($companies as $company){
    foreach($company->flights as $flight){
        $times = $flight->flight_times()->whereRelation('date', 'date', '>=', now()->subDay(1)->toDateString())->get();

        foreach($times as $time){

            foreach($time->flight_details as $detail)
            $detail->update([
                'available_tickets' => fake()->randomNumber(3, false),
            ]);
        }
    }
  }

})->everySecond();

Artisan::command('add_flight_trips', function (){

    $companies = Company::with('flights')->get();

    foreach($companies as $company){

        $flight = Flight::factory()->EnsureUniqueness($company->id)->make();

        if($flight->company_id == null)
        continue;
         
       $flight->save();

         $time = FlightTime::factory()->create([
                'flight_id' => $flight->id,
         ]);

        $first_class = FlightDetail::factory()->create([
            'flight_time_id' => $time->id,
            'flight_type_id' => FlightType::where('name', 'First class')->first()->id, 
         ]);

         FlightDetail::factory()->EconomyPrice($first_class->adult_price,
                                                      $first_class->child_price,
                                                      $time->id)->create();


    }

})->everySecond();


