<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = ['wallet_id', 'transaction_type_id', 'status_id', 'price', 'paid'];

    public function wallet(){

        return $this->belongsTo(Wallet::class);
    }

    public function status(){

        return $this->belongsTo(Status::class);
    }

    public function type(){

        return $this->belongsTo(TransactionType::class, 'transaction_type_id');
    }

    public function room_transactions(){

        return $this->hasMany(RoomTransaction::class);
    }

    public function package_transactions()
    {
        return $this->hasMany(PackageTransaction::class);
    }

    public function flight_transactions()
    {
        return $this->hasMany(FlightTransaction::class);
    }
}
