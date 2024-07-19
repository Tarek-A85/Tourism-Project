<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Region extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'description', 'region_id'];
   

    public function cities(){

        return $this->hasMany(Region::class, 'region_id')->OrderBy('name');
    }

    public function country(){

        return $this->belongsTo(Region::class, 'region_id');
    }

    public function hotels(){

        return $this->hasMany(Hotel::class);
    }

    public function favorites()
    {
        return $this->morphMany(Favorite::class,'favorable');
    }

   

    public function reviews()
    {
        return $this->morphMany(favoriteList::class,'reviewable');
    }

    public function package_areas(){

        return $this->morphMany(PackageArea::class, 'visitable');

    }

    public function airports(){
        return $this->hasMany(Airport::class);
    }
}
