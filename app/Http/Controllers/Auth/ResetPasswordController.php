<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Notifications\SendCodeNotification;
use Ichtrojan\Otp\Otp;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
class ResetPasswordController extends Controller
{
    public function send_code(){

        try{

        auth()->user()->notify(new SendCodeNotification ("Please use the code below to allow you to change the password") );

        return response()->json([
            "message" => "A code is sent to your email, use it to allow you to change the password"
        ], 200);

    } catch(\Exception $e){
        
        return response()->json([
          "status" => false,
          "message" => "Something went wrong",
          "data" => null,
        ]);
  
      }

    }

    public function verify_code(Request $request){

        try{
            $validator = Validator::make($request->all(), [
                'code' => 'required',
             ]);
     
             if($validator->fails()){
               return response()->json([
                 "status" => false,
                 "message" => $validator->errors()->first(),
                 "data" => null,
               ]);
             }
        $otp = (new Otp)->validate(auth()->user()->email, $request->code);

        if(!$otp->status){
            return response()->json([
                "status" => false,
                "message" => $otp->message,
                "data" => null,
            ]);
        }
     
            $reset_token = Str::random(10);

            DB::table('password_reset_tokens')->insert([
                'email' => auth()->user()->email ,
                'token' => $reset_token,
            ]);

            return response()->json([
                "status" => true,
                "message" => "you can reset your password now",
                "data" => ["reset_token" => $reset_token],
            ]);

    } catch(\Exception $e){

        return response()->json([
          "status" => false,
          "message" => "Something went wrong",
          "data" => null,
        ]);
  
      }

    }

    public function reset_password(Request $request){

        try{
        $validator = Validator::make($request->all,[
            'new_password' => 'required',
        ]);

        if($validator->fails()){
            return response()->json([
                "status" => false,
                "message" => $validator->errors()->first(),
                "data" => null,
            ]);
        }

        $check = DB::table('password_reset_tokens')->where('email', auth()->user()->email)->first();

        if($request->reset_token == null || $check->token != $request->reset_token){
            return response()->json([
                "status" => false,
                "message" => "you are not authorized to reset your password",
                "data" => null,
            ]);
        }

        auth()->user()->update([
            "password" => bcrypt($request->password),
        ]);

        return response()->json([
            "status" => true,
            "message" => "your password is changed successfully",
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
