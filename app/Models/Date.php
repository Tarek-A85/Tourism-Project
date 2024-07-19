<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Date extends Model
{
    use HasFactory;

    protected $fillable = ['date'];

    public function hotel_transactions(){

        return $this->hasMany(HotelTransaction::class);
    }

    public function trip_details(){

        return $this->hasMany(TripDetail::class);
    }
}
