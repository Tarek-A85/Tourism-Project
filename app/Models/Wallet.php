<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'balance', 'password'];

    public function user(){

        return $this->belongsTo(User::class);
    }

    public function transactions(){

        return $this->hasMany(Transaction::class);
    }

    public function package_transactions(){
        
        return $this->hasManyThrough(PackageTransaction::class, Transaction::class);
    }

    public function completed_package_transactions(){
        
        return $this->hasManyThrough(PackageTransaction::class, Transaction::class)->where('status_id', Status::where('name', 'Completed')->first()->id);
    }


}
