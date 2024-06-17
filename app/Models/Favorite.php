<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function list(){

        return $this->belongsTo(FavoriteList::class);
    }

    public function favorable(){

        return $this->morphTo(__FUNCTION__, 'favorable_type', 'favorable_id');
    }

}
