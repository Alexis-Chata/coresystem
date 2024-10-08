<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Permission::create(['name' => 'edit conductors']);
        Permission::create(['name' => 'view conductors']);
        // Añade más permisos según sea necesario para otras tablas
    }
}
