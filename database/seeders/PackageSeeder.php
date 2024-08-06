<?php

namespace Database\Seeders;

use App\Models\Package;
use App\Models\PackageArea;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

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
    }
}
