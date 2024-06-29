<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\User\GoogleSignInRequest;
use App\Models\User;
class GoogleController extends Controller
{
    public function sign_up(Request $request){

        try{

        $validator = Validator::make($request->all(), [
            'email' => 'required|unique:users',
            'google_id' => 'required|unique:users',
        ]);

        if($validator->fails()){
            return response()->json([
              "status" => false,
              "message" => $validator->errors()->first(),
              "data" => null,
            ]);
          }

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'google_id' => $request->google_id,
        ]);

       

        $token = $user->createToken('google_token')->plainTextToken;

        return response()->json([
            "statue" => true,
            "message" => "your are registered",
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
        ]);

        if($validator->fails()){
            return response()->json([
              "status" => false,
              "message" => $validator->errors()->first(),
              "data" => null,
            ]);
          }

        if(!Auth::attempt(['email' => $request->email, 'google_id' => $request->google_id])){

            return response()->json([
                "status" => false,
                "message" => "Email and password does not match",
                "data" => null,
            ]);
        }
        else{

            $token = auth()->user()->createToken('sign_in_token')->plainTextToken;

            return response()->json([
                "status" => true,
                "message" => "You are loggen in successfully",
                "data" => ["token" => $token],

            ]);


        }

    } catch(\Exception $e){

        return response()->json([
          "status" => false,
          "message" => "Something went wrong",
          "data" => null,
        ]);
      }
}
}
