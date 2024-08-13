<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Hotel extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'region_id', 'stars', 'description', 'price_per_room'];

    protected $hidden = ['created_at', 'updated_at'];

    public function region(){

        return $this->belongsTo(Region::class, 'region_id')->withTrashed()->with(['country' => function($query){

            $query->withTrashed()->select('id','name','deleted_at');
            
        }]);
    }

    public function previleges(){

        return $this->belongsToMany(Previlege::class)->withPivot('period')->as('info');
    }

    public function favorite()
    {
        return $this->morphMany(Favorite::class,'favorable');
    }

    public function reviews()
    {
        return $this->morphMany(Review::class,'reviewable');
    }

    public function package_areas(){

        return $this->morphMany(PackageArea::class, 'visitable');
    }

    public function getImagesAttribute(){

        $country = Folder::where('folder_id', 4)->where('name', $this->region->country->name)->first();

        $city = Folder::where('folder_id', $country->id)->where('name', $this->region->name)->first();

      return  Folder::where('folder_id', $city->id)->where('name', $this->name)->first()->images;
    }

    public function hotel_transactions(){
        
        return $this->hasMany(HotelTransaction::class);
    }

    public function forceDelete()
    {
        $this->favorite()->forceDelete();

        DB::table($this->table)->where('id',$this->id)->delete();
    }
}
