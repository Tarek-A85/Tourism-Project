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
    public function show()
    {
        $wallet = auth()->user()->wallet;
        $wallet->setHidden(['password','user_id']);
        return $this->success('your wallet information',$wallet);
    }

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

    public function add_balance_to_wallet(Request $request){

        $validator = Validator::make($request->all(), [
            'wallet_id' => ['required', 'exists:wallets,id'],
            'balance' => ['required', 'numeric'],
        ]);

        if($validator->fails()){
            return $this->fail($validator->errors()->first());
        }

        $wallet = Wallet::findOrFail($request->wallet_id);

        if($wallet->balance + $request->balance > 999999999999){
            return $this->fail('wallet balance will be more than allowed, you cant do that');
        }

        $wallet->update([
            'balance' => $wallet->balance + $request->balance,
        ]);

        return $this->success('The balance is added to the wallet successfully');


    }
}
