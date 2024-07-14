<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Hotel extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'region_id', 'stars', 'description'];

    public function region(){

        return $this->belongsTo(Region::class, 'region_id')->withTrashed()->with('country:id,name,deleted_at');
    }

    public function previleges(){

        return $this->belongsToMany(Previlege::class)->withPivot('period')->as('info');
    }

   
    public function room_info()
    {
        return $this->hasOne(Room::class)->withTrashed();
    }

    public function favorite()
    {
        return $this->morphMany(Favorite::class,'favorable');
    }

    public function reviews()
    {
        return $this->morphMany(Review::class,'reviewable');
    }

    public function package_areas(){

        return $this->morphMany(PackageArea::class, 'visitable');
    }

    public function getImagesAttribute(){

        $country = Folder::where('folder_id', 4)->where('name', $this->region->country->name)->first();

        $city = Folder::where('folder_id', $country->id)->where('name', $this->region->name)->first();

      return  Folder::where('folder_id', $city->id)->where('name', $this->name)->first()->images;
    }
}
