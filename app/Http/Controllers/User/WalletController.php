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
        
        $validator = Validator::make($request->all(), [
            'password' => ['required', 'confirmed', Password::min(8)]
        ]);

        if ($validator->fails()) {
            return $this->fail($validator->errors()->first());
        }

        if(auth()->user()->wallet)
        {
            return $this->fail('you already has wallet');
        }

        Wallet::create([
            'user_id' => auth()->id(),
            'balance' => 0,
            'password' => Hash::make($request->password)
        ]);

        return $this->success('your wallet created successfuly');
    }

    public function restePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => ['required', 'confirmed', Password::min(8)]
        ]);

        if ($validator->fails()) {
            return $this->fail($validator->errors()->first());
        }

        $walletOwner = auth()->user();

        $check = DB::table('password_reset_tokens')
            ->where('email', $walletOwner->email)->first();

        if (!$check || $request->reset_token == null || $check->token != $request->reset_token) {
            return $this->fail("you are not authorized to reset your password");
        }

        DB::table('password_reset_tokens')
        ->where('email', $walletOwner->email)->delete();

        $walletOwner->wallet->update([
            'password' => Hash::make($request->password)
        ]);

        return $this->success("your wallet password is changed successfully");

    }
}
