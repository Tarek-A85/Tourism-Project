<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Notifications\SendCodeNotification;
use Ichtrojan\Otp\Otp;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use App\Traits\GeneralTrait;
class ResetPasswordController extends Controller
{
    use GeneralTrait;

    public function send_code(){

        auth()->user()->notify(new SendCodeNotification ("Please use the code below to allow you to change the password") );

        return $this->success("A code is sent to your email, use it to allow you to change the password");

    }

    public function validate_code(Request $request){

            $validator = Validator::make($request->all(), [
                'code' => 'required',
             ]);
     
             if($validator->fails()){
            return $this->fail($validator->errors()->first());
             }

        $otp = (new Otp)->validate(auth()->user()->email, $request->code);

        if(!$otp->status){
            return $this->fail( $otp->message);
        }
     
            $reset_token = Str::random(10);

            DB::table('password_reset_tokens')->insert([
                'email' => auth()->user()->email ,
                'token' => $reset_token,
            ]);

        return $this->success("you can reset your password now",  ["reset_token" => $reset_token]);

    }

    public function reset_password(Request $request){

        $validator = Validator::make($request->all(),[
            'new_password' => ['required', 'confirmed', Password::min(8)],
        ]);

        if($validator->fails()){
            return $this->fail($validator->errors()->first());
        }

        $check = DB::table('password_reset_tokens')->where('email', auth()->user()->email)->first();

        if(!$check || $request->reset_token == null || $check->token != $request->reset_token){
            return $this->fail("you are not authorized to reset your password");
        }

        auth()->user()->update([
            "password" => bcrypt($request->new_password),
        ]);

        DB::table('password_reset_tokens')->where('email', auth()->user()->email)->delete();

        auth()->user()->tokens()->delete();

        return $this->success("your password is changed successfully");

    }
    

   
}
