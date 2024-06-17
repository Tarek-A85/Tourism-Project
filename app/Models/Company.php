<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

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
}
