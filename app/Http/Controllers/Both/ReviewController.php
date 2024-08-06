<?php

namespace App\Http\Controllers\Both;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Hotel;
use App\Models\Company;
use App\Models\Region;
use App\Models\Package;
use Illuminate\Support\Facades\Storage;
use App\Models\Folder;
use App\Models\Status;
use App\Models\TripDetail;
use App\Models\TransactionType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
class ReviewController extends Controller
{

    public function can_review_package(String $id){

            //$detail = TripDetail::where('id', $id)->where('deleted_at', null)->firstOrFail();

            $detail = TripDetail::findOrFail($id);

            $wallet = auth()->user()->wallet;

            $package = Package::findOrFail($detail->detailable_id);
 
            if(!$wallet){
             return $this->fail('You have to complete the trip provided by the package to review it');
            }

             $package_transactions = $wallet->completed_package_transactions;
             
             if($package_transactions->count() == 0)
             return $this->fail('You have to complete the trip provided by the package to review it');

 
             if($package_transactions->where('trip_detail_id', $detail->id)->count() == 0){
                 return $this->fail('You have to complete the trip provided by the package to review it');
             }

             $review = Review::where('reviewable_type', 'Package')->where('reviewable_id', $package->id)->where('user_id', auth()->user()->id)->first();

             if($review){
                return $this->success("Your old review", ["review" => $review]);
             }

             return $this->success();

    }

    public function index(Request $request, String $id){

        $validator = Validator::make($request->all(), [
            'type' => ['required', Rule::in(['Hotel', 'Package', 'Company', 'Region'])],
        ]);

        if($validator->fails()){
            return $this->fail($validator->errors()->first());
        }

        $reviews = Review::with('user')->where('reviewable_type', $request->type)->where('reviewable_id', $id)->where('title', '!=' , null)->get();

        foreach($reviews as $review){
            $review->user->image = $review->user->image;
        }

        return $this->success("All reviews", ["reviews" => $reviews]);
    }

    public function store_rating(Request $request){

        $validator = Validator::make($request->all(), [
            //'trip_detail_id' => ['required', Rule::exists('trip_details', 'id')->where(fn ($query) => $query->where('deleted_at', null))],
            'trip_detail_id' => ['required', 'exists:trip_details,id'],
            'stars' => ['required', 'numeric', 'min:0', 'max:5'],
        ]);

        if($validator->fails()){
            return $this->fail($validator->errors()->first());
        }

        $detail = TripDetail::findOrFail($request->trip_detail_id);

        $package = Package::findOrFail($detail->detailable_id);

       

        if(Review::where('user_id', auth()->user()->id)->where('reviewable_type', 'Package')->where('reviewable_id', $package->id)->first()){
            return $this->fail("You rated this package before");
        }

       Review::create([
            'user_id' => auth()->user()->id,
            'reviewable_type' => 'Package',
            'reviewable_id' => $package->id,
            'stars' => $request->stars,
        ]);

        return $this->success('Your rating is sent successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store_review(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            'type' => ['required', Rule::in(['Hotel', 'Package', 'Company', 'Region']), 'bail'],
            'id' => [ 'required'],
            'title' => ['required', 'string', 'max:50'],
            'description' => ['required', 'string', 'max:200'],
            'photo' => ['nullable', 'image'],
        ]);


        if($validator->fails()){
            return $this->fail($validator->errors()->first());
        }

        $id = $request->id;

        if($request->type == 'Pacakge'){

            $detail = TripDetail::findOrFail($request->id);

            $id = Package::findOrFail($detail->detailable_id);
        }

     $old_review = Review::where('reviewable_type', $request->type)->where('reviewable_id', $id)->where('user_id', auth()->user()->id)->first();

       if($request->type == 'Package' && $old_review == null ){
        return $this->fail("Please give this package a rate before you write the review");
       }

       if($old_review && $old_review->title != null){
        return $this->fail('One review for every place is allowed');
       }

