<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
class GoogleController extends Controller
{
    public function sign_up(Request $request){

        try{

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => ['required', Rule::unique('users', 'email')->where(fn ($query) => $query->where('is_admin', false))],
            'google_id' => ['required', Rule::unique('users', 'google_id')->where(fn ($query) => $query->where('is_admin', false))],
        ]);

        if($validator->fails()){
            return response()->json([
              "status" => false,
              "message" => $validator->errors()->first(),
              "data" => null,
            ]);
          }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'google_id' => $request->google_id,
            'email_verified_at' => now(),
        ]);

       

        $token = $user->createToken('google_token')->plainTextToken;

        return response()->json([
            "statue" => true,
            "message" => "Your are registered successfully",
            "data" => ["token" => $token],
        ]);

    } catch(\Exception $e){

        return response()->json([
          "status" => false,
          "message" => "Something went wrong",
          "data" => null,
        ]);
      }

    }

    public function sign_in(Request $request){
        try{

        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'google_id' => 'required',
            'is_admin' => 'required|boolean',
        ]);

        if($validator->fails()){
            return response()->json([
              "status" => false,
              "message" => $validator->errors()->first(),
              "data" => null,
            ]);
          }

          $user = User::where('email', $request->email)->where('google_id', $request->google_id)->where('is_admin',$request->is_admin)->first();

          if($request->is_admin)
          $rule= 'admin';
        else
        $rule= 'user';

          if(!$user){
            return response()->json([
              "status" => false,
              "message" => "There is no $rule with these credentials",
              "data" => null,
          ]);
          }

            $token = $user->createToken('SignInToken')->plainTextToken;

            return response()->json([
                "status" => true,
                "message" => "You are logged in successfully",
                "data" => ["token" => $token],

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
