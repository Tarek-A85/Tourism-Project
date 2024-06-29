<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Package extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    public function favorite()
    {
        return $this->morphMany(Favorite::class,'favorable');
    }

    public function reviews()
    {
        return $this->morphMany(Review::class,'reviewable');
    }

    public function trip_detail()
    {
        return $this->morphMany(TripDetail::class,'detailable','tripdetails');
    }

    public function package_areas(){

        return $this->hasMany(PackageArea::class);
    }
}
