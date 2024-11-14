<div x-data="{ open: false }" class="mb-3 relative">
    <button @click="open = !open" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
        {{ $showCreateForm ? 'Cerrar' : 'Crear Nueva Ruta' }}
    </button>

    <div x-show="open" @click.away="open = false" class="absolute z-10 mt-2 w-96 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
        <form wire:submit.prevent="createRuta" class="p-4">

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="name">
                    Nombre
                </label>
                <input wire:model="newRuta.name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="name" type="text" placeholder="Nombre de la ruta">
                @error('newRuta.name') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="vendedor_id">
                    Vendedor
                </label>
                <select wire:model="newRuta.vendedor_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="vendedor_id">
                    <option value="">Seleccione un vendedor</option>
                    @foreach(App\Models\Empleado::where('tipo_empleado', 'vendedor')->get() as $vendedor)
                        <option value="{{ $vendedor->id }}">{{ $vendedor->name }}</option>
                    @endforeach
                </select>
                @error('newRuta.vendedor_id') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="empresa_id">
                    Empresa
                </label>
                <select wire:model="newRuta.empresa_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="empresa_id">
                    <option value="">Seleccione una empresa</option>
                    @foreach(App\Models\Empresa::all() as $empresa)
                        <option value="{{ $empresa->id }}">{{ $empresa->razon_social }}</option>
                    @endforeach
                </select>
                @error('newRuta.empresa_id') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="lista_precio_id">
                    Lista de Precios
                </label>
                <select wire:model="newRuta.lista_precio_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="lista_precio_id">
                    <option value="">Seleccione una lista de precios</option>
                    @foreach(App\Models\ListaPrecio::all() as $listaPrecio)
                        <option value="{{ $listaPrecio->id }}">{{ $listaPrecio->name }}</option>
                    @endforeach
                </select>
                @error('newRuta.lista_precio_id') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
            </div>

            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Crear Ruta
                </button>
            </div>
        </form>
    </div>
</div>
