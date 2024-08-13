<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotelTransaction extends Model
{
    use HasFactory;

    protected $fillable = ['transaction_id', 'hotel_id', 'number_of_rooms', 'staying_date_id', 'departure_date_id'];

    public function hotel(){
        
        return $this->belongsTo(Hotel::class);
    }

    public function transaction(){
        
        return $this->belongsTo(Transaction::class);
    }

    public function staying_date(){

        return $this->belongsTo(Date::class, 'staying_date_id')->select('id', 'date');
    }

    public function departure_date(){

        return $this->belongsTo(Date::class, 'departure_date_id')->select('id', 'date');
    }
}
