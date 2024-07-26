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

    public function country_with_trashed(){

        return $this->belongsTo(Region::class, 'region_id')->As('country');
    }

    public function hotels(){

        return $this->hasMany(Hotel::class);
    }

    public function favorite()
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

    public function companies(){
        return $this->hasMany(Company::class);
    }

    public function getImagesAttribute(){

        if($this->region_id == null){
        $country = Folder::where('folder_id', 1)->where('name', $this->name)->first();
        }
        else{
            $country = Folder::where('folder_id', 1)->where('name', $this->country->name)->first();
        }
       
        $city = Folder::where('folder_id', $country->id)->where('name', $this->name)->first();

      return  $city->images;
    }

    function forceDelete()
    {
        $this->favorite()->forceDelete();

        $this->delete();
    }

    
}
