<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class FlightTime extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['flight_id', 'date_id', 'time'];

   public function flight_details(){
    return $this->hasMany(FlightDetail::class, 'flight_time_id');
   }

    public function flight(){
        return $this->belongsTo(Flight::class);
    }

    public function date(){
        return $this->belongsTo(Date::class);
    }
}
