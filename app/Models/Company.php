<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name','description'];

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
}