<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function user(){

        return $this->belongsTo(User::class);
    }

    public function reviewable(){

        return $this->morphTo(__FUNCTION__, 'reviewable_type', 'reviewable_id');
    }
}
 