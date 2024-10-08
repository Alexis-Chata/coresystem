<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        $adminRole = Role::findByName('admin');
        $editorRole = Role::findByName('editor');
        $viewerRole = Role::findByName('viewer');

        $adminRole->givePermissionTo(Permission::all());
        $editorRole->givePermissionTo(['edit conductors', 'view conductors']);
        $viewerRole->givePermissionTo(['view conductors']);
    }
}