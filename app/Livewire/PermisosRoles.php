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

    public function openModal($id)
    {
        $role = Role::findOrFail($id);
        $this->roleId = $role->id;
        $this->roleName = $role->name;
        $this->permissions = Permission::all();

        // Obtener permisos asignados al Rol
        $this->selectedPermissions = $role->permissions->pluck('id')->toArray();

        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->reset(['isOpen', 'roleId', 'roleName', 'selectedPermissions', 'permissions']);
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

}
