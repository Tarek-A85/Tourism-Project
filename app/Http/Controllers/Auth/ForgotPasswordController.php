<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Notifications\SendCodeNotification;
use Ichtrojan\Otp\Otp;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Traits\GeneralTrait;
class ForgotPasswordController extends Controller
{
    use GeneralTrait;

    public function send_forgotten_password_code(Request $request){

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if($validator->fails()){
            return $this->fail( $validator->errors()->first());
        }

        $user = User::where('email', $request->email)->first();

        $user->notify(new SendCodeNotification('Please use the code below to allow you to change the password'));

        return $this->success("A code is sent to your email, use it to allow you to change the password");

    
    }

    public function validate_forgotten_password_code(Request $request){

            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'code' => 'required'
            ]);

            if($validator->fails()){
                return $this->fail( $validator->errors()->first());
            }

            $user = User::where('email', $request->email)->first();

            $otp = (new Otp)->validate($user->email, $request->code);

            if(!$otp->status){
                return $this->fail($otp->message);
            }

            $reset_token = Str::random(10);

            DB::table('password_reset_tokens')->insert([
                'email' => $user->email ,
                'token' => $reset_token,
            ]);

            return $this->success("you can reset your password now",  ["reset_token" => $reset_token]);
    }

    public function change_forgotting_password(Request $request){

            $validator = Validator::make($request->all(), [
                'email' => 'required|email', 
                'new_password' => 'required|confirmed',
            ]);

            if($validator->fails()){
                return $this->fail($validator->errors()->first());
            }

            $user = User::where('email', $request->email)->first();

            $check = DB::table('password_reset_tokens')->where('email', $user->email)->first();

            if(!$check || $request->reset_token == null || $check->token != $request->reset_token){
                return $this->fail("you are not authorized to reset your password");
            }

            $user->update([
                "password" => bcrypt($request->new_password),
            ]);
    
            DB::table('password_reset_tokens')->where('email', $user->email)->delete();

            return $this->success("your password is changed successfully");

    }
}
