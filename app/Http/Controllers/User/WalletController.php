<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use Ichtrojan\Otp\Otp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Str;

class WalletController extends Controller
{
    public function create(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'password' => ['required', 'confirmed', Password::min(8)]
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first(),
                    'data' => null
                ], 401);
            }

            Wallet::create([
                'user_id' => auth()->id(),
                'balance' => 0,
                'password' => Hash::make($request->password)
            ]);

            return response()->json([
                'status' => true,
                'message' => 'your wallet created successfuly',
                'data' => null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "status" => false,
                "message" => "Something went wrong",
                "data" => null,
            ]);
        }
    }

    // public function send_code()
    // {
    //     try {
    //         auth()->user()->notify(new SendCodeNotification("Please use the code below to allow you to change wallet password"));

    //         return response()->json([
    //             "status" => true,
    //             "message" => "A code is sent to your email, use it to allow you to change the password",
    //             "data" => null,
    //         ]);
    //     } catch (\Exception $e) {

    //         return response()->json([
    //             "status" => false,
    //             "message" => "Something went wrong",
    //             "data" => null,
    //         ]);
    //     }
    // }

    // public function validate_code(Request $request)
    // {

    //     try {
    //         $validator = Validator::make($request->all(), [
    //             'code' => 'required',
    //         ]);

    //         if ($validator->fails()) {
    //             return response()->json([
    //                 "status" => false,
    //                 "message" => $validator->errors()->first(),
    //                 "data" => null,
    //             ]);
    //         }

    //         $otp = (new Otp)->validate(auth()->user()->email, $request->code);

    //         if (!$otp->status) {
    //             return response()->json([
    //                 "status" => false,
    //                 "message" => $otp->message,
    //                 "data" => null,
    //             ]);
    //         }

    //         $reset_token = Str::random(10);

    //         DB::table('password_reset_tokens')->insert([
    //             'email' => auth()->user()->email,
    //             'token' => $reset_token,
    //         ]);

    //         return response()->json([
    //             "status" => true,
    //             "message" => "you can reset your wallet password now",
    //             "data" => ["reset_token" => $reset_token],
    //         ]);
    //     } catch (\Exception $e) {

    //         return response()->json([
    //             "status" => false,
    //             "message" => "Something went wrong",
    //             "data" => null,
    //         ]);
    //     }
    // }

    public function restePassword(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'password' => ['required', 'confirmed', Password::min(8)]
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first(),
                    'data' => null
                ], 401);
            }

            $walletOwner = auth()->user();

            $check = DB::table('password_reset_tokens')
                ->where('email', $walletOwner->email)->first();

            if (!$check || $request->reset_token == null || $check->token != $request->reset_token) {
                return response()->json([
                    "status" => false,
                    "message" => "you are not authorized to reset your password",
                    "data" => null,
                ]);
            }

            DB::table('password_reset_tokens')
            ->where('email', $walletOwner->email)->delete();

            $walletOwner->wallet->update([
                'password' => Hash::make($request->password)
            ]);

            return response()->json([
                "status" => true,
                "message" => "your wallet password is changed successfully",
                "data" => null,

            ]);
        } catch (\Exception $e) {
            return response()->json([
                "status" => false,
                "message" => "Something went wrong",
                "data" => null,
            ]);
        }
    }
}
