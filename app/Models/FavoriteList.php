<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FavoriteList extends Model
{
    use HasFactory;

    protected $table = 'lists';

    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    //polymorphic many to many favorite
    //-----------\\
    public function comapnies()
    {
        return $this->morphedByMany(company::class,'favorable','favorites');   
    }

    public function regions()
    {
        return $this->morphedByMany(region::class,'favorable','favorites');   
    }

    public function hotles()
    {
        return $this->morphedByMany(Hotel::class,'favorable','favorites');   
    }

    public function packages()
    {
        return $this->morphedByMany(package::class,'favorable','favorites');   
    }
    //-----------\\
}
