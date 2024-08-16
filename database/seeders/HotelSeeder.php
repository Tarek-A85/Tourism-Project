<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Hotel;
use App\Models\Previlege;
use App\Models\Photo;
use App\Models\Folder;
use Illuminate\Support\Facades\Storage;
class HotelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       $hotel = Hotel::create([
            'name' => 'Sheraton Hotel',
            'description' => 'Amazing Hotel Which has a lot of interesting things',
            'region_id' => 2,
            'stars' => 5.00,
            'price_per_room' => 5000,
        ]);

       $previlege = Previlege::create([
            'name' => 'Free Wifi',
        ]);

        $hotel->previleges()->attach($previlege->id,['period' => null]);

       $country = Folder::create([
            'name' => 'Syria',
            'folder_id' => 4
        ]);

        $city = Folder::create([
            'name' => 'Damascus',
            'folder_id' => $country->id,
        ]);

        $hotel_folder = Folder::create([
            'name' => 'Sheraton Hotel',
            'folder_id' => $city->id,
        ]);

        Storage::makeDirectory('Hotels/Syria/Damascus/Sheraton Hotel');

        Storage::copy('seeding_pictures/Sheraton_pic_1.png', 'Hotels/Syria/Damascus/Sheraton Hotel/Sheraton_pic_1.png');

        Photo::create([
            'name' => 'Sheraton_pic_1.png',
            'folder_id' => $hotel_folder->id,
        ]);


        
    }
}
