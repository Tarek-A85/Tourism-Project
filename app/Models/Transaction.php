<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = ['wallet_id', 'transaction_type_id', 'status_id', 'price', 'name', 'email'];

    public function delete()
    {

        if ($this->type->name == 'Package') {
            $wallet = $this->wallet;
            $wallet->update([
                'balance' => $wallet->balance + $this->price
            ]);
        }

        DB::table($this->table)->where('id', $this->id)->delete();
    }

    public function wallet()
    {

        return $this->belongsTo(Wallet::class);
    }

    public function status()
    {

        return $this->belongsTo(Status::class);
    }

    public function type()
    {

        return $this->belongsTo(TransactionType::class, 'transaction_type_id');
    }

    public function hotel_transactions()
    {

        return $this->hasOne(HotelTransaction::class);
    }

    public function package_transactions()
    {
        return $this->hasOne(PackageTransaction::class);
    }

    public function flight_transactions()
    {
        return $this->hasMany(FlightTransaction::class);
    }
}
