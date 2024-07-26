<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Favorite extends Model
{
    use HasFactory,SoftDeletes;

    protected $guarded = ['id'];

    public function list(){

        return $this->belongsTo(FavoriteList::class);
    }

    public function favorable(){

        return $this->morphTo(__FUNCTION__, 'favorable_type', 'favorable_id')->withTrashed();
    }

}
