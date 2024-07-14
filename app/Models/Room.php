<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Room extends Model
{
    use HasFactory, SoftDeletes ;

    protected $fillable = ['hotel_id', 'total_number', 'available_number', 'price'];

   protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    public function hotel(){

        return $this->belongsTo(Hotel::class);
    }

    public function room_transactions(){

        return $this->hasMany(RoomTransaction::class);
    }
}
