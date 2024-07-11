<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Folder extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'folder_id'];

    protected $appends = ['images'];

    protected $hidden  = ['photos', 'folder'];

    public function folder(){
        return $this->belongsTo(Folder::class, 'folder_id');
    }

    public function photos(){
        return $this->hasMany(Photo::class);
    }

    public function getPathAttribute(){
       
    $path = $this->name;

    $current = $this->folder;

    while($current){

        $path = $current->name . '/' . $path;

        $current = $current->folder;
    }

    return ($path);
    }

    public function getImagesAttribute(){

        $photos = Array() ;
        
        $images = $this->photos;

        $path = $this->path;

        foreach($images as $image){

            array_push($photos, ["id" => $image->id, "path" => $path . '/' . $image->name]);
        }

        return $photos;
    }
}
