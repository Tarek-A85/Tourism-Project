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

            if(auth()->user()->wallet)
            {
                return response()->json([
                    'status' => false,
                    'message' => 'you already has wallet',
                    'data' => null
                ]);
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
                ],403);
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
        } 
        catch (\Exception $e) {
            return response()->json([
                "status" => false,
                "message" => "Something went wrong",
                "data" => null,
            ]);
        }
    }
}
