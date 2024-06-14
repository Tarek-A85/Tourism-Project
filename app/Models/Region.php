<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'region_id'];

    public function children(){

        return $this->hasMany(Region::class, 'region_id');
    }

    public function parent(){

        return $this->belongsTo(Region::class, 'region_id');
    }

    public function hotels(){

        return $this->hasMany(Hotel::class);
    }


}
