<?php

namespace App\Livewire;

use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermisosRoles extends Component
{
    public function render()
    {
        $roles = Role::with(['permissions'])->get();
        //dd($roles);
        return view('livewire.permisos-roles', compact('roles'));
    }

    public $isOpen = false;
    public $roleId, $roleName, $permissions = [];
    public $selectedPermissions = [];
    public $permissionsGrouped = [];

    // Create Role modal state
    public $isCreateOpen = false;
    public $newRoleName = '';
    public $createPermissions = [];
    public $selectedCreatePermissions = [];
    public $createPermissionsGrouped = [];

    public function openModal($id)
    {
        $role = Role::findOrFail($id);
        $this->roleId = $role->id;
        $this->roleName = $role->name;
        $this->permissions = Permission::all();
        $this->permissionsGrouped = $this->groupPermissions($this->permissions);

        // Obtener permisos asignados al Rol
        $this->selectedPermissions = $role->permissions->pluck('id')->toArray();

        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->reset(['isOpen', 'roleId', 'roleName', 'selectedPermissions', 'permissions', 'permissionsGrouped']);
    }

    public function updateRolesPermissions()
    {
        $this->validate([
            'selectedPermissions' => 'array|min:1', // Asegura que al menos un permiso sea seleccionado
        ]);

        $role = Role::findOrFail($this->roleId);

        // Convertir IDs a nombres
        $permissionNames = Permission::whereIn('id', $this->selectedPermissions)->pluck('name')->toArray();

        $role->syncPermissions($permissionNames);

        $this->closeModal();
    }

    // Crear Rol
    public function openCreateModal()
    {
        $this->reset(['newRoleName', 'selectedCreatePermissions']);
        $this->createPermissions = Permission::all();
        $this->createPermissionsGrouped = $this->groupPermissions($this->createPermissions);
        $this->isCreateOpen = true;
    }

    public function closeCreateModal()
    {
        $this->reset(['isCreateOpen', 'newRoleName', 'selectedCreatePermissions', 'createPermissions', 'createPermissionsGrouped']);
    }

    public function createRole()
    {
        $this->validate([
            'newRoleName' => 'required|string|min:2|max:50|unique:roles,name',
            'selectedCreatePermissions' => 'array|min:0',
        ]);

        $role = Role::findOrCreate($this->newRoleName);

        if (!empty($this->selectedCreatePermissions)) {
            $permissionNames = Permission::whereIn('id', $this->selectedCreatePermissions)->pluck('name')->toArray();
            $role->syncPermissions($permissionNames);
        }

        $this->closeCreateModal();
    }

    private function groupPermissions($permissions)
    {
        // Agrupa por el recurso (última palabra del nombre del permiso)
        // Ej: "edit vehiculo" => recurso: vehiculo, accion: edit
        $grouped = [];
        foreach ($permissions as $perm) {
            $parts = explode(' ', $perm->name);
            $resource = trim(end($parts));
            if (!isset($grouped[$resource])) {
                $grouped[$resource] = [];
            }
            $grouped[$resource][] = $perm;
        }

        ksort($grouped);
        return $grouped;
    }
}
