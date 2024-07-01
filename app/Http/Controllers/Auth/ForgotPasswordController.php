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

class ForgotPasswordController extends Controller
{
    public function send_forgotten_password_code(Request $request){

        try{
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if($validator->fails()){
            return response()->json([
                "status" => false,
                "message" => $validator->errors()->first(),
                "data" => null,
            ]);
        }

        $user = User::where('email', $request->email)->first();

        $user->notify(new SendCodeNotification('Please use the code below to allow you to change the password'));

        return response()->json([
            "status" => true,
            "message" => "A code is sent to your email, use it to allow you to change the password",
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

    public function validate_forgotten_password_code(Request $request){

        try{

            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'code' => 'required'
            ]);

            if($validator->fails()){
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first(),
                    'data' => null,
                ]);
            }

            $user = User::where('email', $request->email)->first();

            $otp = (new Otp)->validate($user->email, $request->code);

            if(!$otp->status){

                return response()->json([
                    "status" => false,
                    "message" => $otp->message,
                    "data" => null,
                ]);
            }

            $reset_token = Str::random(10);

            DB::table('password_reset_tokens')->insert([
                'email' => $user->email ,
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

    public function change_forgotting_password(Request $request){

        try{

            $validator = Validator::make($request->all(), [
                'email' => 'required|email', 
                'new_password' => 'required|confirmed',
            ]);

            if($validator->fails()){
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first(),
                    'data' => null,
                ]);
            }

            $user = User::where('email', $request->email)->first();

            $check = DB::table('password_reset_tokens')->where('email', $user->email)->first();

            if(!$check || $request->reset_token == null || $check->token != $request->reset_token){
                return response()->json([
                    "status" => false,
                    "message" => "you are not authorized to reset your password",
                    "data" => null,
                ]);
            }

            $user->update([
                "password" => bcrypt($request->new_password),
            ]);
    
            DB::table('password_reset_tokens')->where('email', $user->email)->delete();

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
