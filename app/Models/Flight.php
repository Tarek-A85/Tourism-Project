<?php

namespace App\Models;

use Faker\Provider\ar_EG\Company;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Flight extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function regions()
    {
        return $this->belongsTo(Region::class);
    }

    public function trip_detail()
    {
        return $this->morphMany(TripDetail::class,'detailable','tripdetails');
    }

}