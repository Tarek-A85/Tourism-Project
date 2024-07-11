<?php

namespace App\Http\Controllers\Both;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Hotel;
use App\Models\Region;
use App\Models\Room;
use App\Models\Previlege;
use Illuminate\Validation\Rule;
use Exception;
use Illuminate\Support\Facades\Validator;
use App\Traits\GeneralTrait;
class HotelController extends Controller
{
    use GeneralTrait;
    /**
     * Display a listing of the resource.
     */


    public function index()
    {
        $all_hotels = Hotel::OrderBy('stars', 'DESC')->select('id', 'name')->get();

        $home_hotels = $all_hotels->take(8);

        return $this->success("All hotels" , ["home_hotels" => $home_hotels, "all_hotels" => $all_hotels]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $messages = [
         'photos.*.image' => 'The inserted files must all be images',
         'previleges.required' => 'You have to add at least one previlege',
        ];

        $validator = Validator::make($request->all(),[
            "name" => ['required', 'string'],
            "description" => ['required', 'max:300'],
            "stars" => ['required', 'decimal:0,2'],
            "region_id" => ['required', Rule::exists('regions', 'id')->where(fn($query)=> $query->where('region_id', '!=', null))],
            "number_of_rooms" => ['required', 'numeric', 'integer'],
            "price_per_night" => ['required', 'numeric', 'decimal:0,2'],
            "photos" => ['required', 'array'],
            "photos.*" => ['required', 'image'],
            "previleges" => ['required', 'array'],
            "previleges.*.name" => ['required', 'string'],
        ], $messages);

        if($validator->fails()){
           return $this->fail($validator->errors()->first());
        }

    $hotel = Hotel::create([
        "name" => $request->name,
        "description" => $request->description,
        "stars" => $request->stars,
        "region_id" => $request->region_id,
    ]);

    $region = Region::find($request->region_id);

    $country = $region->country->name;

    $room = Room::create([
        "hotel_id" => $hotel->id,
        "total_number" => $request->number_of_rooms,
        "available_number" => $request->number_of_rooms,
        "price" => $request->price_per_night,
    ]);

    foreach($request->photos as $photo){

    $this->save_image($photo, 'Hotels', $country . '/' . $region->name . '/' . $hotel->name, $hotel->id, "App/Models/Hotel" );

    }

   
        foreach($request->previleges as $previlege){
            $p = Previlege::firstOrCreate([
                "name" => $previlege["name"],
            ]);
            $period = $previlege["period"] ?? null;

            $hotel->previleges()->attach($p->id, ["period" => $period]);
        }

        return $this->success("Hotel is added successfully");
   


    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

       $hotel = Hotel::withTrashed()->with('previleges:id,name', 'region:name,id,region_id')->findOrFail($id);

       return $this->success("hotel info" , ["hotel" => $hotel]);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $hotel = Hotel::withTrashed()->findOrFail($id);

        $validator = Validator::make($request->all(), [
           "name" => ['required', 'string'],
            "description" => ['required', 'max:300'],
            "stars" => ['required', 'decimal:0,2'],
            "region_id" => ['required', Rule::exists('regions', 'id')->where(fn($query)=> $query->where('region_id', '!=', null))],
            "number_of_rooms" => ['required', 'numeric', 'integer'],
            "price_per_night" => ['required', 'numeric', 'decimal:0,2'],
            'deleted_photos' => ['nullable', 'array'],
            'deleted_photos.*' => ['required', Rule::exists('photos', 'id')->where(fn ($query) => $query->where('photoable_id',  $hotel->id))],
            'added_photos' => ['nullable', 'array'],
            'added_photos.*' => ['required', 'image'],
            'deleted_previleges' => ['nullable', 'array'],
            'deleted_previleges.*' => ['required', Rule::exists('hotel_previlege','previlege_id')->where(fn ($query) => $query->where('hotel_id', $hotel->id))],
            'added_previleges' => ['nullable', 'array'],
            'added_previleges.*.name' => ['required', 'string'],
        ]);

        if($validator->fails()){

            return $this->fail($validator->errors()->first());
        }

         $hotel->update([
            "name" => $request->name,
            "description" => $request->description,
            "stars" => $request->stars,
            "region_id" => $request->region_id,
        ]);

        $room = Room::where('hotel_id', $id)->first();

      $room->update([
        "total_number" => $request->number_of_rooms,
        "price" => $request->price_per_night,
       ]);

       if($request->deleted_photos){

        foreach($request->deleted_photos as $id){
            $this->delete_image($id);
        }

       }

       $region = Region::find($request->region_id);

       $country = $region->country->name;

       if($request->added_photos){

        foreach($request->added_photos as $photo){

            $this->save_image($photo, 'Hotels', $country . '/' . $region->name . '/' . $hotel->name, $hotel->id, "App/Models/Hotel" );
        }
    }

        $hotel->previleges()->detach($request->deleted_previleges);

        if($request->added_previleges){

            foreach($request->added_previleges as $pre){
                $add = Previlege::firstOrCreate([
                    "name" => $pre['name'],
                ]);

                $period = $pre['period'] ?? null;

                $hotel->previleges()->attach($add->id, ['period' => $period]);
            }
        }

        return $this->success("The hotel is updated successfully");


       }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
