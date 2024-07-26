<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name','description', 'country_id'];

    protected $hidden = ['created_at', 'updated_at'];

    public function flights()
    {
        return $this->hasMany(Flight::class);
    }

    public function lists()
    {
        return $this->morphToMany(favoriteList::class,'favorable','favorite');
    }

    public function photos()
    {
        return $this->morphMany(photo::class, 'photoable');
    }

    public function favorite()
    {
        return $this->morphMany(Favorite::class,'favorable');
    }

    public function reviews()
    {
        return $this->morphMany(Review::class,'reviewable');
    }

    public function packages()
    {
        return $this->belongsToMany(Package::class);
    }

    public function country(){
        return $this->belongsTo(Region::class, 'country_id')->withTrashed();
    }

    public function getImagesAttribute(){

        $country = Folder::where('folder_id', 6)->where('name', $this->country->name)->first();


        $company = Folder::where('folder_id', $country->id)->where('name', $this->name)->first();

          return $company->images;
    }

    function forceDelete()
    {
        $this->favorite()->forceDelete();

        $this->delete();
    }
}