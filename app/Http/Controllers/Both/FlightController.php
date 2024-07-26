<?php

namespace App\Http\Controllers\Both;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Flight;
use App\Models\Company;
use App\Models\Date;
use App\Models\Airport;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Rules\MaxChildrenRule;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
class FlightController extends Controller
{
    public function search(Request $request){

        $validator = Validator::make($request->all(), [
            'start_airport_id' => ['required', Rule::exists('airports', 'id')],
            'end_airport_id' => ['required', Rule::exists('airports', 'id')->where( fn($query) => $query->where('id', '!=', $request->start_airport_id))],
            'company_id' => ['nullable', Rule::exists('companies', 'id')->where( fn($query) => $query->where('deleted_at', null))],
            'adults_number' => ['required', 'numeric', 'min:1', 'max:6'],
            'children_number' => ['required', 'numeric', 'min:0', new MaxChildrenRule],
            'class_id' => ['required', Rule::exists('flight_types', 'id')],
            'departure_date' => ['required', 'date'],
            'return_date' => ['nullable', 'date', 'after_or_equal:departure_date'],
        ]);

        if($validator->fails()){
            return $this->fail($validator->errors()->first());
        }

        $going = new collection();

        if($request->company_id){
            $companies = Company::with('flights')->where('id', $request->company_id)->get();
        }

        else{
        $companies = Company::with('flights')->get();
        }

        $request['companies'] = $companies;

        $going = $this->flight_search($request);

        if($going->count()==0){

            return $this->fail("There are no flights applicable to your preferences");
        }

         if(!$request->return_date){

            $going = $going->sortBy('price');

         return $this->success("All going flights" , ["flights" => $going->values()]);

         }

        $return_flight = collect($request)->except(['return_date']);

        $return_flight = $return_flight->replace([
            'start_airport_id' => $request['end_airport_id'],
            'end_airport_id' => $request['start_airport_id'],
            'departure_date' => $request['return_date'],
        ]);

        $return = new collection();

        $return = $this->flight_search($return_flight);

        if($return->count() == 0){
            return $this->fail("There are no flights applicable to your preferences");
        }

        $result = new collection();

        foreach($going as $go){

            foreach($return as $ret){
                $collection = collect([
                    'going_id' => $go['id'],
                    'return_id' => $ret['id'],
                    'going_company_id' => $go['company_id'],
                    'going_company_name' => $go['company_name'],
                    'return_company_id' => $ret['company_id'],
                    'return_company_name' => $ret['company_name'],
                    'start_airport' =>  $go['start_airport'],
                    'end_airport' => $go['end_airport'],
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

}
