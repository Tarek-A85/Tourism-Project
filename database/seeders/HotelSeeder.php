<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Hotel;
use App\Models\Previlege;
use App\Models\Photo;
use App\Models\Region;
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
        Storage::copy('seeding_pictures/Sheraton_pic_2.png', 'Hotels/Syria/Damascus/Sheraton Hotel/Sheraton_pic_2.png');
        Storage::copy('seeding_pictures/Sheraton_pic_3.png', 'Hotels/Syria/Damascus/Sheraton Hotel/Sheraton_pic_3.png');

        Photo::create([
            'name' => 'Sheraton_pic_1.png',
            'folder_id' => $hotel_folder->id,
        ]);
        Photo::create([
            'name' => 'Sheraton_pic_2.png',
            'folder_id' => $hotel_folder->id,
        ]);
        Photo::create([
            'name' => 'Sheraton_pic_3.png',
            'folder_id' => $hotel_folder->id,
        ]);
        ////////////////////////////

        $hotel = Hotel::create([
            'name' => 'Hotel 1898',
            'description' => 'Amazing Hotel Which has a lot of interesting things',
            'region_id' => Region::where('name', 'Barcelona')->first()->id,
            'stars' => 5.00,
            'price_per_room' => 6000,
        ]);

        $previlege_2 = Previlege::create([
            'name' => 'Free Breakfast',
        ]);

        $hotel->previleges()->attach($previlege_2->id,['period' => 3]);

        $second_country = Folder::create([
            'name' => 'Spain',
            'folder_id' => 4
        ]);

        $second_city = Folder::create([
            'name' => 'Barcelona',
            'folder_id' => $second_country->id,
        ]);

        $second_hotel_folder = Folder::create([
            'name' => 'Hotel 1898',
            'folder_id' => $second_city->id,
        ]);

        Storage::makeDirectory('Hotels/Spain/Barcelona/Hotel 1898');

        
         Storage::copy('seeding_pictures/Hotel 1898_pic_1.png', 'Hotels/Spain/Barcelona/Hotel 1898/Hotel 1898_pic_1.png');
         Storage::copy('seeding_pictures/Hotel 1898_pic_2.png', 'Hotels/Spain/Barcelona/Hotel 1898/Hotel 1898_pic_2.png');
         Storage::copy('seeding_pictures/Hotel 1898_pic_3.png', 'Hotels/Spain/Barcelona/Hotel 1898/Hotel 1898_pic_3.png');

         Photo::create([
            'name' => 'Hotel 1898_pic_1.png',
            'folder_id' => $second_hotel_folder->id,
        ]);
         Photo::create([
            'name' => 'Hotel 1898_pic_2.png',
            'folder_id' => $second_hotel_folder->id,
        ]);
         Photo::create([
            'name' => 'Hotel 1898_pic_3.png',
            'folder_id' => $second_hotel_folder->id,
        ]);

        ////////////////////////////////////////////////////////////////

        $hotel = Hotel::create([
            'name' => 'Albergo del senato',
            'description' => 'Amazing Hotel Which has a lot of interesting things',
            'region_id' => Region::where('name', 'Rome')->first()->id,
            'stars' => 4.00,
            'price_per_room' => 3000,
        ]);

        $third_country = Folder::create([
            'name' => 'Italy',
            'folder_id' => 4
        ]);

        $third_city = Folder::create([
            'name' => 'Rome',
            'folder_id' => $third_country->id,
        ]);

        $third_hotel_folder = Folder::create([
            'name' => 'Albergo del senato',
            'folder_id' => $third_city->id,
        ]);

        Storage::makeDirectory('Hotels/Italy/Rome/Albergo del senato');

        Storage::copy('seeding_pictures/Albergo del senato_pic_1.png', 'Hotels/Italy/Rome/Albergo del senato/Albergo del senato_pic_1.png');
        Storage::copy('seeding_pictures/Albergo del senato_pic_2.png', 'Hotels/Italy/Rome/Albergo del senato/Albergo del senato_pic_2.png');
        Storage::copy('seeding_pictures/Albergo del senato_pic_3.png', 'Hotels/Italy/Rome/Albergo del senato/Albergo del senato_pic_3.png');

        Photo::create([
            'name' => 'Albergo del senato_pic_1.png',
            'folder_id' => $third_hotel_folder->id,
        ]);
        Photo::create([
            'name' => 'Albergo del senato_pic_2.png',
            'folder_id' => $third_hotel_folder->id,
        ]);
        Photo::create([
            'name' => 'Albergo del senato_pic_3.png',
            'folder_id' => $third_hotel_folder->id,
        ]);

        /////////////////////////////////////////////

        $hotel = Hotel::create([
            'name' => 'Desert gardens',
            'description' => 'Amazing Hotel Which has a lot of interesting things',
            'region_id' => Region::where('name', 'Sydney')->first()->id,
            'stars' => 3.00,
            'price_per_room' => 2000,
        ]);

        $hotel->previleges()->attach($previlege->id,['period' => null]);

        $fourth_country = Folder::create([
            'name' => 'Australia',
            'folder_id' => 4
        ]);

        $fourth_city = Folder::create([
            'name' => 'Sydney',
            'folder_id' => $fourth_country->id,
        ]);

        $fourth_hotel_folder = Folder::create([
            'name' => 'Desert gardens',
            'folder_id' => $fourth_city->id,
        ]);

        Storage::makeDirectory('Hotels/Australia/Sydney/Desert gardens');

        Storage::copy('seeding_pictures/Desert gardens_pic_1.png', 'Hotels/Australia/Sydney/Desert gardens/Desert gardens_pic_1.png');
        Storage::copy('seeding_pictures/Desert gardens_pic_2.png', 'Hotels/Australia/Sydney/Desert gardens/Desert gardens_pic_2.png');
        Storage::copy('seeding_pictures/Desert gardens_pic_3.png', 'Hotels/Australia/Sydney/Desert gardens/Desert gardens_pic_3.png');

        Photo::create([
            'name' => 'Desert gardens_pic_1.png',
            'folder_id' => $fourth_hotel_folder->id,
        ]);
        Photo::create([
            'name' => 'Desert gardens_pic_2.png',
            'folder_id' => $fourth_hotel_folder->id,
        ]);
        Photo::create([
            'name' => 'Desert gardens_pic_3.png',
            'folder_id' => $fourth_hotel_folder->id,
        ]);


  

        
    }
}
