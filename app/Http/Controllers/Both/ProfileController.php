<?php

namespace App\Http\Controllers\Both;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Folder;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
class ProfileController extends Controller
{
    public function show(User $user){

        $user->image = $user->image;

        $user =  $user->setHidden(['password', 'email_verified_at', 'google_id', 'remember_token']);

        return $this->success('profile', ['profile' => $user]);
    }

    public function update(Request $request, User $user){

        if($user->deleted_at){
            return $this->fail('There is no object like that');
        }

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string'],
            'phone_number' => [
                'nullable',
                'digits_between:10,15',
                Rule::unique('users', 'phone_number')
                    ->where(fn ($query) => $query->where('is_admin', $request->is_admin)->where('id', '!=', $user->id))
            ],
            'birth_date' => ['nullable', 'date'],
            'deleted_image_id' => ['nullable', 'exists:photos,id'],
            'added_image' => ['nullable', 'image'],
        ]);

        if($validator->fails()){
            return $this->fail($validator->errors()->first());
        }

        if($user->name == $request->name && 
           $user->phone_number == $request->phone_number &&
           $user->birth_date == $request->birth_date &&
           $request->deleted_image_id == null &&
           $request->added_image == null){
            return $this->fail('There is nothing changed to update');
           }

        if($request->deleted_image_id){
            $this->delete_image($request->deleted_image_id);
        }

        if($request->added_image){
            $parent = Folder::where('name', 'Users')->where('folder_id', null)->first();

            $folder = Folder::FirstOrCreate([
            'folder_id'=> $parent->id,
            'name'=> 'user ' . $user->id
        ]);

            $this->save_image($request->added_image, 'Users', 'user ' . $user->id, $folder->id);
        }

        $user->update([
            'name' => $request->name,
            'phone_number' => $request->phone_number,
            'birth_date' => $request->birth_date,
        ]);

        return $this->success('Your profile is updated successfully');
    }
}
