<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Ichtrojan\Otp\Otp;
use App\Notifications\SendCodeNotification;
use Exception;
use App\Traits\GeneralTrait;
class EmailVerificationController extends Controller
{
  use GeneralTrait;

    public function verification_code_validation( Request $request ){

        $validator = Validator::make($request->all(), [
           'code' => 'required',
        ]);

        if($validator->fails()){
          return $this->fail($validator->errors()->first());
        }

        if(auth()->user()->email_verified_at != null){
          return $this->fail("You are already verified");
        }

        $otp = (new Otp)->validate(auth()->user()->email, $request->code);

        if(!$otp->status){
            return $this->fail($otp->message);
        }
        else{
          auth()->user()->update([
            "email_verified_at" => now(),
          ]);

          return $this->success("Your email is verified successfully");

        }


  }
  

    public function resend_verification_code(){

      if(auth()->user()->email_verified_at != null){
        return $this->fail("You are already verified");
      }

        auth()->user()->notify(new SendCodeNotification('Please verify your email using the code below'));

        return $this->success("A verification code is sent to your email");

  }
   
}
