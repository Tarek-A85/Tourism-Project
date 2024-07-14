<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Region;
use App\Models\Photo;
use App\Models\Folder;
use Illuminate\Support\Facades\Storage;
class RegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Region::create([
            'name' => 'Syria',
            'description' => 'My Country',
            'region_id' => null,
        ]);

       $parent = Folder::create([
            'name' => 'Syria',
            'folder_id' => 1,
        ]);

        $child = Folder::create([
            'name' => 'Syria',
            'folder_id' => $parent->id,
        ]);

        Storage::makeDirectory('Regions/Syria/Syria');

        Storage::copy('seeding_pictures/Syria_pic_2.png','Regions/Syria/Syria/Syria_pic_2.png');

        Photo::create([
            'name' => 'Syria_pic_2.png',
            'folder_id' => $child->id
        ]);

        Region::create([
            'name' => 'Damascus',
            'description' => 'The Capital',
            'region_id' => 1,
        ]);

        $parent_folder = Folder::create([
            'name' => 'Damascus',
            'folder_id' => $parent->id,
        ]);

        Storage::makeDirectory('Regions/Syria/Damascus');

        Storage::copy('seeding_pictures/Damascus_pic_1.png','Regions/Syria/Damascus/Damascus_pic_1.png');

        Photo::create([
            'name' => 'Damascus_pic_1.png',
            'folder_id' => $parent_folder->id
        ]);




    }
}
