<?php

namespace App\Http\Controllers\Both;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Region;
use App\Models\Photo;
use App\Models\Folder;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Exception;
use Illuminate\Support\Str;
use App\Traits\GeneralTrait;
class RegionController extends Controller
{
    use GeneralTrait;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        
        $countries = Region::where('region_id', null)->select('id', 'name')->OrderBy('name')->get();

        return $this->success("All countries", ["countries" => $countries]);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        
        $validator = Validator::make($request->all(),[
            'description' => ['required', 'max:300'],
            'country_id' => ['nullable', Rule::exists('regions', 'id')->where(fn ($query) => $query->where('deleted_at',  null))],
            'name' => ['required', Rule::unique('regions', 'name')->where(fn ($query) => $query->where('region_id', $request->country_id)->orWhere('id', $request->country_id))],
            'photos' => ['required', 'array'],
            'photos.*' => ['required', 'image'],
        ]);

        if($validator->fails()){
        return $this->fail($validator->errors()->first());
        }

       $region = Region::create([
            'name' => $request->name,
            'description' => $request->description,
            'region_id' => $request->country_id ?? null,
        ]);

     if($request->country_id){
        $country = Region::find($request->country_id);
     }
     else{
        $country = $region;
     }

     foreach($request->photos as $photo){
        
       $parent = Folder::firstOrCreate([
            "name" => $country->name,
            "folder_id" => 1,
        ]);

       $folder = Folder::firstOrCreate([
            "name" => $region->name,
            "folder_id" => $parent->id,
        ]);

        $this->save_image($photo, 'Regions',  $country->name . '/' . $region->name, $folder->id);
     }

     return $this->success("Region is added successfully");
        
    }

    /**
     * Display the specified resource.
     */
    public function show(Region $region)
    {
       
        $region->load('cities:id,name,region_id', 'country:id,name,region_id');

        if($region->country){
            $father = Folder::where('name', $region->country->name)->where('folder_id', 1)->first();
        }
        else{
            $father = Folder::where('name', $region->name)->where('folder_id', 1)->first();
        }

       $region->images = Folder::where('folder_id', $father->id)->where('name', $region->name)->first()->images;

       return $this->success("Region info", ['region' => $region]);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, String $id)
    {
        
            $messages = [
                'deleted_photos.*.exists' => 'You are deleting a photo that doesnt exist',
                'deleted_photos.*.required' => 'You must add the deleted photos if there are any',
                'added_photos.*.image' => 'The inserted files must all be images',
                'added_photos.*.required' => 'You must add the inserted photos if the exist',
            ];

        $region = Region::withTrashed()->findOrFail($id);

        $validator = Validator::make($request->all(),[
            'name' => ['required', Rule::unique('regions', 'name')->where(fn ($query) => $query->where('id', '!=', $region->id)->where('region_id', ($region->country()->withTrashed()->exists() ? $region->country()->withTrashed()->first()->id : null)))],
            'description' => ['required', 'max:200'],
            'deleted_photos' => ['nullable', 'array'],
            'deleted_photos.*' => ['required', Rule::exists('photos', 'id')],
            'added_photos' => ['nullable', 'array'],
            'added_photos.*' => ['required', 'image'],
        ], $messages);


        if($validator->fails()){
            return $this->fail($validator->errors()->first());
        }

        if($region->region_id){

            $country = Region::withTrashed()->find($region->region_id);
         }
         else{

            $country = $region;
         }

        $old_region = $region->name;

       $region->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        Storage::move('Regions/' . $country->name . '/' . $old_region, 'Regions/' . $country->name . '/' . $region->name);

        Folder::where('name', $old_region)->where('folder_id', '!=', null)->first()->update([
            'name' => $region->name,
        ]);

        if($request->deleted_photos){
    
        foreach($request->deleted_photos as $id){
           $this->delete_image($id);
        }
    }

         if($request->added_photos){

        foreach($request->added_photos as $photo){

            $parent = Folder::firstOrCreate([
                "name" => $country->name,
                "folder_id" => 1,
            ]);
    
           $folder = Folder::firstOrCreate([
                "name" => $region->name,
                "folder_id" => $parent->id,
            ]);

           $this->save_image($photo, 'Regions', $country->name . '/' . $region->name, $folder->id);
        }
    }

    return $this->success("Region is updated successfully");

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(String $id)
    {

            $region = Region::withTrashed()->findOrFail($id);

        if($region->cities()->withTrashed()->exists()|| $region->hotels()->withTrashed()->exists() || $region->package_areas()->exists() ){
            return $this->fail("You can't permenentaly delete this region, it's used at some places");
        }

           $country = null;

           $country_name = null;
            
            if($region->country()->withTrashed()->exists())
             $country = $region->country()->withTrashed()->first();

             if($country)
             $country_name = $country->name;

            Storage::deleteDirectory('Regions/' . $country_name . '/' . $region->name);

            if($country){
                $father = Folder::where('folder_id', 1)->where('name', $country->name)->first();
            Folder::where('name', $region->name)->where('folder_id', $father->id)->delete();
            } else{
                Folder::where('folder_id', 1)->where('name', $region->name)->first()->delete();
            }
        
            $region->forceDelete();

            return $this->success("Region is permanently deleted successfully");

    }

    public function archive(Region $region){

        $string = null;

        if($region->cities()->exists()){

            $string = " And it's cities";

            foreach($region->cities as $city){
                $city->delete();
            }
        }

        $region->delete();

        return $this->success("Region" . $string . " is temporariy deleted successfully");

}

    public function index_archived(){

        $regions = Region::onlyTrashed()->select('id', 'name')->get();

        return $this->success("Archived regions", ["regions" => $regions]);

    }

    public function show_archived(String $id){

        $region = Region::onlyTrashed()->findOrFail($id);

        if($region->country()->withTrashed()->exists()){
            $father = Folder::where('name', $region->country()->withTrashed()->first()->name)->where('folder_id', 1)->first();
        }
        else{
            $father = Folder::where('name', $region->name)->where('folder_id', 1)->first();
        }

       $region->images = Folder::where('name', $region->name)->where('folder_id', $father->id)->first()->images;
       
       $region->cities = $region->cities()->onlyTrashed()->select('id','name')->get();

       $region->country = $region->country()->withTrashed()->select('id', 'name')->get();

        return $this->success("Archived region info", ["region" => $region]);

    }

    public function restore_archived(String $id){

         $region = Region::onlyTrashed()->findOrFail($id);

         $string = null;

         if($region->country()->onlyTrashed()->exists()){

            $string = " With it's country";

            $region->country()->onlyTrashed()->first()->restore();
         }

         $region->restore();

        return $this->success("Region" . $string . " restored");

    }

        public function cities(){

            $cities = Region::where('region_id', '!=', null)->select('id','name')->get();

            return $this->success("All cities",  ["cities" => $cities]);

        }

    }

    
        





    

