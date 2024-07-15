<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeOfPackage extends Model
{
    use HasFactory;

    protected $fillable = ['name'];
    protected $hidden = ['pivot'];

    public function packages()
    {
        return $this->belongsToMany(Package::class, 'package_type', 'type_id', 'package_id');
    }
}
