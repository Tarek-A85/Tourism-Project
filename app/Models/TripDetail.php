<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TripDetail extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    
    public function date()
    {
        return $this->belongsTo(Date::class);
    }

    public function flightTransaction()
    {
        return $this->hasMany(FlightTransaction::class);
    }

    public function packageTransaction()
    {
        return $this->hasMany(PackageTransaction::class);
    }

    public function detailable()
    {
        return $this->morphto();
    }
}
