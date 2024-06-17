<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageArea extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function package(){

        return $this->belongsTo(Package::class);
    }

    public function visitable(){

        return $this->morphTo(__FUNCTION__, 'visitable_type', 'visitable_id');
    }
}
