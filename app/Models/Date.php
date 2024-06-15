<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Date extends Model
{
    use HasFactory;

    protected $fillable = ['date'];

    public function room_transactions(){

        return $this->hasMany(RoomTransaction::class);
    }

    public function trip_details(){

        return $this->hasMany(TripDetail::class);
    }
}