       if($old_review){

        $review = $old_review;

        $review->update([
            'title' => $request->title,
            'description' => $request->description,
        ]);
       }

       else{

        $review = Review::create([
            'user_id' => auth()->user()->id,
            'reviewable_type' => $request->type,
            'reviewable_id' => $id,
            'title' => $request->title,
            'description' => $request->description,
            'stars' => null,
        ]);

       }

         if($request->photo){

            $folder = Folder::firstOrCreate([
                'name' => $review->id,
                'folder_id' => Folder::where('name', 'Reviews')->first()->id,
            ]);

            $this->save_image($request->photo, 'Reviews', $review->id, $folder->id);

         }

         return $this->success('Your review is posted');

    }

    /**
     * Display the specified resource.
     */
    public function show(Review $review)
    {
        
        if($review->title == null){
            return $this->fail("There is no object like that");
        }

        $review->load('user');

        $review->user->image = $review->user->image;

        return $this->success('review info', ["review" => $review]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update_review(Request $request, Review $review)
    {
        if($review->title == null){
            return $this->fail('There is no object like that');
        }

        if($review->user_id != auth()->user()->id){
            return $this->fail('You are not authorized');
        }
        
        $validator = Validator::make($request->all(), [
            'title' => ['required', 'string', 'max:50'],
            'description' => ['required', 'string', 'max:200'],
            'deleted_photo' => ['nullable', 'exists:photos,id'],
            'added_photo' => ['nullable', 'image'],
        ]);

       
        if($validator->fails()){
            return $this->fail($validator->errors()->first());
        }

        $review->update([
            'title' => $request->title,
            'description' => $request->description,
        ]);

        if($request->deleted_photo){
            $this->delete_image($request->deleted_photo);
        }

        if($request->added_photo){

            $folder = Folder::firstOrCreate([
                'name' => $review->id,
                'folder_id' => Folder::where('name', 'Reviews')->first()->id,
            ]);

            $this->save_image($request->added_photo, 'Reviews', $review->id, $folder->id);
        }

        return $this->success('Your review is updated successfully');
    }

    public function update_rating(Request $request, Review $review){

        if($review->user_id != auth()->user()->id){
            return $this->fail('You are not authorized');
        }

        if($review->stars == null){
            return $this->fail('There is no object like that');
        }

        if($review->reviewable_type != 'Package'){
            return $this->fail('You cant rate anything except packages');
        }
        
        $validator = Validator::make($request->all(), [
            'stars' => ['required', 'numeric', 'min:0', 'max:5'],
        ]);

        if($validator->fails()){
            return $this->fail($validator->errors());
        }

        $review->update([
            'stars' => $request->stars,
        ]);

        return $this->success('Your rating is updated successfully');
    }

   
    /**
     * Remove the specified resource from storage.
     */
    public function destroy_review(Review $review)
    {
        if(!auth()->user()->is_admin && $review->user_id != auth()->user()->id){
            return $this->fail('You are not authorized');
        }

        if($review->title == null){
            return $this->fail('There is no object like that');
        }

        $parent = Folder::where('name', 'Reviews')->where('folder_id', null)->first()->id;

        $folder = Folder::where('name', $review->id)->where('folder_id', $parent)->first();

        Storage::deleteDirectory('Reviews/' . $review->id);

        if($folder){
            $folder->delete();
        }

        if($review->stars == null){
          $review->delete();
        }
        else{
            $review->update([
                'title' => null,
                'description' => null,
            ]);
        }

        return $this->success('Your review is deleted successfully');

    }

    public function destroy_rating(Review $review){

        if(!auth()->user()->is_admin && $review->user_id != auth()->user()->id){
            return $this->fail('You are not authorized');
        }

        if($review->stars == null){
            return $this->fail('There is no object like that');
        }

        $review->delete();

        return $this->fail("Your rating with the review (if exists) is deleted successfully");

    }
}
