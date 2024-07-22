<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name','description'];
    protected $hidden = ['pivot'];

    public function flights()
    {
        return $this->hasMany(flight::class);
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
        return $this->morphMany(Review::class,'reviewable');
    }

    public function packages()
    {
        return $this->belongsToMany(Package::class);
    }

    public function getImagesAttribute(){

        $country = Folder::where('folder_id', 6)->where('name', $this->country->name)->first();


        $company = Folder::where('folder_id', $country->id)->where('name', $this->name)->first();

          return $company->images;
    }
}