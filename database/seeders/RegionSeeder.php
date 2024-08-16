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
            'description' => 'The best country and it has a lot of amazing places to visit and a lot of amazing experiences to live ',
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

        Storage::copy('seeding_pictures/Syria_pic_1.png','Regions/Syria/Syria/Syria_pic_1.png');
        Storage::copy('seeding_pictures/Syria_pic_2.png','Regions/Syria/Syria/Syria_pic_2.png');
        Storage::copy('seeding_pictures/Syria_pic_3.png','Regions/Syria/Syria/Syria_pic_3.png');

        Photo::create([
            'name' => 'Syria_pic_1.png',
            'folder_id' => $child->id
        ]);
        Photo::create([
            'name' => 'Syria_pic_2.png',
            'folder_id' => $child->id
        ]);
        Photo::create([
            'name' => 'Syria_pic_3.png',
            'folder_id' => $child->id
        ]);

/////////////////////////////////////////////////////////////////////////////////
        //Damascus city
        Region::create([
            'name' => 'Damascus',
            'description' => 'The Capital city of syria and the city of jasmin, it mixes the beauty of the past with the modernity',
            'region_id' => 1,
        ]);

        $parent_folder = Folder::create([
            'name' => 'Damascus',
            'folder_id' => $parent->id,
        ]);

        Storage::makeDirectory('Regions/Syria/Damascus');

        Storage::copy('seeding_pictures/Damascus_pic_1.png','Regions/Syria/Damascus/Damascus_pic_1.png');
        Storage::copy('seeding_pictures/Damascus_pic_2.png','Regions/Syria/Damascus/Damascus_pic_2.png');
        Storage::copy('seeding_pictures/Damascus_pic_3.png','Regions/Syria/Damascus/Damascus_pic_3.png');

        Photo::create([
            'name' => 'Damascus_pic_1.png',
            'folder_id' => $parent_folder->id
        ]);
        Photo::create([
            'name' => 'Damascus_pic_2.png',
            'folder_id' => $parent_folder->id
        ]);
        Photo::create([
            'name' => 'Damascus_pic_3.png',
            'folder_id' => $parent_folder->id
        ]);
        //////////////////////////////////////////////////////////////////////////////////////////

        //Homs city

        Region::create([
            'name' => 'Homs',
            'description' => 'The largest city in syria, its the city of halawet al jobn which is homsieh no matter what they say, and they love mateh from the bottom of their hearts, special tribute to AL HAFAR village there which is the best village in the world',
            'region_id' => Region::where('name', 'Syria')->where('region_id', null)->first()->id,
        ]);

        $parent_folder_for_homs = Folder::create([
            'name' => 'Homs',
            'folder_id' => $parent->id,
        ]);

        Storage::makeDirectory('Regions/Syria/Homs');

        Storage::copy('seeding_pictures/Homs_pic_1.png','Regions/Syria/Homs/Homs_pic_1.png');
        Storage::copy('seeding_pictures/Homs_pic_2.png','Regions/Syria/Homs/Homs_pic_2.png');
        Storage::copy('seeding_pictures/Homs_pic_3.png','Regions/Syria/Homs/Homs_pic_3.png');

        Photo::create([
            'name' => 'Homs_pic_1.png',
            'folder_id' => $parent_folder_for_homs->id
        ]);

        Photo::create([
            'name' => 'Homs_pic_2.png',
            'folder_id' => $parent_folder_for_homs->id
        ]);

        Photo::create([
            'name' => 'Homs_pic_3.png',
            'folder_id' => $parent_folder_for_homs->id
        ]);

/////////////////////////////////////////////////////////////////////////////////////////////////
    //Spain

    Region::create([
        'name' => 'Spain',
        'description' => 'A very beautiful country with a lot of interesting things to do, ofcourse the best city there is barcelona and the best team in the world is also barcelona',
        'region_id' => null,
    ]);

    $parent_for_spain = Folder::create([
        'name' => 'Spain',
        'folder_id' => 1,
    ]);

    $child_for_spain = Folder::create([
        'name' => 'Spain',
        'folder_id' => $parent_for_spain->id,
    ]);

    Storage::makeDirectory('Regions/Spain/Spain');

    Storage::copy('seeding_pictures/Spain_pic_1.png','Regions/Spain/Spain/Spain_pic_1.png');
    Storage::copy('seeding_pictures/Spain_pic_2.png','Regions/Spain/Spain/Spain_pic_2.png');
    Storage::copy('seeding_pictures/Spain_pic_3.png','Regions/Spain/Spain/Spain_pic_3.png');

    Photo::create([
        'name' => 'Spain_pic_1.png',
        'folder_id' => $child_for_spain->id
    ]);
    Photo::create([
        'name' => 'Spain_pic_2.png',
        'folder_id' => $child_for_spain->id
    ]);
    Photo::create([
        'name' => 'Spain_pic_3.png',
        'folder_id' => $child_for_spain->id
    ]);
//////////////////////////////////////////////////////////////

//barcelona city

