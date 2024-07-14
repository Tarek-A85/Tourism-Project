<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Traits\GeneralTrait;
class GoogleController extends Controller
{
  use GeneralTrait;

    public function sign_up(Request $request){

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => ['required', Rule::unique('users', 'email')->where(fn ($query) => $query->where('is_admin', false))],
            'google_id' => ['required', Rule::unique('users', 'google_id')->where(fn ($query) => $query->where('is_admin', false))],
        ]);

        if($validator->fails()){
            return $this->fail($validator->errors()->first());
          }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'google_id' => $request->google_id,
            'email_verified_at' => now(),
        ]);

        $token = $user->createToken('google_token')->plainTextToken;

        return $this->success("Your are registered successfully", ["token" => $token]);

    }

    public function sign_in(Request $request){

        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'google_id' => 'required',
            'is_admin' => 'required|boolean',
        ]);

        if($validator->fails()){
            return $this->fail($validator->errors()->first());
          }

          $user = User::where('email', $request->email)->where('google_id', $request->google_id)->where('is_admin',$request->is_admin)->first();

          if($request->is_admin)
          $rule= 'admin';
        else
        $rule= 'user';

          if(!$user){
             return $this->fail("There is no $rule with these credentials");
          }

          $token = $user->createToken('SignInToken')->plainTextToken;

           return $this->success( "You are logged in successfully", ["token" => $token]);
       
   }
}
