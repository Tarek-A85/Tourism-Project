<?php

namespace App\Http\Controllers\Both;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use Illuminate\Support\Collection;
class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $wallet = auth()->user()->wallet;

        if(!$wallet){
            return $this->fail('Please create a wallet and make transactions to see them');
        }

        $find = Transaction::with('type', 'status')->where('wallet_id', $wallet->id)->get();

        $transactions = new collection();

        foreach($find as $transaction){

            $collect = collect([
                'id' => $transaction->id,
                'price' => $transaction->price,
                'type' => $transaction->type->name . ' transaction',
                'status' => $transaction->status->name,
            ]);

            $transactions->push($collect);
        }

        return $this->success('All transactions', ['transactions' => $transactions]);
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
       $find = Transaction::with('type', 'status')->findOrFail($id);

       $wallet = $find->wallet;

       if(auth()->user()->id != $wallet->user->id){
        return $this->fail('You are not authorized');
       }

       $transaction['id'] = $find->id;

       $transaction['price'] = $find->price;

       $transaction['type'] = $find->type->name . ' transaction';

       $transaction['status'] = $find->status->name;

       return $this->success('transaction info',['transaction_info' => $transaction]);
    }
  
}
