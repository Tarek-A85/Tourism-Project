<?php

namespace App\Http\Controllers\Both;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\Company;
use App\Models\Region;
use App\Models\Folder;
use App\Models\Flight;
use App\Models\FlightDetail;
use App\Models\FlightTime;
use App\Models\FlightType;
use Illuminate\Support\Facades\Storage;
class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $companies = Company::with('country:id,name,deleted_at')->OrderBy('name')->get();

        foreach($companies as $company){
            $company->image = $company->images[0];
        }

        return $this->success("All companies", ["companies" => $companies]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
           'name' => ['required', 'unique:companies'],
           'description' => ['required', 'max:200'],
           'country_id' => ['required', Rule::exists('regions', 'id')->where(fn ($query)=> $query->where('region_id', null)->where('deleted_at', null))],
           'photos' => ['required', 'array']
        ]);

        if($validator->fails()){
            return $this->fail($validator->errors()->first());
        }

      $company = Company::create([
            'name' => $request->name,
            'description' => $request->description,
            'country_id' => $request->country_id,
        ]);

        $country = Region::findOrFail($request->country_id);

        $parent = Folder::firstOrCreate([
            'name' => $country->name,
            'folder_id' => 6,

        ]);

       $folder = Folder::create([
            'name' => $request->name,
            'folder_id' => $parent->id,
        ]);

        foreach($request->photos as $photo){
            $this->save_image($photo, 'Companies/' . $country->name, $request->name, $folder->id );
        }

        $flights = Flight::factory()->count(3)->create([
            'company_id' => $company->id,
        ]);
        
        foreach($flights as $flight){

           $times = FlightTime::factory()->count(5)->create([
                'flight_id' => $flight->id,
            ]);
            
            foreach($times as $time){

              $first_class =  FlightDetail::factory()->create([
                    'flight_time_id' => $time->id,
                    'flight_type_id' => FlightType::where('name', 'First class')->first()->id, 
                ]);


                FlightDetail::factory()->EconomyPrice($first_class->adult_price,
                                                      $first_class->child_price,
                                                      $time->id)->create();
            }


        }

        return $this->success("The company is added successfully");
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        if(auth()->user()->is_admin){
            $company = Company::withTrashed()->with('country:id,name,deleted_at')->findOrFail($id);
        }
        else{
            $company = Company::with('country:id,name,deleted_at')->findOrFail($id);
        }

        $company->append('images');

        return $this->success("Company info", ["company" => $company]);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $company = Company::withTrashed()->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string'],
            'description' => ['required', 'max:200'],
            'country_id' => ['required', Rule::exists('regions', 'id')->where(fn ($query)=> $query->where('region_id', null)->where('deleted_at', null))],
            'deleted_photos' => ['nullable', 'array'],
            'deleted_photos.*' => ['required', Rule::exists('photos', 'id')],
            'added_photos' => ['nullable', 'array'],
            'added_photos.*' => ['required', 'image'],
        ]);

        if($validator->fails()){
            return $this->fail($validator->errors()->first());
        }

        $old_country_folder = Folder::where('folder_id', 6)->where('name', $company->country->name)->first();

        $old_folder = Folder::where('folder_id', $old_country_folder->id)->where('name', $company->name)->first();

        $old_company = $company->name;

        $new_country = Region::find($request->country_id);

        $company->update([
            'name' => $request->name,
            'description' => $request->description,
            'country_id' => $request->country_id,
        ]);

        if($request->deleted_photos){
            foreach($request->deleted_photos as $id){
              $this->delete_image($id);
            }
        }

        $new_country_folder = Folder::firstOrCreate([
            'folder_id' => 6,
            'name' => $new_country->name,
        ]);

       

        $old_folder->update([
            'name' => $request->name,
            'folder_id' => $new_country_folder->id,
        ]);

        Storage::move('Companies/' . $old_country_folder->name . '/' . $old_company,
                      'Companies/' . $new_country->name . '/' . $request->name);

        if($request->added_photos){
            foreach($request->added_photos as $photo){
                $this->save_image($photo, 'Companies', $new_country->name . '/' . $request->name, $old_folder->id);
            }
        }

        return $this->success("Company is updated successfully");


    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
       
        $company = Company::withTrashed()->findOrFail($id);

        if($company->packages()->exists()){
            return $this->fail("You can't permenentaly delete this company, it's used at some places");
        }

        foreach($company->flights as $flight){

            foreach($flight->flight_times as $time){

                foreach($time->flight_details as $detail)

                if($detail->flight_transactions()->exists()){
                    return $this->fail("You can't permenentaly delete this company, it's used at some places");
                }
            }
        }

        $country = Folder::where('folder_id', 6)->where('name', $company->country->name)->first();

        $company_folder = Folder::where('folder_id', $country->id)->where('name', $company->name)->first();

        Storage::deleteDirectory('Companies/' . $country->name . '/' . $company_folder->name);

        $company_folder->delete();

        $company->forceDelete();

        return $this->success("The company is deleted successfully");
    }

    public function archive (Company $company){

     foreach( $company->flights as $flight){

        foreach($flight->flight_times as $time){

            $time->flight_details()->delete();
        }

        $flight->flight_times()->delete();
       
     }

       $company->flights()->delete();

        $company->delete();

        return $this->success("Company is temporarily deleted successfully");
    }

    public function index_archived(){

        $companies = Company::onlyTrashed()->with('country:id,name,deleted_at')->get();

        foreach($companies as $company){
            $company->image = $company->images[0];
        }

        return $this->success("All archived companies", ["companies" => $companies]);
    }

    public function restore(String $id){

        $company = Company::onlyTrashed()->findOrFail($id);

        $company->restore();

        $company->flights()->restore();

        foreach( $company->flights as $flight){

            $flight->flight_times()->restore();

            foreach($flight->flight_times as $time){
    
                $time->flight_details()->restore();
            }
           
         }

        

        return $this->success("Company restored");

    }
}
