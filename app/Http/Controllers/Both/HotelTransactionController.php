<?php

namespace App\Http\Controllers\Both;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Hotel;
use App\Models\Status;
use App\Models\Date;
use App\Models\Transaction;
use Illuminate\Validation\Rule;
use App\Models\TransactionType;
use App\Models\HotelTransaction;
use App\Notifications\CancelledReservationNotification;
use App\Notifications\BookingVerificationNotification;
use App\Mail\BookingVerificationMail;
use App\Mail\CancelledBookingMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
class HotelTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function check_availablity(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'hotel_id' => ['required', Rule::exists('hotels', 'id')->where( fn($query) => $query->where('deleted_at', null))],
            'number_of_rooms' => ['required', 'numeric', 'min:1', 'max:5'],
            'staying_date' => ['required', 'date'],
            'departure_date' => ['required', 'date'],
        ]);

        if($validator->fails()){
            return $this->fail($validator->errors()->first());
        }

        $available = rand(0,1);

        if(!$available){
            return $this->fail("There is no results applicable to your preferences");
        }

        $hotel = Hotel::findOrFail($request->hotel_id);

        $price = round($hotel->price_per_room * $request->number_of_rooms, 2);

        return $this->success("The total price for the reservation" , ["price" => $price]);

        
    }

    public function store(Request $request){

        $validator = Validator::make($request->all(), [
            'hotel_id' => ['required', Rule::exists('hotels', 'id')->where( fn($query) => $query->where('deleted_at', null))],
            'number_of_rooms' => ['required', 'numeric', 'min:0', 'max:5'],
            'staying_date' => ['required', 'date'],
            'departure_date' => ['required', 'date', 'after_or_equal:staying_date'],
            'name' => ['nullable', 'string'],
            'email' => [Rule::requiredIf(fn() => $request->name != null), 'email'],
            'wallet_password' => ['required', 'string'],
        ]);

        if($validator->fails()){
            return $this->fail($validator->errors()->first());
        }

        $wallet = auth()->user()->wallet;

        if(!$wallet){
            return $this->fail('Please create a wallet to make the reservation');
        }

        if(!Hash::check($request->wallet_password, $wallet->password)){
            return $this->fail('The password for the wallet is not correct');
        }

        $hotel = Hotel::findOrFail($request->hotel_id);

        $price = round($hotel->price_per_room * $request->number_of_rooms, 2);

        if($wallet->balance < $price){
            return $this->fail("You dont have enough balance to make this transaction");
        }

        $wallet->update([
            'balance' => round($wallet->balance - $price, 2),
        ]);

        $transaction = Transaction::create([
            'wallet_id' => $wallet->id,
            'transaction_type_id' => TransactionType::where('name', 'Hotel')->first()->id,
            'status_id' => Status::where('name', 'Has not started yet')->first()->id,
            'price' => $price,
            'name' => $request->name ?? null,
            'email' => $request->email ?? null,
        ]);

        $hotel_transaction = HotelTransaction::create([
            'transaction_id' => $transaction->id,
            'hotel_id' => $hotel->id,
            'number_of_rooms' => $request->number_of_rooms,
            'staying_date_id' => Date::where('date', $request->staying_date)->first()->id,
            'departure_date_id' => Date::where('date', $request->departure_date)->first()->id,
        ]);


        $details [0] = 'Transaction number: ' . $transaction->id;

        $details[1] = 'number of rooms: ' .   $request->number_of_rooms;

        $details[2] = 'price_per_room: ' .  $hotel->price_per_room;

        $details[3] = 'staying date: ' . $request->staying_date;

        $details[4] = 'departure date: ' . $request->departure_date;

        $details[5] =  'total price: ' . $transaction->price;

        if($request->name){
        $details[6] = 'renter name: ' . $request->name;

        $details[7] = 'renter email: ' . $request->email;
        }
        

        auth()->user()->notify(new BookingVerificationNotification($hotel->name, $details, 'Two days'));

        if($request->name){
            Mail::to($request->email)->send(new BookingVerificationMail($hotel->name, $details, 'Two days'));
        }

        return $this->success('The booking is done successfully');


    }

    /**
     * Display the specified resource.
     */
    public function show(String $id)
    {

        $just_to_find = HotelTransaction::findOrFail($id);

        $just_to_find->load('hotel', 'staying_date', 'departure_date');

        $hotel = $just_to_find->hotel->only('id', 'name');

        $hotel_transaction['id'] = $just_to_find->id;

        $hotel_transaction['transaction_id'] = $just_to_find->transaction_id;

        $hotel_transaction['hotel'] = $hotel;
        
        $hotel_transaction['staying_date'] = $just_to_find->staying_date->date;

        $hotel_transaction['departure_date'] = $just_to_find->departure_date->date;

        $hotel_transaction['number_of_rooms'] = $just_to_find->number_of_rooms;

        $hotel_transaction['price_per_room'] = $just_to_find->hotel->price_per_room;

        $name = $just_to_find->transaction->name;

        if($name){

        $hotel_transaction['renter_name'] = $name;

        $hotel_transaction['renter_email'] = $just_to_find->transaction->email;

        }

        return $this->success('Hotel transaction details', ["hotel_transaction" => $hotel_transaction]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        
        $hotel_transaction = HotelTransaction::findOrFail($id);

        $transaction = $hotel_transaction->transaction;

        $user = $transaction->wallet->user;

        if(auth()->user()->id != $user->id){
            return $this->fail('You are not authorized');
        }

        if($transaction->status->name != 'Has not started yet'){
            return $this->fail('You cant modify this booking because its ' . $transaction->status->name);
        }

        $validator = Validator::make($request->all(), [
            'number_of_rooms' => ['required', 'numeric' , 'min:1', 'max:5'],
            'staying_date' => ['required', 'date'],
            'departure_date' => ['required', 'date', 'after_or_equal:staying_date'],
            'name' => ['nullable', 'string'],
            'email' => [Rule::requiredIf(fn() => $request->name != null), 'email'],
            'wallet_password' => ['required', 'string'],
        ]);

        if($validator->fails()){
            return $this->fail($validator->errors()->first());
        }


        $wallet = auth()->user()->wallet;

        if(!Hash::check($request->wallet_password, $wallet->password)){
            return $this->fail('The password for the wallet is not correct');
        }

        if($hotel_transaction->number_of_rooms == $request->number_of_rooms &&
        $transaction->name == $request->name &&
        $transaction->email == $request->email &&
        $hotel_transaction->staying_date->date == $request->staying_date &&
        $hotel_transaction->departure_date->date == $request->departure_date){
         return $this->fail('Nothing changed to modify');
        }

        if($hotel_transaction->staying_date->date < now()->addDays(2)->toDateString()){
            if($request->number_of_rooms < $hotel_transaction->number_of_rooms)
            return $this->fail('You just can add additional rooms not decrease the number, because the reservation will happen after less than two days');

            if($request->staying_date > $hotel_transaction->staying_date->date || $request->departure_date < $hotel_transaction->departure_date->date){
                return $this->fail('You can just add aditional days to your stay, because the reservation will happen after less than two days ');
            }
        }


        if($request->number_of_rooms > $hotel_transaction->number_of_rooms || $request->staying_date < $hotel_transaction->staying_date->date || $request->staying_date > $hotel_transaction->departure_date->date || $request->departure_date > $hotel_transaction->departure_date->date){

            $available = rand(0,1);

            if(!$available){
                return $this->fail('There is no results applicable to your preferences, your booking will not change');
            }
        }

        $hotel = $hotel_transaction->hotel;

       

        if($request->number_of_rooms > $hotel_transaction->number_of_rooms){

          
            
            $added_price = round(($request->number_of_rooms - $hotel_transaction->number_of_rooms) * $hotel->price_per_room, 2);

            if($wallet->balance < $added_price){
                return $this->fail('You dont have enough balance');
            }

            $wallet->update([
                'balance' => round($wallet->balance - $added_price, 2),
            ]);
        }
        else if($request->number_of_rooms < $hotel_transaction->number_of_rooms){
            
            $added_balance = round(($hotel_transaction->number_of_rooms - $request->number_of_rooms) * $hotel->price_per_room, 2); 
            
            $wallet->update([
                'balance' => round($wallet->balance + $added_balance, 2),
            ]);
        }

        if((!$request->email && $transacation->email) || ($request->email && $request->email != $transaction->email)){

            Mail::to($transaction->email)->send(new CancelledBookingMail($hotel->name, $transaction->id));
        }

        $hotel_transaction->update([
            'number_of_rooms' => $request->number_of_rooms,
            'staying_date' => $request->staying_date,
            'departure_date' => $request->departure_date,
        ]);

        $transaction->update([
            'price' => round($request->number_of_rooms * $hotel->price_per_room, 2),
            'name' => $request->name ?? null,
            'email' => $request->email ?? null,
        ]);

        $details [0] = 'Transaction number: ' . $transaction->id;

        $details[1] = 'number of rooms: ' .   $request->number_of_rooms;

        $details[2] = 'price_per_room: ' .  $hotel->price_per_room;

        $details[3] = 'staying date: ' . $request->staying_date;

        $details[4] = 'departure date: ' . $request->departure_date;

        $details[5] =  'total price: ' . $transaction->price;

        if($request->name){

        $details[6] = 'renter name: ' . $request->name;

        $details[7] = 'renter email: ' . $request->email;
        }

        $user->notify(new BookingVerificationNotification($hotel->name, $details, 'Two days', 'modefied') );

        if($request->email){
            Mail::to($request->email)->send(new BookingVerificationMail($hotel->name, $details, 'Two days', 'modefied'));
        }

        return $this->success('The booking is modefied successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'wallet_password' => ['required', 'string'],

        ]);

        if($validator->fails()){
            return $this->fail($validator->errors()->first());
        }


       $hotel_transaction = HotelTransaction::findOrFail($id);

       $transaction = $hotel_transaction->transaction;

       $wallet = $transaction->wallet;

       $hotel = $hotel_transaction->hotel;


       if($wallet)
       $user = $wallet->user;

       if(!$wallet || $user->id != auth()->user()->id){

        return $this->fail('You are not authorized');
       }

       if(!Hash::check($request->wallet_password, $wallet->password)){
        return $this->fail('The password for the wallet is not correct');
       }

       if($hotel_transaction->staying_date->date < now()->addDays(2)->toDateString()){

        if($transaction->status->name == 'Has not started yet'){
           $message = 'It will happen after less than two days';
        }
        else{
            $message = 'its ' . $transaction->status->name;
        }

        return $this->fail('You cant cancel the reservation because ' . $message);
        
       }

       $wallet->update([
        'balance' => round($wallet->balance + $transaction->price, 2),
      ]);

       auth()->user()->notify(new CancelledReservationNotification($hotel->name, $transaction->id));

       if($transaction->name){
        Mail::to($transaction->email)->send(new CancelledBookingMail($hotel->name, $transaction->id));
       }


      $transaction->delete();

      return $this->success('You booking is cancelled successfully');

    }
}
