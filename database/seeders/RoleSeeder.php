<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Definir recursos y acciones
        $resources = ['proveedors', 'conductors', 'empresas'];
        $actions = ['view', 'create', 'edit', 'delete'];

        // Crear permisos
        $allPermissions = [];
        foreach ($resources as $resource) {
            foreach ($actions as $action) {
                $permissionName = "$action $resource";
                Permission::findOrCreate($permissionName);
                $allPermissions[] = $permissionName;
            }
        }

        // Crear roles y asignar permisos
        $rolePermissions = [
            'admin' => $allPermissions, // Todos los permisos
            'editor' => ['view *', 'edit *'],
            'viewer' => ['view *'],
        ];

        foreach ($rolePermissions as $roleName => $permissions) {
            $role = Role::findOrCreate($roleName);
            
            if ($roleName === 'admin') {
                // Para el admin, asignamos todos los permisos individualmente
                $role->syncPermissions($allPermissions);
            } else {
                // Para otros roles, procesamos los permisos con comodines
                $rolePermissions = $this->processWildcardPermissions($permissions, $allPermissions);
                $role->syncPermissions($rolePermissions);
            }
        }
    }

    private function processWildcardPermissions($permissions, $allPermissions)
    {
        $processedPermissions = [];
        foreach ($permissions as $permission) {
            if (strpos($permission, '*') !== false) {
                // Si el permiso contiene un comodín, lo expandimos
                $wildcard = str_replace('*', '', $permission);
                $matchingPermissions = array_filter($allPermissions, function($p) use ($wildcard) {
                    return strpos($p, $wildcard) === 0;
                });
                $processedPermissions = array_merge($processedPermissions, $matchingPermissions);
            } else {
                $processedPermissions[] = $permission;
            }
        }
        return array_unique($processedPermissions);
    }
}