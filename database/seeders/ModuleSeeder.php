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
        'feedback', 'qa', 'ncr', 'audit', 'finance', 'dashboard', 'admin',
    ];

    foreach ($modules as $module) {
        Module::firstOrCreate(
            ['name' => $module],
            ['status' => 1, 'is_enabled' => true, 'created_by' => 0, 'updated_by' => 0]
        );

        // Ensure status is set for already-existing rows
        Module::where('name', $module)->update(['status' => 1]);
    }
}
}
