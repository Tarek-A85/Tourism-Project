<?php

namespace App\Traits;
use App\Models\Previlege;
use App\Models\Photo;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
 trait GeneralTrait
{
    public function fail($message = null){
        return response()->json([
            "status" => false,
            "message" => $message,
            "data" => null,
        ]);
    }

    public function success($message = null, $data = null){
        return response()->json([
            "status" => true,
            "message" => $message,
            "data" => $data,
        ]);
    }

    public function save_image($photo, $folder, $path, $folder_id ){

        $extension = $photo->getClientOriginalExtension();

        $add = Str::uuid()->toString();

        $name = $add . '.' . $extension;

        $photo->storeAs($folder, $path . '/' . $name);

        Photo::create([
            'name' => $name ,
            'folder_id' => $folder_id,
        ]);

    }

    public function delete_image($id){

        $photo = Photo::find($id);

        $path = $photo->folder->path . '/' . $photo->name;

        Storage::delete($path);
        
        $photo->delete();
    }

   
}
