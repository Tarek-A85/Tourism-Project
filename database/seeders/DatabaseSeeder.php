<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        $this->call(AdminSeeder::class);

        $this->call(UserSeeder::class);

        $this->call(FolderSeeder::class);

        $this->call(FlightTypeSeeder::class);

        $this->call(AirportSeeder::class);

        $this->call(DateSeeder::class);

        $this->call(RegionSeeder::class);
        
        $this->call(HotelSeeder::class);
        
        $this->call(CompanySeeder::class);

        $this->call(PackageTypeSeeder::class);

        $this->call(PackageSeeder::class);

        $this->call(TransactionTypeSeeder::class);

        $this->call(StatusSeeder::class);

        


    }
}
