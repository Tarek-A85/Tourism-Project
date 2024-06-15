<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function lists()
    {
        return $this->morphToMany(favoriteList::class,'favorable','favorite');
    }

    public function reviews()
    {
        return $this->morphToMany(favoriteList::class,'reviewable','reviews');
    }

    public function trip_detail()
    {
        return $this->morphMany(TripDetail::class,'detailable','tripdetails');
    }
}
