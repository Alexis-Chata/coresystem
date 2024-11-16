<div x-data="{ open: false }" class="relative mb-3">
    <button @click="open = !open" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
        {{ $showCreateForm ? 'Cerrar' : 'Crear Nuevo Precio' }}
    </button>

    <div x-show="open" @click.away="open = false" class="absolute z-10 mt-2 w-96 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
        <form wire:submit.prevent="createProductoListaPrecio" class="p-4">
            <div class="mb-4">
                <x-searchable-select
                    :options="App\Models\Producto::all()->map(function($producto) {
                        return ['id' => $producto->id, 'name' => $producto->name];
                    })"
                    wire-model="newProductoListaPrecio.producto_id"
                    field="producto_id"
                    label="Producto"
                    placeholder="Buscar producto..."
                />
            </div>

            @foreach(App\Models\ListaPrecio::all() as $listaPrecio)
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="precio_{{ $listaPrecio->id }}">
                        Precio {{ $listaPrecio->name }}
                    </label>
                    <input 
                        wire:model="precios.{{ $listaPrecio->id }}" 
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                        id="precio_{{ $listaPrecio->id }}" 
                        type="number" 
                        step="0.01" 
                        placeholder="0.00"
                    >
                    @error('precios.' . $listaPrecio->id) <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
                </div>
            @endforeach

            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Guardar Precios
                </button>
            </div>
        </form>
    </div>
</div>