<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomTransaction extends Model
{
    use HasFactory;

    protected $fillable = ['transaction_id', 'room_id', 'number_of_rooms', 'staying_date_id', 'departure_date_id'];

    public function room(){
        
        return $this->belongsTo(Room::class);
    }

    public function transaction(){
        
        return $this->belongsTo(Transaction::class);
    }

    public function date(){

        return $this->belongsTo(Date::class);
    }
}
