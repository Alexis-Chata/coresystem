<div x-data="{ open: false }" class="relative">
    <button @click="open = !open" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
        {{ $showCreateForm ? 'Cerrar' : 'Crear Nuevo Cliente' }}
    </button>

    <div x-show="open" @click.away="open = false" class="absolute z-10 mt-2 w-96 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
        <form wire:submit.prevent="createCliente" class="p-4">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="razon_social">
                    Razón Social
                </label>
                <input wire:model="newCliente.razon_social" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="razon_social" type="text" placeholder="Razón Social">
                @error('newCliente.razon_social') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="direccion">
                    Dirección
                </label>
                <input wire:model="newCliente.direccion" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="direccion" type="text" placeholder="Dirección">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="f_tipo_documento_id">
                    Tipo de Documento
                </label>
                <select wire:model="newCliente.f_tipo_documento_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="f_tipo_documento_id">
                    <option value="">Seleccione un tipo de documento</option>
                    @foreach(App\Models\F_tipo_documento::all() as $tipoDocumento)
                        <option value="{{ $tipoDocumento->id }}">{{ $tipoDocumento->tipo_documento }}</option>
                    @endforeach
                </select>
                @error('newCliente.f_tipo_documento_id') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="numero_documento">
                    Número de Documento
                </label>
                <input wire:model="newCliente.numero_documento" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="numero_documento" type="text" placeholder="Número de Documento">
                @error('newCliente.numero_documento') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="celular">
                    Celular
                </label>
                <input wire:model="newCliente.celular" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="celular" type="text" placeholder="Celular">
            </div>

            <!-- Empresa -->
            <div class="mb-4" style="display: none;">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="empresa_id">
                    Empresa
                </label>
                @php
                    $empresa = App\Models\Empresa::first();
                    $this->newCliente['empresa_id'] = $empresa->id; // Establecer el valor inicial
                @endphp
                <select wire:model="newCliente.empresa_id" 
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                        id="empresa_id">
                    <option value="{{ $empresa->id }}">{{ $empresa->razon_social }}</option>
                </select>
                @error('newCliente.empresa_id') 
                    <span class="text-red-500 text-xs italic">{{ $message }}</span> 
                @enderror
            </div>

            <!-- Ruta -->
            <div class="mb-4">
                <x-searchable-select
                    :options="App\Models\Ruta::when(
                        auth()->user()->empleados()->first()?->tipo_empleado === 'vendedor',
                        fn($query) => $query->where('vendedor_id', auth()->user()->empleados()->first()->id)
                    )->get()->map(function($ruta) {
                        return ['id' => $ruta->id, 'name' => $ruta->name];
                    })"
                    wire-model="newCliente.ruta_id"
                    field="ruta_id"
                    label="Ruta"
                    placeholder="Buscar ruta..."
                />
            </div>

            <!-- Lista de Precios -->
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="lista_precio_id">
                    Lista de Precios
                </label>
                <select wire:model="newCliente.lista_precio_id" 
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                        id="lista_precio_id"
                        style="pointer-events: none;"
                        {{ $newCliente['ruta_id'] ? 'disabled' : '' }}>  {{-- Deshabilitar si hay una ruta seleccionada --}}
                    <option value="">Seleccione una lista de precios</option>
                    @foreach(App\Models\Lista_precio::all() as $listaPrecio)
                        <option value="{{ $listaPrecio->id }}">{{ $listaPrecio->name }}</option>
                    @endforeach
                </select>
                @if($newCliente['ruta_id'])
                    <p class="text-sm text-gray-500 mt-1">Precio seleccionado según la ruta.</p>
                @endif
                @error('newCliente.lista_precio_id') 
                    <span class="text-red-500 text-xs italic">{{ $message }}</span> 
                @enderror
            </div>

            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Crear Cliente
                </button>
            </div>
        </form>
    </div>
</div>