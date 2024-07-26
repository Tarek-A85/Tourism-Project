<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class FlightDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['flight_time_id', 'flight_type_id', 'available_tickets', 'adult_price', 'child_price'];

    public function flight_transactions(){
        return $this->hasMany(FlightTransaction::class, 'flight_detail_id');
    }

    public function flight_time(){
        return $this->belongsTo(FlightTime::class);
    }

    public function class(){
        return $this->belongsTo(FlightType::class, 'flight_type_id');
    }
}
