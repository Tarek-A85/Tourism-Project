<?php

namespace App\Traits;
use App\Models\Previlege;
use App\Models\Photo;
use App\Models\Flight;
use App\Models\Company;
use App\Models\Date;
use App\Models\Airport;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;
 trait GeneralTrait
{
    public function fail($message = null){
        return response()->json([
            "status" => false,
            "message" => $message,
            "data" => null,
        ]);
    }

    public function success($message = null, $data = null){
        return response()->json([
            "status" => true,
            "message" => $message,
            "data" => $data,
        ]);
    }

    public function save_image($photo, $folder, $path, $folder_id ){

        $extension = $photo->getClientOriginalExtension();

        $add = Str::uuid()->toString();

        $name = $add . '.' . $extension;

        $photo->storeAs($folder, $path . '/' . $name);

        Photo::create([
            'name' => $name ,
            'folder_id' => $folder_id,
        ]);

    }

    public function delete_image($id){

        $photo = Photo::find($id);

        $path = $photo->folder->path . '/' . $photo->name;

        Storage::delete($path);
        
        $photo->delete();
    }

    public function flight_search($request){

        $array = new collection();

        $dep_date = Date::where('date', $request['departure_date'])->first();

        foreach($request['companies'] as $company){

           $flight = $company->flights()->where('start_airport_id', $request['start_airport_id'])->where('end_airport_id', $request['end_airport_id'])->first();
           if(!$flight)
              continue;

           $times = $flight->flight_times()->where('date_id', $dep_date->id)->get();

           foreach($times as $time){
           $valid = $time->flight_details()->where('flight_type_id', $request['class_id'])->where('available_tickets', '>=', $request['adults_number'] + $request['children_number'])->first();
          
           if($valid){

            $collection = collect([
                "id" => $valid->id,
                "company_id" => $company->id,
                "company_name" => $company->name,
                "start_airport" => Airport::where('id', $request['start_airport_id'])->first()->name,
                "end_airport" => Airport::where('id', $request['end_airport_id'])->first()->name,
                "class" => $valid->class->name,
                "date" => $request['departure_date'],
                "time" => $time->time,
                "price" => round(($request['adults_number'] * $valid->adult_price) + ($request['children_number'] * $valid->child_price), 2) 
            ]);

            $array->push($collection);
           }
           }

        }

        return $array;
    }

    

   
}
