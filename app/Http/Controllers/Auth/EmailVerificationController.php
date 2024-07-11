<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Ichtrojan\Otp\Otp;
use App\Notifications\SendCodeNotification;
use Exception;
class EmailVerificationController extends Controller
{
    public function verification_code_validation( Request $request ){
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
        if(auth()->user()->email_verified_at != null){

          return response()->json([
            "status" => false,
            "message" => "You are already verified",
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
        else{
          auth()->user()->update([
            "email_verified_at" => now(),
          ]);

          return response()->json([
            "status" => true,
            "message" => "Your email is verified successfully",
            "data" => null,
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
  

    public function resend_verification_code(){

      

      if(auth()->user()->email_verified_at != null){
        return response()->json([
          "status" => false,
          "message" => "You are already verified",
          "data" => null,
        ]);
      }

        auth()->user()->notify(new SendCodeNotification('Please verify your email using the code below'));

        return response()->json([
            "status" => true,
            "message" => "A verification code is sent to your email",
            "data" => null,
        ]);

   


  }
   
}
