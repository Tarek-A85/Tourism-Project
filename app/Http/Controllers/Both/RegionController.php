<?php

namespace App\Http\Controllers\Both;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Region;
use App\Models\Photo;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Exception;
use Illuminate\Support\Str;
class RegionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try{

        $countries = Region::where('region_id', null)->select('id', 'name')->get();

        return response()->json([
            "status" => true,
            "message" => "All countries",
            "data" => ["countries" => $countries ] ,
        ]);

    } catch(\Exception $e){
        return response()->json([
            "status" => false,
            "message" => "Something went wrong",
            "data" => null,
        ]);
    }

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try{
        $validator = Validator::make($request->all(),[
            'description' => ['required', 'max:300'],
            'country_id' => ['nullable', Rule::exists('regions', 'id')],
            'name' => ['required', Rule::unique('regions', 'name')->where(fn ($query) => $query->where('region_id', $request->country_id))],
            'photos' => ['required', 'array'],
        ]);


        if($validator->fails()){
            return response()->json([
                "status" => false,
                "message" => $validator->errors()->first(),
                "data" => null,
            ]);
        }

       
       $region = Region::create([
            'name' => $request->name,
            'description' => $request->description,
            'region_id' => $request->country_id ?? null,
        ]);

     if($request->country_id){
        $country = Region::find($request->country_id)->name;
        $city = $request->name;
     }
     else{
        $country = $city = $request->name;
     }

     foreach($request->photos as $photo){
        $extension = $photo->getClientOriginalExtension();
        $add = Str::uuid()->toString();
        $name = $add . '.' . $extension;
       $path = $photo->storeAs('Regions', $country . '/' . $city . '/' . $name);
        Photo::create([
            'photoable_type' => 'App\Models\Region',
            'photoable_id' => $region->id,
            'path' => $path,
        ]);
     }

     return response()->json([
        "status" => true,
        "message" => "Region is added successfully",
        "data" => null,
     ]);



    } catch(\Exception $e){
        return response()->json([
            "status" => false,
            "message" => "Something went wrong",
            "data" => null,
        ]);
    }



        
    }

    /**
     * Display the specified resource.
     */
    public function show(Region $region)
    {
        try{
        $region->load('photos:id,path,photoable_id', 'cities:id,name,region_id', 'country:id,name,region_id');


        return response()->json([
            "status" => true,
            "message" => "Region info",
            "data" => ['region'=>$region],
        ]);

    } catch(\Exception $e){
        return response()->json([
            "status" => false,
            "message" => "Something went wrong",
            "data" => null,
        ]);

    }

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, String $id)
    {
        try{
            $messages = [
                'deleted_photos.*.exists' => 'Deleted photo must be for this region',
                'deleted_photos.*.required' => 'You must add the deleted photos if there are any',
                'added_photos.*.image' => 'The inserted files must all be images',
                'added_photos.*.required' => 'You must add the inserted photos if the exist',
            ];

        $region = Region::withTrashed()->find($id);

        if(!$region){
            return response()->json([
                "status" => false,
                "message" => "There is no object like that",
                "data" => null,
            ]);
        }

        $validator = Validator::make($request->all(),[
            'name' => ['required', Rule::unique('regions', 'name')->where(fn ($query) => $query->where('id', '!=', $region->id)->where('region_id', ($region->country()->withTrashed()->exists() ? $region->country()->withTrashed()->first()->id : null)))],
            'description' => ['required', 'max:200'],
            'deleted_photos' => ['nullable', 'array'],
            'deleted_photos.*' => ['required', Rule::exists('photos', 'id')->where(fn ($query) => $query->where('photoable_id',  $region->id))],
            'added_photos' => ['nullable', 'array'],
            'added_photos.*' => ['required', 'image'],
        ], $messages);


        if($validator->fails()){
            return response()->json([
                "status" => false,
                "message" => $validator->errors()->first(),
                "data" => null,
            ]);
        }

       $region->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        if($request->deleted_photos){

        foreach($request->deleted_photos as $id){
            $photo = Photo::find($id);
            $path = $photo->path;
            Storage::delete($path);
            $photo->delete();
        }
    }

        if($region->region_id){

            $country = Region::withTrashed()->find($region->region_id)->name;
            $city = $request->name;
         }
         else{

            $country = $city = $request->name;
         }
         if($request->added_photos){

        foreach($request->added_photos as $photo){

            $extension = $photo->getClientOriginalExtension();

            $add = Str::uuid()->toString();

            $name = $add . '.' . $extension;

           $path = $photo->storeAs('Regions', $country . '/' . $city . '/' . $name);

            Photo::create([
                'photoable_type' => 'App\Models\Region',
                'photoable_id' => $region->id,
                'path' => $path,
            ]);
        }
    }

        return response()->json([
            "status" => true,
            "message" => "Region is updated successfully",
            "data" => null,
        ]);

    } catch(\Exception $e){

        return response()->json([
            "status" => false,
            "message" => "Something went wrong",
            "data" => null,
        ]);

    }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(String $id)
    {
        try{

            $region = Region::withTrashed()->find($id);

            if(!$region){

                return response()->json([
                    "status" => false,
                    "message" => "There is no object like that",
                    "data" => null,
                ]);
            }

        if($region->cities()->withTrashed()->exists()|| $region->hotels()->withTrashed()->exists() || $region->package_areas()->exists() ){
            return response()->json([
                "status" => false,
                "message" => "You can't permenentaly delete this region, it's used at some places",
                "data" => null,
            ]);
        }

           $country = null;
            
            if($region->country()->withTrashed()->exists())
             $country = $region->country()->withTrashed()->first()->name;

            Storage::deleteDirectory('Regions/' . $country . '/' . $region->name);

            $region->photos()->delete();

            $region->forceDelete();

            return response()->json([
                'status' => true,
                'message' => "Region is permanently deleted successfully",
                'data' => null,
            ]);

        } catch(\Exception $e){
            return response()->json([
                "status" => false,
                "message" => "Something went wrong",
                "data" => null,
            ]);
        }

    }

    public function archive(Region $region){

        try{

        $string = null;

        if($region->cities()->exists()){

            $string = " And it's cities";

            foreach($region->cities as $city){
                $city->delete();
            }
        }

        $region->delete();

        return response()->json([
            "status" => false,
            "message" => "Region" . $string . " is temporariy deleted successfully",
            "data" => null,

        ]);

    } catch(\Exception $e){
            return response()->json([
                "status" => false,
                "message" => "Something went wrong",
                "data" => null,
            ]);
        }
}

    public function index_archived(){

        try{

        $regions = Region::onlyTrashed()->select('id', 'name')->get();

        return response()->json([
            "status" => true,
            "message" => "Archived regions",
            "data" => ["regions" => $regions],
        ]);

        } catch(\Exception $e){

            return response()->json([
            "status" => false,
            "message" => "Something went wrong",
            "data" => null,
        ]);
    }

    }

    public function show_archived(String $id){

        try{

        $region = Region::withTrashed()->where('id', $id)->first();

        if(!$region){
            return response()->json([
                "status" => false,
                "message" => "There is no object like that",
                "data" => null,
            ]);
        }
        if($region->deleted_at == null){
            return response()->json([
                "status" => false,
                "message" => "The region isn't in the archive",
                "data" => null,
            ]);
        }

       $region->load('photos:id,path,photoable_id');
       
       $region->cities = $region->cities()->onlyTrashed()->select('id','name')->get();

       $region->country = $region->country()->withTrashed()->select('id', 'name')->get();

       

        return response()->json([
            "status" => true,
            "message" => "Archived region info",
            "data" => ["region" => $region],

        ]);

        } catch(\Exception $e){

            return response()->json([
                "status" => false,
                "message" => "Something went wrong",
                "data" => null,
            ]);
        }
        }

    public function restore_archived(String $id){

        try{

         $region = Region::onlyTrashed()->find($id);

         if(!$region){

            return response()->json([
                "status" => false,
                "message" => "There is no object like that",
                "data" => null,
            ]);
         }

         $string = null;

         if($region->country()->onlyTrashed()->exists()){

            $string = " With it's country";

            $region->country()->onlyTrashed()->first()->restore();
         }

         $region->restore();

         return response()->json([
            "status" => true,
            "message" => "Region" . $string . " restored",
            "data" => null,
         ]);

        } catch(\Exception $e){
            return response()->json([
                "status" => false,
                "message" => "Something went wrong",
                "data" => null,
            ]);
        }

        }
    }

    
        





    

