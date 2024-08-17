<?php

namespace Database\Seeders;

use App\Models\Package;
use App\Models\TripDetail;
use App\Models\PackageArea;
use App\Models\Photo;
use App\Models\Date;
use App\Models\Folder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    private function add_to_package_area($packageId, $visitable, $type)
    {
        PackageArea::create([
            'package_id' => $packageId,
            'visitable_id' => $visitable[0],
            'visitable_type' => $type,
            'period' => $visitable[1]
        ]);
    }

    public function run(): void
    {
        $package1 = Package::create([
            'name' => 'Syrain trip',
            'description' => 'visit the land of yasamin',
            'adult_price' => 100,
            'child_price' => 75,
            'period' => 50
        ]);
        $this->add_to_package_area($package1->id,[1,10],'Hotel');
        $this->add_to_package_area($package1->id,[1,10],'Region');
        $this->add_to_package_area($package1->id,[2,15],'Region');
        $package1->companies()->syncWithoutDetaching([1]);
        $package1->types()->syncWithoutDetaching([1,2]);

        $package1 = Package::create([
            'name' => 'to homs package',
            'description' => 'Let\'s go to Homs to drink yerba mate',
            'adult_price' => 50,
            'child_price' => 50,
            'period' => 20
        ]);
        $this->add_to_package_area($package1->id,[1,10],'Hotel');
        $this->add_to_package_area($package1->id,[1,10],'Region');
        $this->add_to_package_area($package1->id,[2,10],'Region');
        $package1->types()->syncWithoutDetaching([2]);

        /////////////////////////////////////////////////////////////

        $package2 = Package::create([
            'name' => 'Italian trip',
            'description' => 'let us explore the beauty of italy and experince the best things there, this is a once in a lifetime trip so we highly recommend you to live it with us, you can take whatever you want with you there is no limitations',
            'adult_price' => 3000,
            'child_price' => 1000,
            'period' => 7
        ]);

        $this->add_to_package_area($package2->id,[8,4],'Region');
        $this->add_to_package_area($package2->id,[7,7],'Region');
        $this->add_to_package_area($package2->id,[9,3],'Region');
        $this->add_to_package_area($package2->id,[3,7],'Hotel');

        $package2->companies()->syncWithoutDetaching([3]);

        $package2->types()->syncWithoutDetaching([5,8,10]);

        $package_2_folder = Folder::create([
            'name' => 'Italian trip',
            'folder_id' => Folder::where('name', 'Packages')->where('folder_id', null)->first()->id,
        ]);

        Storage::makeDirectory('Packages');

        Storage::copy('seeding_pictures/Italian trip_pic_1.png', 'Packages/Italian trip_pic_1.png');
        Storage::copy('seeding_pictures/Italian trip_pic_2.png', 'Packages/Italian trip_pic_2.png');
        Storage::copy('seeding_pictures/Italian trip_pic_3.png', 'Packages/Italian trip_pic_3.png');
        Storage::copy('seeding_pictures/Italian trip_pic_4.png', 'Packages/Italian trip_pic_4.png');
        Storage::copy('seeding_pictures/Italian trip_pic_5.png', 'Packages/Italian trip_pic_5.png');

        for($i=1; $i<=5; $i++){
        Photo::create([
            'name' => 'Italian trip_pic_' . $i . '.png',
            'folder_id' => $package_2_folder->id
        ]);

    }

        TripDetail::create([
            'package_id' => $package2->id,
            'date_id' => Date::where('date', '2024-09-01')->first()->id,
            'time' => now()->toTimeString(),
            'num_of_tickets' => 50,
            'available_tickets' => 50,
        ]);

        TripDetail::create([
            'package_id' => $package2->id,
            'date_id' => Date::where('date', '2024-09-07')->first()->id,
            'time' => now()->toTimeString(),
            'num_of_tickets' => 40,
            'available_tickets' => 40,
        ]);

/////////////////////////////////////////////

$package3 = Package::create([
    'name' => 'Australian trip',
    'description' => 'let us explore the beauty of australia and experince the best things there, this is a once in a lifetime trip so we highly recommend you to live it with us, you can take whatever you want with you there is no limitations',
    'adult_price' => 4000,
    'child_price' => 2000,
    'period' => 3
]);

$this->add_to_package_area($package3->id,[11,2],'Region');
$this->add_to_package_area($package3->id,[10,3],'Region');
$this->add_to_package_area($package3->id,[12,1],'Region');
$this->add_to_package_area($package3->id,[4,1],'Hotel');

$package3->types()->syncWithoutDetaching([2,1,3]);

$package_3_folder = Folder::create([
    'name' => 'Australian trip',
    'folder_id' => Folder::where('name', 'Packages')->where('folder_id', null)->first()->id,
]);

Storage::makeDirectory('Packages');

Storage::copy('seeding_pictures/Australian trip_pic_1.png', 'Packages/Australian trip_pic_1.png');
Storage::copy('seeding_pictures/Australian trip_pic_2.png', 'Packages/Australian trip_pic_2.png');
Storage::copy('seeding_pictures/Australian trip_pic_3.png', 'Packages/Australian trip_pic_3.png');
Storage::copy('seeding_pictures/Australian trip_pic_4.png', 'Packages/Australian trip_pic_4.png');
Storage::copy('seeding_pictures/Australian trip_pic_5.png', 'Packages/Australian trip_pic_5.png');

for($i=1; $i<=5; $i++){
    Photo::create([
        'name' => 'Australian trip_pic_' . $i . '.png',
        'folder_id' => $package_3_folder->id
    ]);

}

TripDetail::create([
    'package_id' => $package3->id,
    'date_id' => Date::where('date', '2024-09-03')->first()->id,
    'time' => now()->toTimeString(),
    'num_of_tickets' => 75,
    'available_tickets' => 75,
]);

TripDetail::create([
    'package_id' => $package3->id,
    'date_id' => Date::where('date', '2024-09-020')->first()->id,
    'time' => now()->toTimeString(),
    'num_of_tickets' => 55,
    'available_tickets' => 55,
]);

    }

}
