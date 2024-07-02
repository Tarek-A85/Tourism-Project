<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class WalletController extends Controller
{
    public function create(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'password' => ['required', Password::min(8)]
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
                'password' => Hash::make( $request->password)
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
}