Region::create([
    'name' => 'Barcelona',
    'description' => 'The Best city in the world and it has the best team in the world',
    'region_id' => Region::where('name', 'Spain')->where('region_id', null)->first()->id,
]);

$parent_folder_for_barca = Folder::create([
    'name' => 'Barcelona',
    'folder_id' => $parent_for_spain->id,
]);

Storage::makeDirectory('Regions/Spain/Barcelona');

Storage::copy('seeding_pictures/Barcelona_pic_1.png','Regions/Spain/Barcelona/Barcelona_pic_1.png');
Storage::copy('seeding_pictures/Barcelona_pic_2.png','Regions/Spain/Barcelona/Barcelona_pic_2.png');
Storage::copy('seeding_pictures/Barcelona_pic_3.png','Regions/Spain/Barcelona/Barcelona_pic_3.png');

Photo::create([
    'name' => 'Barcelona_pic_1.png',
    'folder_id' => $parent_folder_for_barca->id
]);
Photo::create([
    'name' => 'Barcelona_pic_2.png',
    'folder_id' => $parent_folder_for_barca->id
]);
Photo::create([
    'name' => 'Barcelona_pic_3.png',
    'folder_id' => $parent_folder_for_barca->id
]);
//////////////////////////////////////////////////////////////
 //Seville

 Region::create([
    'name' => 'Seville',
    'description' => 'We do not want to put madrid so we put seville, and it is a very beautiful city by the wat',
    'region_id' => Region::where('name', 'Spain')->where('region_id', null)->first()->id,
]);

$parent_folder_for_seville = Folder::create([
    'name' => 'Seville',
    'folder_id' => $parent_for_spain->id,
]);

Storage::makeDirectory('Regions/Spain/Seville');

Storage::copy('seeding_pictures/Seville_pic_1.png','Regions/Spain/Seville/Seville_pic_1.png');
Storage::copy('seeding_pictures/Seville_pic_2.png','Regions/Spain/Seville/Seville_pic_2.png');
Storage::copy('seeding_pictures/Seville_pic_3.png','Regions/Spain/Seville/Seville_pic_3.png');

Photo::create([
    'name' => 'Seville_pic_1.png',
    'folder_id' => $parent_folder_for_seville->id
]);
Photo::create([
    'name' => 'Seville_pic_2.png',
    'folder_id' => $parent_folder_for_seville->id
]);
Photo::create([
    'name' => 'Seville_pic_3.png',
    'folder_id' => $parent_folder_for_seville->id
]);

//////////////////////////////////////////////////////////////////

//italy

Region::create([
    'name' => 'Italy',
    'description' => 'An amazing country which has a lot of amazing places both old and modern, you have to visit italy once in your life',
    'region_id' => null,
]);

$parent_for_italy = Folder::create([
    'name' => 'Italy',
    'folder_id' => 1,
]);

$child_for_italy = Folder::create([
    'name' => 'Italy',
    'folder_id' => $parent_for_italy->id,
]);

Storage::makeDirectory('Regions/Italy/Italy');

Storage::copy('seeding_pictures/Italy_pic_1.png','Regions/Italy/Italy/Italy_pic_1.png');
Storage::copy('seeding_pictures/Italy_pic_2.png','Regions/Italy/Italy/Italy_pic_2.png');
Storage::copy('seeding_pictures/Italy_pic_3.png','Regions/Italy/Italy/Italy_pic_3.png');

Photo::create([
    'name' => 'Italy_pic_1.png',
    'folder_id' => $child_for_italy->id
]);
Photo::create([
    'name' => 'Italy_pic_2.png',
    'folder_id' => $child_for_italy->id
]);
Photo::create([
    'name' => 'Italy_pic_3.png',
    'folder_id' => $child_for_italy->id
]);
/////////////////////////////////////////////////
 //Rome

 Region::create([
    'name' => 'Rome',
    'description' => 'The capital of italy, it is very beautiful city which has a lot of things',
    'region_id' => Region::where('name', 'Italy')->where('region_id', null)->first()->id,
]);

$parent_folder_for_rome = Folder::create([
    'name' => 'Rome',
    'folder_id' => $parent_for_italy->id,
]);

Storage::makeDirectory('Regions/Italy/Rome');

Storage::copy('seeding_pictures/Rome_pic_1.png','Regions/Italy/Rome/Rome_pic_1.png');
Storage::copy('seeding_pictures/Rome_pic_2.png','Regions/Italy/Rome/Rome_pic_2.png');
Storage::copy('seeding_pictures/Rome_pic_3.png','Regions/Italy/Rome/Rome_pic_3.png');

Photo::create([
    'name' => 'Rome_pic_1.png',
    'folder_id' => $parent_folder_for_rome->id,
]);
Photo::create([
    'name' => 'Rome_pic_2.png',
    'folder_id' => $parent_folder_for_rome->id,
]);
Photo::create([
    'name' => 'Rome_pic_3.png',
    'folder_id' => $parent_folder_for_rome->id,
]);
//////////////////////////////////////////////////////////

//Venice

