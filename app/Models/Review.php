<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $appends = ['image'];

    protected $hidden = ['created_at', 'updated_at'];

    public function user(){

        return $this->belongsTo(User::class)->select('id', 'name');
    }

    public function reviewable(){

        return $this->morphTo(__FUNCTION__, 'reviewable_type', 'reviewable_id');
    }

    public function getImageAttribute(){

       $parent = Folder::where('name', 'Reviews')->first();

       $image = Folder::where('name', $this->id)->where('folder_id', $parent->id)->first();

       if($image)
       $image = $image->images;

       return $image;
    }

    
}
 