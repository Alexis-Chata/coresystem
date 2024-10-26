<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    // Define los recursos y sus acciones permitidas
    protected $resourcePermissions = [
        'proveedors' => ['view', 'create', 'edit', 'delete'],
        'conductors' => ['view', 'create', 'edit', 'delete'],
        'empresas' => ['view', 'create', 'edit', 'delete'],
        //Permisos para vista en el menu
        'menuEmpleado' => ['view'],
        
    ];

    // Define los roles y sus permisos
    protected $rolePermissions = [
        'admin' => ['*'], // Todos los permisos
        'editor' => [
            'proveedors' => ['view', 'edit'],
            'conductors' => ['view', 'edit'],
            'empresas' => ['view', 'edit'],
            
        ],
        'viewer' => [
            'proveedors' => ['view'],
            'conductors' => ['view'],
            'empresas' => ['view'],
            
        ],
    ];

    public function run(): void
    {
        // Crear todos los permisos
        $allPermissions = $this->createPermissions();

        // Crear roles y asignar permisos
        foreach ($this->rolePermissions as $roleName => $permissions) {
            $role = Role::findOrCreate($roleName);
            $rolePermissions = $this->processRolePermissions($permissions, $allPermissions);
            $role->syncPermissions($rolePermissions);
        }
    }

    protected function createPermissions(): array
    {
        $allPermissions = [];
        foreach ($this->resourcePermissions as $resource => $actions) {
            foreach ($actions as $action) {
                $permissionName = "$action $resource";
                Permission::findOrCreate($permissionName);
                $allPermissions[] = $permissionName;
            }
        }
        return $allPermissions;
    }

    protected function processRolePermissions($permissions, $allPermissions): array
    {
        if ($permissions === ['*']) {
            return $allPermissions;
        }

        $processedPermissions = [];
        foreach ($permissions as $resource => $actions) {
            if ($actions === ['*']) {
                $processedPermissions = array_merge(
                    $processedPermissions,
                    array_filter($allPermissions, fn($p) => strpos($p, $resource) !== false)
                );
            } else {
                foreach ($actions as $action) {
                    $processedPermissions[] = "$action $resource";
                }
            }
        }
        return $processedPermissions;
    }
}