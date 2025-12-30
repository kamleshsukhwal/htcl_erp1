<?php

namespace Database\Seeders;

use App\Models\Module;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
public function run()
{
    $modules = ['hr', 'finance', 'project', 'boq'];

    foreach ($modules as $module) {
        Module::firstOrCreate(['name' => $module]);
    }
}
}
