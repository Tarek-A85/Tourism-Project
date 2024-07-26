<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlightTransaction extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function type()
    {
        return $this->belongsTo(flightType::class,'flight_type_id');
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function tripDetail()
    {
        return $this->belongsTo(TripDetail::class);
    }

    public function flight_details(){
        return $this->belongsTo(FlightDetail::class, 'flight_detail_id');
    }

}
