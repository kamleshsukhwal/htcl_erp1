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
    $modules = [
        'project', 'vendor', 'purchase-orders', 'dc-in', 'dc-outs',
        'execution', 'boq', 'hr', 'clients', 'user', 'ratings',
        'feedback', 'qa', 'ncr', 'audit', 'finance', 'dashboard',
    ];

    foreach ($modules as $module) {
        Module::firstOrCreate(['name' => $module]);
    }
}
}