Region::create([
    'name' => 'Venice',
    'description' => ' it is very beautiful city which has a lot of things',
    'region_id' => Region::where('name', 'Italy')->where('region_id', null)->first()->id,
]);

$parent_folder_for_venice = Folder::create([
    'name' => 'Venice',
    'folder_id' => $parent_for_italy->id,
]);

Storage::makeDirectory('Regions/Italy/Venice');

Storage::copy('seeding_pictures/Venice_pic_1.png','Regions/Italy/Venice/Venice_pic_1.png');
Storage::copy('seeding_pictures/Venice_pic_2.png','Regions/Italy/Venice/Venice_pic_2.png');
Storage::copy('seeding_pictures/Venice_pic_3.png','Regions/Italy/Venice/Venice_pic_3.png');

Photo::create([
    'name' => 'Venice_pic_1.png',
    'folder_id' => $parent_folder_for_venice->id,
]);
Photo::create([
    'name' => 'Venice_pic_2.png',
    'folder_id' => $parent_folder_for_venice->id,
]);
Photo::create([
    'name' => 'Venice_pic_3.png',
    'folder_id' => $parent_folder_for_venice->id,
]);

///////////////////////////////////////////////////////////////////

//Australia

Region::create([
    'name' => 'Australia',
    'description' => 'An amazing country which has a lot of amazing places both old and modern, you have to visit australia once in your life',
    'region_id' => null,
]);

$parent_for_australia = Folder::create([
    'name' => 'Australia',
    'folder_id' => 1,
]);

$child_for_australia = Folder::create([
    'name' => 'Australia',
    'folder_id' => $parent_for_australia->id,
]);

Storage::makeDirectory('Regions/Australia/Australia');

Storage::copy('seeding_pictures/Australia_pic_1.png','Regions/Australia/Australia/Australia_pic_1.png');
Storage::copy('seeding_pictures/Australia_pic_2.png','Regions/Australia/Australia/Australia_pic_2.png');
Storage::copy('seeding_pictures/Australia_pic_3.png','Regions/Australia/Australia/Australia_pic_3.png');

Photo::create([
    'name' => 'Australia_pic_1.png',
    'folder_id' => $child_for_australia->id,
]);
Photo::create([
    'name' => 'Australia_pic_2.png',
    'folder_id' => $child_for_australia->id,
]);
Photo::create([
    'name' => 'Australia_pic_3.png',
    'folder_id' => $child_for_australia->id,
]);

/////////////////////////////////////////////////////////////////////////////////

//Sydney

Region::create([
    'name' => 'Sydney',
    'description' => 'it is very beautiful city which has a lot of things',
    'region_id' => Region::where('name', 'Australia')->where('region_id', null)->first()->id,
]);

$parent_folder_for_sydney = Folder::create([
    'name' => 'Rome',
    'folder_id' => $parent_for_australia->id,
]);

Storage::makeDirectory('Regions/Australia/Sydney');

Storage::copy('seeding_pictures/Sydney_pic_1.png','Regions/Australia/Sydney/Sydney_pic_1.png');
Storage::copy('seeding_pictures/Sydney_pic_2.png','Regions/Australia/Sydney/Sydney_pic_2.png');
Storage::copy('seeding_pictures/Sydney_pic_3.png','Regions/Australia/Sydney/Sydney_pic_3.png');

Photo::create([
    'name' => 'Sydney_pic_1.png',
    'folder_id' => $parent_folder_for_sydney->id,
]);
Photo::create([
    'name' => 'Sydney_pic_2.png',
    'folder_id' => $parent_folder_for_sydney->id,
]);
Photo::create([
    'name' => 'Sydney_pic_3.png',
    'folder_id' => $parent_folder_for_sydney->id,
]);

/////////////////////////////////////////////////////////////////

Region::create([
    'name' => 'Melbourne',
    'description' => 'it is very beautiful city which has a lot of things',
    'region_id' => Region::where('name', 'Australia')->where('region_id', null)->first()->id,
]);

$parent_folder_for_melbourne = Folder::create([
    'name' => 'Melbourne',
    'folder_id' => $parent_for_australia->id,
]);

Storage::makeDirectory('Regions/Australia/Melbourne');

Storage::copy('seeding_pictures/Melbourne_pic_1.png','Regions/Australia/Melbourne/Melbourne_pic_1.png');
Storage::copy('seeding_pictures/Melbourne_pic_2.png','Regions/Australia/Melbourne/Melbourne_pic_2.png');
Storage::copy('seeding_pictures/Melbourne_pic_3.png','Regions/Australia/Melbourne/Melbourne_pic_3.png');

Photo::create([
    'name' => 'Melbourne_pic_1.png',
    'folder_id' => $parent_folder_for_melbourne->id,
]);
Photo::create([
    'name' => 'Melbourne_pic_2.png',
    'folder_id' => $parent_folder_for_melbourne->id,
]);
Photo::create([
    'name' => 'Melbourne_pic_3.png',
    'folder_id' => $parent_folder_for_melbourne->id,
]);


    }
}
