<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Mail\BookingVerificationMail;
use App\Mail\CancelledBookingMail;
use App\Models\Package;
use App\Models\PackageTransaction;
use App\Models\Status;
use App\Models\Transaction;
use App\Models\TransactionType;
use App\Models\TripDetail;
use App\Notifications\BookingVerificationNotification;
use App\Notifications\CancelledReservationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class PackageTransactionController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['nullable', 'max:50', 'regex:/^[a-zA-Z ]+$/'],
            'email' => [Rule::requiredIf(fn() => $request->name != null), 'email'],
            'wallet_password' => ['required', Password::min(8)],
            'trip_detail_id' => [
                'required',
                Rule::exists('package_details', 'id')
            ],
            'children_number' => ['required', 'integer'],
            'adult_number' => ['required', 'integer']
        ]);
        if ($validator->fails()) {
            return $this->fail($validator->errors()->first());
        }

        $wallet = auth()->user()->wallet;
        if (!Hash::check($request->wallet_password, $wallet->password))
            return $this->fail('The password for the wallet is not correct');

        $trip = TripDetail::findOrfail($request->trip_detail_id);
        if ($trip->date->date < now()->toDateString())
            return $this->fail('you can not book a trip has already been started');
        if ($trip->available_tickets < $request->adult_number + $request->children_number)
            return $this->fail('The number of selected tickets is more than the number of available tickets');

        $package = Package::findOrfail($trip->package_id);

        $children_total_price = $package->child_price * $request->children_number;
        $adult_total_price = $package->adult_price * $request->adult_number;
        $total_price = $children_total_price + $adult_total_price;

        if ($wallet->balance < $total_price)
            return $this->fail('You dont have enough balance to make this transaction');

        //transaction
        //create transaction
        $transaction = Transaction::create([
            'name' => $request->name ?? null,
            'email' => $request->email ?? null,
            'wallet_id' => $wallet->id,
            'transaction_type_id' => TransactionType::where('name', 'Package')->first()->id,
            'status_id' => Status::where('name', 'Has not started yet')->first()->id,
            'price' => $total_price,
        ]);

        //create package transaction
        PackageTransaction::create([
            'transaction_id' => $transaction->id,
            'trip_detail_id' => $trip->id,
            'children_number' => $request->children_number,
            'children_total_price' => $children_total_price,
            'adult_number' => $request->adult_number,
            'adult_total_price' => $adult_total_price
        ]);

        $wallet->update([
            'balance' => $wallet->balance - $total_price
        ]);

        $trip->update([
            'available_tickets' => $trip->available_tickets - ($request->adult_number + $request->children_number)
        ]);

        $details[0] = 'Transaction number: ' . $transaction->id;
        $details[1] = 'trip number: ' .   $trip->id;
        $details[2] = 'adult number: ' . $request->adult_number;
        $details[3] = 'adult total price: ' . $adult_total_price;
        $details[4] = 'children number: ' .  $request->children_number;
        $details[5] = 'children total price: ' .  $children_total_price;
        $details[6] =  'total price: ' . $total_price;
        $details[7] =  'date and time of the trip: ' . $trip->date->date . '  ' . $trip->time;
        $details[8] =  'Expected ending date: ' . $trip->date->date + ($package->period)/24;


        if ($request->name) {
            $details[9] = 'renter name: ' . $request->name;

            $details[10] = 'renter email: ' . $request->email;
        }

        auth()->user()->notify(new BookingVerificationNotification($package->name . ' package', $details, 'Two days'));

        if ($request->name) {
            Mail::to($request->email)->send(new BookingVerificationMail($package->name . ' package', $details, 'Two days'));
        }

        //sent a mail to user 
        return $this->success('The booking is done successfully');
    }

    public function update(PackageTransaction $packageTransaction, Request $request)
    {
        $transaction = Transaction::where('id', $packageTransaction->transaction_id)->where('wallet_id', auth()->user()->wallet->id)->first();
        if (!$transaction)
            return $this->fail('You are not authorized');

        $validator = Validator::make($request->all(), [
            'name' => ['max:50', 'regex:/^[a-zA-Z ]+$/'],
            'email' => ['email'],
            'wallet_password' => ['required', Password::min(8)],
            'children_number' => ['required', 'integer'],
            'adult_number' => ['required', 'integer','gte:0']
        ]);
        if ($validator->fails()) {
            return $this->fail($validator->errors()->first());
        }

        if (
            $packageTransaction->children_number == $request->children_number
            && $packageTransaction->adult_number == $request->adult_number
            && $transaction->name == $request->name
            && $transaction->email == $request->email
        ) {
            return $this->fail('Nothing changed to modify');
        }


        $wallet = auth()->user()->wallet;
        if (!Hash::check($request->wallet_password, $wallet->password))
            return $this->fail('Wrong password');

        $trip = TripDetail::findOrfail($packageTransaction->trip_detail_id);
        if ($trip->date->date < now()->toDateString())
            return $this->fail('you can not update reservation on trip has already been started');
        
        $package = Package::where('id', $trip->package_id)->first();

        $childrenCancelNum = 0;
        $childrenAddNum = 0;
        $adultCancelNum = 0;
        $adultAddNum = 0;
        $refundedBalance = 0;
        $additionalCost = 0;

        if ($request->children_number < $packageTransaction->children_number || $request->adult_number < $packageTransaction->adult_number) {
            if ($trip->date->date <= now()->addDays(2)) {
                return $this->fail('Sorry, you cannot cancel tickets less than 2 days before the start of the trip.');
            }
            if ($request->children_number < $packageTransaction->children_number)
                $childrenCancelNum = $packageTransaction->children_number - $request->children_number;
            if ($request->adult_number < $packageTransaction->adult_number)
                $adultCancelNum = $packageTransaction->adult_number - $request->adult_number;

            $refundedBalance = $childrenCancelNum * $package->child_price + $adultCancelNum * $package->adult_price;
        }

        if ($request->children_number > $packageTransaction->children_number || $request->adult_number > $packageTransaction->adult_number) {
            if ($request->children_number > $packageTransaction->children_number)
                $childrenAddNum = $request->children_number - $packageTransaction->children_number;
            if ($request->adult_number > $packageTransaction->adult_number)
                $adultAddNum = $request->adult_number > $packageTransaction->adult_number;

            $additionalCost = $childrenAddNum * $package->child_price + $adultAddNum * $package->adult_price;
        }

        if ($trip->available_tickets < $childrenAddNum + $adultAddNum)
            return $this->fail('There are not enough tickets.');

        if ($additionalCost > $wallet->balance) {
            return $this->fail('You dont have enough balance to make this transaction');
        }

        $children_total_price = $package->child_price * $request->children_number;
        $adult_total_price = $package->adult_price * $request->adult_number;

        $wallet->update([
            'balance' => $wallet->balance - $additionalCost + $refundedBalance
        ]);

        $transaction->update([
            'name' => $request->name ?? null,
            'email' => $request->email ?? null,
            'price' => $transaction->price + $additionalCost - $refundedBalance,
        ]);

        $packageTransaction->update([
            'children_number' => $request->children_number,
            'children_total_price' => $children_total_price,
            'adult_number' => $request->adult_number,
            'adult_total_price' => $adult_total_price
        ]);

        $trip->update([
            'available_tickets' => $trip->available_tickets - ($childrenAddNum + $adultAddNum) + ($childrenCancelNum + $adultCancelNum)
        ]);

        $details[0] = 'Transaction number: ' . $transaction->id;
        $details[1] = 'trip number: ' .   $trip->id;
        $details[2] = 'adult number: ' . $request->adult_number;
        $details[3] = 'adult total price: ' . $adult_total_price;
        $details[4] = 'children number: ' .  $request->children_number;
        $details[5] = 'children total price: ' .  $children_total_price;
        $details[6] =  'total price: ' . ($adult_total_price + $children_total_price);
        $details[7] =  'date and time of the trip: ' . $trip->date->date . '  ' . $trip->time;

        if ($request->name) {
            $details[7] = 'renter name: ' . $request->name;

            $details[8] = 'renter email: ' . $request->email;
        }

        auth()->user()->notify(new BookingVerificationNotification($package->name . ' package', $details, 'Two days', 'modefied'));

        if ($request->name) {
            Mail::to($request->email)->send(new BookingVerificationMail($package->name . ' package', $details, 'Two days', 'modefied'));
        }

        return $this->success('The booking is modefied successfully');
    }

    public function destroy(PackageTransaction $packageTransaction, Request $request)
    {
        $transaction = Transaction::where('id', $packageTransaction->transaction_id)->where('wallet_id', auth()->user()->wallet->id)->first();
        if (!$transaction)
            return $this->fail('You are not authorized');

        $validator = Validator::make($request->all(), [
            'wallet_password' => ['required', Password::min(8)]
        ]);
        if ($validator->fails()) {
            return $this->fail($validator->errors()->first());
        }

        $wallet = auth()->user()->wallet;
        if (!Hash::check($request->wallet_password, $wallet->password))
            return $this->fail('Wrong password');

        $trip = TripDetail::findOrfail($packageTransaction->trip_detail_id);

        if($trip->date->date <= now()->toDateString()){
            return $this->fail('you can not cancel reservation on trip has already been started');
        }

        if ($trip->date->date <= now()->addDays(2)) {
            return $this->fail('Sorry, you cannot cancel reservation less than 2 days before the start of the trip.');
        }

        $wallet->update([
            'balance' => $wallet->balance + $transaction->price
        ]);

        $trip->update([
            'available_tickets' => $trip->available_tickets + $packageTransaction->children_number + $packageTransaction->adult_number
        ]);

        auth()->user()->notify(new CancelledReservationNotification($trip->package->name . ' package', $transaction->id));

        if($transaction->name){
         Mail::to($transaction->email)->send(new CancelledBookingMail($trip->package->name . ' package', $transaction->id));
        }

        $transaction->delete();

        return $this->success('You booking is cancelled successfully');

    }

    public function show(PackageTransaction $packageTransaction)
    {
        $transaction = $packageTransaction->transaction;
        
        $data=[];
        $data['package'] = $packageTransaction->tripDetail->package->only('id','name');
        $data['trip'] =$packageTransaction->tripDetail->only('id','time','date');
        $data['transaction_info'] = $packageTransaction->only('adult_number','adult_total_price','children_number','children_total_price');
        $data['transaction_info']['transaaction_id'] = $transaction->id;
        $data['transaction_info']['total_price'] = $transaction->price;

        if($transaction->name){
            $data['renter_name'] = $transaction->name;
            $data['renter_email'] = $transaction->email;
        }
        return $this->success('Package transaction details',['package_transaction' => $data]);
    }
}
