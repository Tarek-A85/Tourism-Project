<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TripDetail extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $table = 'package_details';
    protected $with = ['date'];
    protected $hidden = ['delay','current_area'];

    public function delete(){
        $package_transactions = $this->packageTransaction;
        foreach($package_transactions as $package_transaction){
            $package_transaction->transaction->delete();
        }
        DB::table($this->table)->where('id', $this->id)->delete();
    }

    public function date()
    {
        return $this->belongsTo(Date::class);
    }

    public function packageTransaction()
    {
        return $this->hasMany(PackageTransaction::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function areas()
    {
        return  $this->package->package_areas->where('visitable_type','Region')->where('visitable.region_id','!=','')->select('name','period');
    }
}
