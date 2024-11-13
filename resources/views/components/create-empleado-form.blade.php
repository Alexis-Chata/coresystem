<div x-data="{ open: false }" class="relative">
    <button @click="open = !open" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
        {{ $showCreateForm ? 'Cerrar' : 'Crear Nuevo Empleado' }}
    </button>

    <div x-show="open" @click.away="open = false" class="absolute z-10 mt-2 w-96 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
        <form wire:submit.prevent="createEmpleado" class="p-4">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="codigo">
                    Código
                </label>
                <input wire:model="newEmpleado.codigo" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="codigo" type="text" placeholder="Código del empleado">
                @error('newEmpleado.codigo') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="name">
                    Nombre
                </label>
                <input wire:model="newEmpleado.name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="name" type="text" placeholder="Nombre del empleado">
                @error('newEmpleado.name') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="direccion">
                    Dirección
                </label>
                <input wire:model="newEmpleado.direccion" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="direccion" type="text" placeholder="Dirección del empleado">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="celular">
                    Celular
                </label>
                <input wire:model="newEmpleado.celular" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="celular" type="text" placeholder="Número de celular">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="f_tipo_documento_id">
                    Tipo de Documento
                </label>
                <select wire:model="newEmpleado.f_tipo_documento_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="f_tipo_documento_id">
                    <option value="">Seleccione un tipo de documento</option>
                    @foreach(App\Models\FTipoDocumento::all() as $tipoDocumento)
                        <option value="{{ $tipoDocumento->id }}">{{ $tipoDocumento->name }}</option>
                    @endforeach
                </select>
                @error('newEmpleado.f_tipo_documento_id') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="numero_documento">
                    Número de Documento
                </label>
                <input wire:model="newEmpleado.numero_documento" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="numero_documento" type="text" placeholder="Número de documento">
                @error('newEmpleado.numero_documento') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="tipo_empleado">
                    Tipo de Empleado
                </label>
                <select wire:model="newEmpleado.tipo_empleado" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="tipo_empleado">
                    <option value="">Seleccione un tipo de empleado</option>
                    <option value="conductor">Conductor</option>
                    <option value="vendedor">Vendedor</option>
                    <option value="almacenero">Almacenero</option>
                </select>
                @error('newEmpleado.tipo_empleado') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="numero_brevete">
                    Número de Brevete
                </label>
                <input wire:model="newEmpleado.numero_brevete" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="numero_brevete" type="text" placeholder="Número de brevete">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="empresa_id">
                    Empresa
                </label>
                <select wire:model="newEmpleado.empresa_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="empresa_id">
                    <option value="">Seleccione una empresa</option>
                    @foreach(App\Models\Empresa::all() as $empresa)
                        <option value="{{ $empresa->id }}">{{ $empresa->razon_social }}</option>
                    @endforeach
                </select>
                @error('newEmpleado.empresa_id') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="vehiculo_id">
                    Vehículo
                </label>
                <select wire:model="newEmpleado.vehiculo_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="vehiculo_id">
                    <option value="">Seleccione un vehículo</option>
                    @foreach(App\Models\Vehiculo::all() as $vehiculo)
                        <option value="{{ $vehiculo->id }}">{{ $vehiculo->placa }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Crear Empleado
                </button>
            </div>
        </form>
    </div>
</div>
