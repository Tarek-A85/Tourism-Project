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

    public function room(){

        return $this->hasOne(Room::class);
    }
}
