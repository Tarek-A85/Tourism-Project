<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fillable = ['hotel_id', 'total_number', 'available_number', 'price'];

    public function hotel(){

        return $this->belongsTo(Hotel::class);
    }

    public function room_transactions(){

        return $this->hasMany(RoomTransaction::class);
    }
}
