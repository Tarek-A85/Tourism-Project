<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Status;
class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach(['Completed', 'In progress', 'Has not started yet'] as $status){
            
            Status::create([
                'name' => $status,
            ]);
        }
    }
}
