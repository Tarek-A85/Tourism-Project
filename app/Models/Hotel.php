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

    public function favorite()
    {
        return $this->morphMany(Favorite::class,'favorable');
    }

    public function photos()
    {
        return $this->morphMany(photo::class, 'photoable');
    }
    
    public function reviews()
    {
        return $this->morphMany(Review::class,'reviewable');
    }

    public function package_areas(){

        return $this->morphMany(PackageArea::class, 'visitable');
    }
}
