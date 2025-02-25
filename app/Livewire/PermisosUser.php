<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermisosUser extends Component
{
    public function render()
    {
        $users = User::with(['roles', 'user_empleados.empleado'])->get();
        return view('livewire.permisos-user', compact('users'));
    }

    public $isOpen = false;
    public $userId, $userName, $roles = [], $permissions = [];
    public $selectedRoles = [], $selectedPermissions = [];

    public function openModal($id)
    {
        $user = User::findOrFail($id);
        $this->userId = $user->id;
        $this->userName = $user->name;
        $this->roles = Role::all();
        $this->permissions = Permission::all();

        // Obtener roles y permisos asignados al usuario
        $this->selectedRoles = $user->roles->pluck('id')->toArray();
        $this->selectedPermissions = $user->permissions->pluck('id')->toArray();

        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->reset(['isOpen', 'userId', 'userName', 'selectedRoles', 'selectedPermissions']);
    }

    public function updateRolesPermissions()
    {
        $user = User::findOrFail($this->userId);

        // Convertir IDs a nombres
        $roleNames = Role::whereIn('id', $this->selectedRoles)->pluck('name')->toArray();
        $permissionNames = Permission::whereIn('id', $this->selectedPermissions)->pluck('name')->toArray();

        $user->syncRoles($roleNames);
        $user->syncPermissions($permissionNames);

        $this->closeModal();
    }

}
