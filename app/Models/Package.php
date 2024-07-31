<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Package extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];
    protected $hidden = ['package_areas'];
    protected $appends = ['image'];

    public function forceDelete()
    {
        $this->favorite()->forceDelete();

        DB::table($this->table)->where('id',$this->id)->delete();
    }

    public function getImageAttribute()
    {
        $folder = Folder::where('name', $this->name)->where('folder_id', 2)->first();
        if (!$folder)
            return null;

        $image = $folder->photos()->first();
        return "Packages/$this->name/" . $image->name;
    }

    public function getCountriesAttribute()
    {
        $countries = [];
        foreach ($this->package_areas as $area) {
            if ($area['visitable']['region_id'] == '') {
                $countries[] = [
                    'id' => $area['visitable']['id'],
                    'name' => $area['visitable']['name']
                ];
            }
        }
        return $countries;
    }

    public function favorite()
    {
        return $this->morphMany(Favorite::class, 'favorable');
    }

    public function reviews()
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    public function trip_detail()
    {
        return $this->hasMany(TripDetail::class);
    }

    public function package_areas()
    {
        return $this->hasMany(PackageArea::class);
    }

    public function types()
    {
        return $this->belongsToMany(TypeOfPackage::class, 'package_type', 'package_id', 'type_id');
    }

    public function companies()
    {
        return $this->belongsToMany(Company::class);
    }

    public function getImagesAttribute()
    {
        $folder = Folder::where('folder_id', 2)->where('name', $this->name)->first();
        return $folder ? $folder->images : null;
    }
}
