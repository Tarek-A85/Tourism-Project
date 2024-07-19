<?php

namespace App\Http\Controllers\Both;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Hotel;
use App\Models\Region;
use App\Models\Folder;
use App\Models\Previlege;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Exception;
use Illuminate\Support\Facades\Validator;
use App\Traits\GeneralTrait;
use File;
class HotelController extends Controller
{
    use GeneralTrait;
    /**
     * Display a listing of the resource.
     */


    public function index()
    {
        $all_hotels = Hotel::withCount('previleges', 'reviews')->with('region:id,name,region_id,deleted_at')->OrderBy('stars', 'DESC')->OrderBy('previleges_count','DESC')->OrderBy('reviews_count','DESC')->get();

        foreach($all_hotels as $hotel){
            $hotel->image = $hotel->images[0];
        }

        $all_hotels = $all_hotels->select('id', 'name',  'stars', 'price_per_room', 'image', 'region');

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
            "name" => ['required', 'string', Rule::unique('hotels', 'name')->where(fn($query)=> $query->where('region_id', $request->region_id))],
            "description" => ['required', 'max:300'],
            "stars" => ['required', 'decimal:0,2', 'gte:0', 'lte:5'],
            "region_id" => ['required', Rule::exists('regions', 'id')->where(fn($query)=> $query->where('region_id', '!=', null)->where('deleted_at', null))],
            "price_per_room" => ['required', 'numeric', 'decimal:0,2'],
            "photos" => ['required', 'array'],
            "photos.*" => ['required', 'image'],
            "previleges" => ['nullable', 'array'],
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
        "price_per_room" => $request->price_per_room,
    ]);

    $region = Region::findOrFail($request->region_id);

    $country = $region->country->name;

    $parent = Folder::firstOrCreate([
        "name" => $country,
        "folder_id" => 4,
    ]);

    $city = Folder::firstOrCreate([
        "name" => $region->name,
        "folder_id" => $parent->id,
    ]);

   $folder = Folder::create([
        "name" => $hotel->name,
        "folder_id" => $city->id,
    ]);

    foreach($request->photos as $photo){

    $this->save_image($photo, 'Hotels', $country . '/' . $region->name . '/' . $hotel->name, $folder->id);

    }
      if($request->previleges){
        foreach($request->previleges as $previlege){
            $p = Previlege::firstOrCreate([
                "name" => $previlege["name"],
            ]);
            $period = $previlege["period"] ?? null;

            $hotel->previleges()->attach($p->id, ["period" => $period]);
        }
    }

        return $this->success("Hotel is added successfully");
   


    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

       $hotel = Hotel::with('previleges:id,name', 'region:name,id,region_id,deleted_at')->findOrFail($id);

       $hotel->append('images');

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
            "stars" => ['required', 'decimal:0,2', 'gte:0', 'lte:5'],
            "region_id" => ['required', Rule::exists('regions', 'id')->where(fn($query)=> $query->where('region_id', '!=', null))],
            "price_per_room" => ['required', 'numeric', 'decimal:0,2'],
            'deleted_photos' => ['nullable', 'array'],
            'deleted_photos.*' => ['required', Rule::exists('photos', 'id')],
            'added_photos' => ['nullable', 'array'],
            'added_photos.*' => ['required', 'image'],
            'previleges' => ['required', 'array'],
            'previleges.*.name' => ['required', 'string'],
        ]);

        if($validator->fails()){

            return $this->fail($validator->errors()->first());
        }

        $old_city = Region::findOrFail($hotel->region->id);

        $old_country = $old_city->country;

        $old_hotel = $hotel->name;

        $old_country_folder = Folder::where('folder_id', 4)->where('name', $old_country->name)->first();

        $old_city_folder = Folder::where('folder_id', $old_country_folder->id)->where('name', $old_city->name)->first();

        $hotel_folder = Folder::where('folder_id', $old_city_folder->id)->where('name', $hotel->name)->first();

         $hotel->update([
            "name" => $request->name,
            "description" => $request->description,
            "stars" => $request->stars,
            "region_id" => $request->region_id,
            "price_per_room" => $request->price_per_room,
        ]);

       if($request->deleted_photos){

        foreach($request->deleted_photos as $id){
            $this->delete_image($id);
        }

       }

       $new_city = Region::find($request->region_id);

       $new_country = $new_city->country->name;

       $new_country_folder = Folder::firstOrCreate([
        'name' => $new_country,
        'folder_id' => 4,
       ]);

       $new_city_folder = Folder::firstOrCreate([
        'name' => $new_city->name,
        'folder_id' => $new_country_folder->id,
       ]);

       $hotel_folder->update([
        'name' => $hotel->name,
        'folder_id' => $new_city_folder->id,
       ]);

        Storage::move('Hotels/' . $old_country->name . '/' . $old_city->name . '/' . $old_hotel, 
                      'Hotels/' . $new_country . '/' . $new_city->name . '/' . $hotel->name);

       if($request->added_photos){

        foreach($request->added_photos as $photo){

            $this->save_image($photo, 'Hotels', $new_country . '/' . $new_city->name . '/' . $hotel->name, $hotel_folder->id);
        }
    }

        $hotel->previleges()->delete();

            foreach($request->previleges as $pre){
                $add = Previlege::firstOrCreate([
                    "name" => $pre['name'],
                ]);

                $period = $pre['period'] ?? null;

                $hotel->previleges()->attach($add->id, ['period' => $period]);
            }
       

        return $this->success("The hotel is updated successfully");


       }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $hotel = Hotel::withTrashed()->find($id);

        if($hotel->package_areas()->exists() || $hotel->hotel_transactions()->exists()){
            return $this->fail("You can't permenentaly delete this hotel, it's used at some places");
        }

        $city = $hotel->region;

        $country = $city->country;

        $country_folder = Folder::where('folder_id', 4)->where('name', $country->name)->first();

        $city_folder = Folder::where('folder_id', $country_folder->id)->where('name', $city->name)->first();

        $hotel_folder = Folder::where('folder_id', $city_folder->id)->where('name', $hotel->name)->first();

        Storage::deleteDirectory('Hotels/' . $country->name . '/' . $city->name . '/' . $hotel->name);

        $hotel_folder->delete();

        $hotel->forceDelete();

        return $this->success("The hotel is deleted successfully");

    }

    public function archive(Hotel $hotel){

        $hotel->delete();

        return $this->success("Hotel is temporariy deleted successfully");

    }

    public function index_archived(){
        
        $hotels = Hotel::onlyTrashed()->with('previleges:id,name', 'region:name,id,region_id,deleted_at')->get();

        return $this->success("Archived Hotels", ["Hotels" => $hotels]);
    }

    public function restore_archived(String $id){

        $hotel = Hotel::onlyTrashed()->findOrFail($id);

        $hotel->restore();

        return $this->success("Hotel restored");
    }
}
