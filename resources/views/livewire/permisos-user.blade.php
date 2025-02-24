<div>
    <div class="p-3">
        <h2 class="text-2xl font-semibold mb-4">Administración de Permisos de Usuarios</h2>

        <div class="overflow-x-auto">
            <table class="w-full border border-gray-200 rounded-lg">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-3 text-left">Nombre</th>
                        <th class="p-3 text-left">Email</th>
                        <th class="p-3 text-left">Cod-Vendedor</th>
                        <th class="p-3 text-left">Roles</th>
                        <th class="p-3 text-left">Permisos</th>
                        <th class="p-3 text-left">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr class="border-t h-full">
                            <td class="px-3 py-2">{{ $user->name }}</td>
                            <td class="px-3 py-2">{{ $user->email }}</td>
                            <td class="px-3 py-2">
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($user->user_empleados as $user_empleado)
                                        <span class="px-2 py-1 text-xs bg-neutral-200 text-gray-600 rounded">
                                            {{ $user_empleado->empleado->id }}-{{ $user_empleado->empleado->name }}-{{ $user_empleado->tipo }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-3 py-2">
                                @foreach ($user->roles as $role)
                                    <span
                                        class="px-2 py-1 text-xs bg-blue-200 text-blue-800 rounded">{{ $role->name }}</span>
                                @endforeach
                            </td>
                            <td class="px-3 py-2">
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($user->permissions as $permission)
                                        <span class="px-2 py-1 text-xs bg-green-200 text-green-800 rounded">
                                            {{ $permission->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-3 py-2">
                                <!-- Botón para abrir el modal -->
                                <button wire:click="openModal({{ $user->id }})"
                                    class="px-4 py-2 text-white bg-blue-600 rounded-md">
                                    Editar
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div>
            <!-- Fondo Oscuro (Modal) -->
            <div x-data="{ open: @entangle('isOpen') }" x-show="open"
                class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
                <div class="bg-white p-6 rounded-lg shadow-lg w-96 max-h-[80vh] overflow-y-auto"
                    @click.away="open = false">
                    <h2 class="text-xl font-semibold">Editar Roles y Permisos</h2>

                    <p class="mt-2 text-gray-700">Usuario: <strong>{{ $userName }}</strong></p>

                    <!-- Sección con Scroll -->
                    <div class="mt-4 max-h-[50vh] overflow-y-auto border p-2 rounded-md">
                        <h3 class="text-md font-semibold">Roles</h3>
                        @foreach ($roles as $role)
                            <div class="flex items-center">
                                <label>
                                    <input type="checkbox" wire:model="selectedRoles" value="{{ $role->id }}"
                                        class="mr-2">
                                    {{ $role->name }}
                                </label>
                            </div>
                        @endforeach

                        <h3 class="text-md font-semibold mt-4">Permisos</h3>
                        @foreach ($permissions as $permission)
                            <div class="flex items-center">
                                <label>
                                    <input type="checkbox" wire:model="selectedPermissions"
                                        value="{{ $permission->id }}" class="mr-2">
                                    {{ $permission->name }}
                                </label>
                            </div>
                        @endforeach
                    </div>

                    <!-- Botones -->
                    <div class="flex justify-end mt-4">
                        <button wire:click="closeModal" class="px-4 py-2 bg-gray-500 text-white rounded-md mr-2">
                            Cancelar
                        </button>
                        <button wire:click="updateRolesPermissions" class="px-4 py-2 bg-blue-600 text-white rounded-md">
                            Guardar
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
