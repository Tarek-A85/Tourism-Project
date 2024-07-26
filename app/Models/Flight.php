<?php

namespace App\Models;

use Faker\Provider\ar_EG\Company;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Flight extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function starting_airport()
    {
        return $this->belongsTo(Airport::class, 'start_airport_id');
    }

    public function ending_airport(){

        return $this->belongsTo(Region::class, 'end_airport_id');
    }

    public function flight_times()
    {
        return $this->hasMany(FlightTime::class, 'flight_id');
    }

}