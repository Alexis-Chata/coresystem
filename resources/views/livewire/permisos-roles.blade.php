<div class="p-0 sm:p-3">
    <h2 class="text-xl md:text-2xl font-semibold mb-4 text-center md:text-left">
        Administración de Permisos de los Roles
    </h2>

    <div class="overflow-x-auto">
        <table class="w-full border border-gray-200 rounded-lg text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-2 md:p-3 text-left">Rol</th>
                    <th class="p-2 md:p-3 text-left hidden md:table-cell">Guard</th>
                    <th class="p-2 md:p-3 text-left">Permisos</th>
                    <th class="p-2 md:p-3 text-left">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($roles as $role)
                    <tr class="border-t">
                        <td class="px-2 md:px-3 py-2">{{ $role->name }}</td>
                        <td class="px-2 md:px-3 py-2 hidden md:table-cell">{{ $role->guard_name }}</td>
                        <td class="px-2 md:px-3 py-2">
                            <div class="flex flex-wrap gap-1">
                                <span class="px-2 py-1 text-xs bg-neutral-200 text-gray-800 rounded">
                                    {{ $role->permissions->count() }}-Permisos
                                </span>
                                @foreach ($role->permissions as $permission)
                                    <span class="px-2 py-1 text-xs bg-green-200 text-green-800 rounded">
                                        {{ $permission->name }}
                                    </span>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-2 md:px-3 py-2">
                            <button wire:click="openModal({{ $role->id }})"
                                class="px-3 py-1 text-xs md:text-sm text-white bg-blue-600 rounded-md">
                                Editar
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Modal -->
    <div x-data="{ open: @entangle('isOpen') }" x-show="open"
        class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 p-3">
        <div class="bg-white p-4 md:p-6 rounded-lg shadow-lg w-full max-w-xs md:max-w-md max-h-[80vh] overflow-y-auto"
            @click.away="open = false">
            <h2 class="text-lg md:text-xl font-semibold">Editar Permisos</h2>

            <p class="mt-2 text-gray-700 text-sm">Rol: <strong>{{ $roleName }}</strong></p>

            <div class="mt-4 max-h-[50vh] overflow-y-auto border p-2 rounded-md text-sm">
                <h3 class="font-semibold">Permisos</h3>
                @foreach ($permissions as $permission)
                    <div class="flex items-center text-sm">
                        <label class="flex items-center">
                            <input type="checkbox" wire:model="selectedPermissions" value="{{ $permission->id }}"
                                class="mr-2">
                            {{ $permission->name }}
                        </label>
                    </div>
                @endforeach
            </div>

            <!-- Botones -->
            <div class="flex justify-end mt-4">
                <button wire:click="closeModal"
                    class="px-3 py-1 md:px-4 md:py-2 bg-gray-500 text-white rounded-md text-xs md:text-sm mr-2">
                    Cancelar
                </button>
                <button wire:click="updateRolesPermissions"
                    class="px-3 py-1 md:px-4 md:py-2 bg-blue-600 text-white rounded-md text-xs md:text-sm">
                    Guardar
                </button>
            </div>
        </div>
    </div>
</div>
