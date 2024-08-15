<?php

namespace App\Http\Controllers\Both;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Rules\MaxChildrenRule;
use App\Models\FlightTransaction;
use App\Models\FlightDetail;
use App\Models\FlightType;
use App\Models\Transaction;
use App\Models\Status;
use App\Models\Date;
use App\Models\Company;
use Illuminate\Support\Arr;

use App\Models\TransactionType;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Notifications\CancelledReservationNotification;
use App\Notifications\BookingVerificationNotification;
use App\Mail\BookingVerificationMail;
use App\Mail\CancelledBookingMail;
use App\Models\Flight;
use Illuminate\Support\Collection;
class FlightTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'going_id' => ['required', 'exists:flight_details,id'],
            'return_id' => ['nullable', 'exists:flight_details,id'],
            'adults_number' => ['required', 'numeric', 'min:1', 'max:6'],
            'children_number' => ['required', 'numeric', 'min:0', new MaxChildrenRule],
            'price' => ['required', 'numeric'],
            'wallet_password' => ['required', 'string'],
            'name' => ['nullable', 'string'],
            'email' => [Rule::requiredIf(fn()=> $request->name != null), 'email']
        ]);

        if($validator->fails()){
            return $this->fail($validator->errors()->first());
        }

        $wallet = auth()->user()->wallet;

        if(!Hash::check($request->wallet_password, $wallet->password)){
            return $this->fail('The password for the wallet is not correct');
        }

        if($wallet->balance < $request->price){
            return $this->fail('You do not have enough balance to make this booking');
        }

        $transaction = Transaction::create([
            'wallet_id' => $wallet->id,
            'transaction_type_id' => TransactionType::where('name', 'Flight')->first()->id,
            'status_id' => Status::where('name', 'Has not started yet')->first()->id,
            'price' => $request->price,
            'name' => $request->name ?? null,
            'email' => $request->email ?? null,
        ]);

        $going_detail = FlightDetail::findOrFail($request->going_id);

        $flight_time = $going_detail->flight_time;

        $flight = $flight_time->flight;

        if($request->return_id){
            $return_detail = FlightDetail::findOrFail($request->return_id);

            $return_time = $return_detail->flight_time;
    
            $return_flight = $return_time->flight;
        }

        $flight_transaction = FlightTransaction::create([
            'transaction_id' => $transaction->id,
            'flight_detail_id' => $request->going_id,
            'flight_type_id' =>  $going_detail->class->id,
            'children_number' => $request->children_number,
            'children_total_price' => round($request->children_number * $going_detail->child_price, 2),
            'adult_number' => $request->adults_number,
            'adult_total_price' => round($request->adults_number * $going_detail->adult_price, 2)
        ]);

        $going_detail->update([
            'available_tickets' => $going_detail->available_tickets - ($request->adults_number + $request->children_number),
        ]);

        if($request->return_id){
            $return_flight_transaction = FlightTransaction::create([
                'transaction_id' => $transaction->id,
                'flight_detail_id' => $request->return_id,
                'flight_type_id' => $going_detail->class->id,
                'children_number' => $request->children_number,
                'children_total_price' => round($request->children_number * $return_detail->child_price, 2),
                'adult_number' => $request->adults_number,
                'adult_total_price' =>  round($request->adults_number * $return_detail->adult_price, 2),
            ]);

            $return_detail->update([
                'available_tickets' => $return_detail->available_tickets - ($request->adults_number + $request->children_number)

            ]);
        }

        $wallet->update([
            'balance' => $wallet->balance - $request->price,
        ]);

        $details = [];

        array_push($details, 'Transaction number: ' . $transaction->id);

        array_push($details,'start airport: ' . $flight->starting_airport->name);

        array_push($details,'end airport: ' . $flight->ending_airport->name);

        if($request->return_id){
        array_push($details,'flight type: Round trip');
        } else{
        array_push($details,'flight type: One-way trip');
        }

        array_push($details, 'Flight class: ' . $going_detail->class->name);

        array_push($details,'Going date and time: ' . $flight_time->date->date . '  ' . $flight_time->time);

        array_push($details, 'Going company: ' . $flight->company->name);

        if($request->return_id){

           

            array_push($details, 'Return date and time: ' . $return_time->date->date . '  ' . $return_time->time);

            array_push($details, 'Return company: ' . $return_flight->company->name);
        }

        array_push($details, 'Adults number: ' . $request->adults_number);

        array_push($details, 'Children number: ' . $request->children_number);

        array_push($details, 'Total price: ' . $request->price);

        if($request->name){
            array_push($details, 'Reserver name: ' . $request->name);

            array_push($details, 'Reserver email: ' . $request->email);
        }

        auth()->user()->notify(new BookingVerificationNotification($flight->starting_airport->name, $details, 'during 24 hours after reservation if its 7 days or more'));

        if($request->name){
            Mail::to($request->email)->send(new BookingVerificationMail($flight->starting_airport->name, $details, 'during 24 hours after reservation if its 7 days or more'));
        }
        
        return $this->success('The booking is done successfully');

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

        $transaction = Transaction::findOrFail($id); 

        $find = Transaction::with('flight_transactions')->findOrFail($id)->flight_transactions;

        if($find->count() == 0){
            return $this->fail('There is no object like that');
        }

         $flight_detail = $find[0]->flight_details;

         $flight_time = $flight_detail->flight_time;

         $flight = $flight_time->flight;

         $response['transaction_number'] = $find[0]->transaction_id;

         $response['start_airport'] = $flight->starting_airport->name;

         $response['end_airport'] = $flight->ending_airport->name;

         if($find->count() > 1){
            $response['flight_type'] = 'Round trip';
        } else{
            $response['flight_type'] = 'One-way';
        }

        $response['adults_number'] = $find[0]->adult_number;

        $response['children_number'] = $find[0]->children_number;

         $response['going_company'] = $flight->company->name;

        $response['going_date'] = $flight_time->date->date;

        $response['going_time'] = $flight_time->time;

        $response['going_adult_total_price'] = $find[0]->adult_total_price;

        $response['going_children_total_price'] = $find[0]->children_total_price;

        if($find->count() > 1){
            $second_flight_detail = $find[1]->flight_details;

            $second_flight_time = $second_flight_detail->flight_time;
    
            $second_flight = $second_flight_time->flight;

            $response['return_company'] = $second_flight->company->name;

            $response['return_date'] = $second_flight_time->date->date;

            $response['return_time'] = $second_flight_time->time;

            $response['return_adult_total_price'] = $find[1]->adult_total_price;

            $response['return_children_total_price'] = $find[1]->children_total_price;
        }

           $response['class'] = $flight_detail->class->name;
           if($transaction->name){

           $response['name'] = $transaction->name;

           $response['email'] = $transaction->email;
           }

        return $this->success('Flight transaction info', ['flight_transaction' => $response]);

    }

    /**
     * Update the specified resource in storage.
     */
    public function available_flights_to_update(Request $request, string $id)
    {

        $transaction = Transaction::findOrFail($id);

        $flight_transaction = $transaction->flight_transactions;

        if($flight_transaction->count() == 0){
            return $this->fail('There is no object like that');
        }

        $validator = Validator::make($request->all(), [
            'adults_number' => ['required', 'numeric', 'min:1', 'max:6'],
            'children_number' => ['required', 'numeric', 'min:0', new MaxChildrenRule],
            'class_id' => ['required', Rule::exists('flight_types', 'id')],
            'departure_date' => ['required', 'date'],
            'return_date' => ['nullable', 'date', 'after_or_equal:departure_date'],
            //'flight_type' => ['required', Rule::in(['One-way', 'Round trip'])],
            'name' => ['nullable', 'string'],
            'email' => [Rule::requiredIf(fn()=> $request->name != null), 'string']
        ]);

        if($validator->fails()){
            return $this->fail($validator->errors()->first());
        }

        $wallet = $transaction->wallet;

        if($wallet->user->id != auth()->user()->id){
            return $this->fail('You are not authorized');
        }

        $going_detail = $flight_transaction[0]->flight_details;

        $going_time = $going_detail->flight_time;

        $flight = $going_time->flight;

        $return_detail = null;

        $return_time = null;

        $return_flight = null;

        if($request->return_date){

            $return_detail = $flight_transaction[1]->flight_details;

            $return_time = $return_detail->flight_time;

            $return_flight = $return_time->flight;

        }

        if($flight_transaction[0]->adult_number == $request->adults_number &&
           $flight_transaction[0]->children_number == $request->children_number && 
           $going_detail->class->id == $request->class_id && 
           $going_time->date->date == $request->departure_date){
            if($request->return_date){

                if($return_time->date->date == $request->return_date){
                    return $this->fail("There is nothing to update");
                }
            }
            else if( $transaction->name == $request->name &&
            $transaction->email == $request->email){
                return $this->fail("There is nothing to update");
            }

            else{
                if($transaction->name != $request->name){
                    $transaction->update([
                        'name' => $request->name,
                    ]);
                }

                if($transaction->email != $request->email){
                    if($request->email)
                    Mail::to($transaction->email)->send(new CancelledBookingMail($flight->starting_airport->name, $transaction->id));

                    $transaction->update([
                        'email' => $request->email,
                    ]);
                }

                $details = [];

                array_push($details, 'Transaction number: ' . $transaction->id);
        
                array_push($details,'start airport: ' . $flight->starting_airport->name);
        
                array_push($details,'end airport: ' . $flight->ending_airport->name);
        
                if($request->return_date){
                array_push($details,'flight type: Round trip');
                } else{
                array_push($details,'flight type: One-way trip');
                }
        
                array_push($details, 'Flight class: ' . $going_detail->class->name);
        
                array_push($details,'Going date and time: ' . $going_time->date->date . '  ' . $going_time->time);
        
                array_push($details, 'Going company: ' . $flight->company->name);
        
                if($request->return_date){
                    array_push($details, 'Return date and time: ' . $return_time->date->date . '  ' . $return_time->time);
        
                    array_push($details, 'Return company: ' . $return_flight->company->name);
                }
        
                array_push($details, 'Adults number: ' . $request->adults_number);
        
                array_push($details, 'Children number: ' . $request->children_number);
        
                array_push($details, 'Total price: ' . $transaction->price);
        
                if($request->name){
                    array_push($details, 'Reserver name: ' . $request->name);
        
                    array_push($details, 'Reserver email: ' . $request->email);

                    Mail::to($request->email)->send(new BookingVerificationMail($flight->starting_airport->name, $details, 'during 24 hours after reservation if its 7 days or more', 'modefied'));
                }

                auth()->user()->notify(new BookingVerificationNotification($flight->starting_airport->name, $details, 'during 24 hours after reservation if its 7 days or more', 'modefied'));

                return $this->success('Your flights info is updated successfully');

            }
        }

        if($going_time->date->date < now()->addDays(7)->toDateString() && ($request->adults_number < $flight_transaction[0]->adult_number || $request->children_number < $flight_transaction[0]->children_number || $request->departure_date != $going_time->date->date || ( $request->return_date != null && $request->return_date != $return_time->date->date))){
            return $this->fail('You cant cancel some tickets or modify the dates in the reservation because it is after less than 7 days');
        }

        if($transaction->created_at > now()->addDays(1) && ($request->adults_number < $flight_transaction[0]->adult_number || $request->children_number < $flight_transaction[0]->children_number || $request->departure_date != $going_time->date->date ||( $request->return_date != null && $request->return_date != $return_time->date->date))){
            return $this->fail('You cant cancel some tickets or modify the dates in the reservation because you reserved before more than a day');
        }

        $request['companies'] = $flight->company->get();

        $request['start_airport_id'] = $flight->starting_airport->id;

        $request['end_airport_id'] = $flight->ending_airport->id;

        $available_going = new collection();

        $available_going = $this->flight_search($request);

        if($available_going->count() == 0){

            return $this->fail('There are no flights applicable to your preferences');

        }

        if($request->return_date){

            $available_return = collect($request);

            $available_return = $available_return->replace([
                'companies' => $return_flight->company->get(),
                'start_airport_id' => $request['end_airport_id'],
                'end_airport_id' => $request['start_airport_id'],
                'departure_date' => $request['return_date'],
            ]);

            $is_available_on_return = new collection();

            $is_available_on_return = $this->flight_search($available_return);

            if($is_available_on_return->count() == 0){
                return $this->fail('There are no flights applicable to your preferences');
            }

            $result = new collection();

            foreach($available_going as $go){
                foreach($is_available_on_return as $ret){
                    $collection = collect([
                    'going_id' => $go['id'],
                    'return_id' => $ret['id'],
                    'start_airport' =>  $go['start_airport'],
                    'end_airport' => $go['end_airport'],
                    'going_company_name' => $go['company_name'],
                    'return_company_name' => $ret['company_name'],
                    'class' => $go['class'],
                    'going_date' => $go['date'],
                    'going_time' => $go['time'],
                    'return_date' => $ret['date'],
                    'return_time' => $ret['time'],
                    'price' => round($go['price'] + $ret['price'], 2),

                    ]);

                    $result->push($collection);
                }
            }

        $result = $result->sortBy('price');

        return $this->success("All going and returning flights", ["flights" => $result->values()]);
        }

        $available_going->sortBy('price');

        return $this->success("All going flights" , ["flights" => $available_going->values()]);

    }

    public function update(Request $request, String $id){

        $transaction = Transaction::findOrFail($id);

        $flight_transaction = $transaction->flight_transactions;

        if($flight_transaction->count() == 0){
            return $this->fail('There is no object like that');
        }

        $validator = Validator::make($request->all(), [
            'going_id' => ['required', 'exists:flight_details,id'],
            'return_id' => ['nullable', 'exists:flight_details,id'],
            'adults_number' => ['required', 'numeric', 'min:1', 'max:6'],
            'children_number' => ['required', 'numeric', 'min:0', new MaxChildrenRule],
            'price' => ['required', 'numeric'],
            'wallet_password' => ['required', 'string'],
            'name' => ['nullable', 'string'],
            'email' => [Rule::requiredIf(fn()=> $request->name != null), 'email']
        ]);

        if($validator->fails()){
            return $this->fail($validator->errors()->first());
        }

        $going_detail = FlightDetail::findOrFail($request->going_id);

        $going_time = $going_detail->flight_time;

        $flight = $going_time->flight;

        $return_detail = null;

        $return_time = null;

        $return_flight = null;

            $going_detail->update([
                'available_tickets' => $going_detail->available_tickets +( $flight_transaction[0]->adult_number - $request->adults_number ),
            ]);

            $going_detail->update([
                'available_tickets' => $going_detail->available_tickets + ($flight_transaction[0]->children_number - $request->children_numbe),
            ]);

        $flight_transaction[0]->update([
            'flight_detail_id' => $request->going_id,
            'flight_type_id' => $going_detail->class->id,
            'children_number' => $request->children_number,
            'children_total_price' => round($request->children_number * $going_detail->child_price, 2),
            'adult_number' => $request->adults_number,
            'adult_total_price' => round($request->adults_number * $going_detail->adult_price, 2)
        ]);

        if($request->return_id){

            $return_detail = FlightDetail::findOrFail($request->return_id);

            $return_time = $return_detail->flight_time;

            $return_flight = $return_time->flight;

            $return_detail->update([
                'available_tickets' => $return_detail->available_tickets +( $flight_transaction[0]->adult_number - $request->adults_number ),
            ]);

            $return_detail->update([
                'available_tickets' => $return_detail->available_tickets + ($flight_transaction[0]->children_number - $request->children_numbe),
            ]);

            $flight_transaction[1]->update([
                'flight_detail_id' => $request->return_id,
                'flight_type_id' => $going_detail->class->id,
                'children_number' => $request->children_number,
                'children_total_price' => round($request->children_number * $return_detail->child_price, 2),
                'adult_number' => $request->adults_number,
                'adult_total_price' => round($request->adults_number * $return_detail->adult_price, 2),
            ]);
        }

        $details = [];

        array_push($details, 'Transaction number: ' . $transaction->id);

        array_push($details,'start airport: ' . $flight->starting_airport->name);

        array_push($details,'end airport: ' . $flight->ending_airport->name);

        if($request->return_id){
        array_push($details,'flight type: Round trip');
        } else{
        array_push($details,'flight type: One-way trip');
        }

        array_push($details, 'Flight class: ' . $going_detail->class->name);

        array_push($details,'Going date and time: ' . $going_time->date->date . '  ' . $going_time->time);

        array_push($details, 'Going company: ' . $flight->company->name);

        if($request->return_id){

            array_push($details, 'Return date and time: ' . $return_time->date->date . '  ' . $return_time->time);

            array_push($details, 'Return company: ' . $return_flight->company->name);
        }

        array_push($details, 'Adults number: ' . $request->adults_number);

        array_push($details, 'Children number: ' . $request->children_number);

        array_push($details, 'Total price: ' . $request->price);

        if($request->name){
            array_push($details, 'Reserver name: ' . $request->name);

            array_push($details, 'Reserver email: ' . $request->email);
        }

        if($transaction->name && $request->name != $transaction->name){
            Mail::to($transaction->email)->send(new CancelledBookingMail($flight->starting_airport->name, $transaction->id));
        }

        $transaction->update([
            'price' => $request->price,
            'name' => $request->name,
            'email' => $request->email,
        ]);

        auth()->user()->notify(new BookingVerificationNotification($flight->starting_airport->name, $details, 'during 24 hours after reservation if its 7 days or more', 'modefied' ) );

        if($transaction->email){
            Mail::to($transaction->email)->send(new BookingVerificationMail($flight->starting_airport->name, $details, 'during 24 hours after reservation if its 7 days or more', 'modefied'));
        }

        return $this->success('Your flight is modefied successfylly');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $transaction = Transaction::findOrFail($id);

        $flight_transaction = $transaction->flight_transactions;

        if($flight_transaction->count() == 0){
            return $this->fail('There is no object like that');
        }

        $validator = Validator::make($request->all(), [
            'wallet_password' => ['required', 'string'],
        ]);

        if($validator->fails()){
            return $this->fail($validator->errors()->first());
        }

        $going_detail = $flight_transaction[0]->flight_details;

        $going_time = $going_detail->flight_time;

        $flight = $going_time->flight;

        if($going_time->date->date < now()->addDays(7)->toDateString()){
            return $this->fail('You cant cancel the reservation because it is after less than 7 days');
        }

        if($transaction->created_at > now()->addDays(1)){
            return $this->fail('You cant cancel the reservation because you reserved before more than a day');
        }

        $going_detail->update([
            'available_tickets' => $going_detail->available_tickets + $flight_transaction[0]->adult_number + $flight_transaction[0]->children_number,
        ]);

        if($flight_transaction->count() > 1){

            $return_detail = $flight_transaction[1]->flight_details;

            $return_detail->update([
                'available_tickets' => $return_detail->available_tickets + $flight_transaction[1]->adult_number + $flight_transaction[0]->children_number,
            ]);



        }

        $wallet = $transaction->wallet;

        $wallet->update([
            'balance' => $wallet->balance + $transaction->price,
        ]);

        auth()->user()->notify(new CancelledReservationNotification($flight->starting_airport->name, $transaction->id));

        if($transaction->email){
            Mail::to($transaction->email)->send(new CancelledBookingMail($flight->starting_airport->name, $transaction->id));
        }

        $transaction->delete();

        return $this->success('You booking is cancelled successfully');

    }
}
