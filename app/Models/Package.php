<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Package extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected $appends = ['period'];

    public function getPeriodAttribute()
    {
        $period = 0;
        foreach ($this->package_areas as $area) {
            if ($area['visitable']['region_id'] == '' && $area['visitable_type']=='Region') {
                $period += $area['period'];
            }
        }
        return $period;
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
        return $this->morphMany(TripDetail::class, 'detailable');
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
}
