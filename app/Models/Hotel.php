<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hotel extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'region_id', 'stars'];

    public function region(){

        return $this->belongsTo(Region::class);
    }

    public function previleges(){

        return $this->belongsToMany(Previlege::class);
    }

    public function room()
    {
        return $this->hasOne(Room::class);
    }

    public function lists()
    {
        return $this->morphToMany(favoriteList::class,'favorable','favorite');
    }

    public function photos()
    {
        return $this->morphMany(photo::class, 'photoable');
    }
    
    public function reviews()
    {
        return $this->morphToMany(favoriteList::class,'reviewable','reviews');
    }
}
