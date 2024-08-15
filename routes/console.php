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
use App\Models\Transaction;
use App\Models\HotelTransaction;
use App\Models\TransactionType;
use App\Models\Status;
use Carbon\Carbon;
use Psy\Readline\Transient;

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

Artisan::command('change_hotel_transaction_status', function(){

    $transactions = Transaction::where('transaction_type_id', TransactionType::where('name', 'Hotel')->first()->id)->get();

    foreach($transactions as $transaction){

        $hotel_transaction = $transaction->hotel_transactions;

        if($hotel_transaction->staying_date->date >= now()->toDateString() ){

            if($hotel_transaction->departure_date->date > now()->toDateString()){

            $transaction->update([
                'status_id' => Status::where('name', 'In progress')->first()->id,
            ]);
        }
        else if($hotel_transaction->departure_date->date < now()->toDateString()){

            $transaction->update([
                'status_id' => Status::where('name', 'Completed')->first()->id,
            ]);
        }
        } 
    }
});

Artisan::command('change_flight_transaction_status', function(){

    $transactions = Transaction::where('transaction_type_id', TransactionType::where('name', 'Flight')->first()->id)->get();

    foreach($transactions as $transaction){

        $flight_transactions = $transaction->flight_transactions;

        $going_detail = $flight_transactions[0]->flight_details;

        $going_time = $going_detail->flight_time;

        if($flight_transactions->count() > 1){
            $return_detail = $flight_transactions[1]->flight_details;

            $return_time = $return_detail->flight_time;
        }

       if($going_time->date->date == now()->toDateString()){

        if($going_time->time <=now()->toTimeString()){
            $transaction->update([
                'status_id' => Status::where('name', 'In progress')->first()->id,
            ]);
        }
       }

       else if($going_time->date->date < now()->toDateString()){

        if($return_time && $return_time->date->date < now()->toDateString()){
            
            $transaction->update([
                'status_id' => Status::where('name', 'Completed')->first()->id,
            ]);
           
        }
       }
    }


});

Artisan::command('change_package_transaction_status', function(){

    $transactions = Transaction::where('transaction_type_id', TransactionType::where('name', 'Package')->first()->id)->get();

    foreach($transactions as $transaction){

        $package_transaction = $transaction->package_transactions;
        $trip = $transaction->package_transactions->tripDetail;

        if($trip->date->date >= now()->toDateString() ){

            if($trip->current_area == -1){

            $transaction->update([
                'status_id' => Status::where('name', 'Completed')->first()->id,
            ]);
        }
        else{

            $transaction->update([
                'status_id' => Status::where('name', 'In progress')->first()->id,
            ]);
        }
        } 
    }
});


