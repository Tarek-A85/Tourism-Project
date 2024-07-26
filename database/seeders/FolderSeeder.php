<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Folder;
class FolderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Folder::create([
            'name' => 'Regions',
            'folder_id' => null,
        ]);

        Folder::create([
            'name' => 'Packages',
            'folder_id' => null,
        ]);

        Folder::create([
            'name' => 'Airways',
            'folder_id' => null,
        ]);

        Folder::create([
            'name' => 'Hotels',
            'folder_id' => null,
        ]);
        
        Folder::create([
            'name' => 'Users',
            'folder_id' => null,
        ]);

        Folder::create([
            'name' => 'Companies',
            'folder_id' => null,
        ]);
    }
}
