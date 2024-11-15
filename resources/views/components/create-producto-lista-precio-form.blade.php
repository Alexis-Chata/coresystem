<div x-data="{ open: false }" class="relative mb-3">
    <button @click="open = !open" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
        {{ $showCreateForm ? 'Cerrar' : 'Crear Nuevo Precio' }}
    </button>

    <div x-show="open" @click.away="open = false" class="absolute z-10 mt-2 w-96 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
        <form wire:submit.prevent="createProductoListaPrecio" class="p-4">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="producto_id">
                    Producto
                </label>
                <select wire:model="newProductoListaPrecio.producto_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="producto_id">
                    <option value="">Seleccione un producto</option>
                    @foreach(App\Models\Producto::all() as $producto)
                        <option value="{{ $producto->id }}">{{ $producto->name }}</option>
                    @endforeach
                </select>
                @error('newProductoListaPrecio.producto_id') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="lista_precio_id">
                    Lista de Precio
                </label>
                <select wire:model="newProductoListaPrecio.lista_precio_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="lista_precio_id">
                    <option value="">Seleccione una lista de precio</option>
                    @foreach(App\Models\ListaPrecio::all() as $listaPrecio)
                        <option value="{{ $listaPrecio->id }}">{{ $listaPrecio->name }}</option>
                    @endforeach
                </select>
                @error('newProductoListaPrecio.lista_precio_id') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="precio">
                    Precio
                </label>
                <input wire:model="newProductoListaPrecio.precio" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="precio" type="number" step="0.01" placeholder="0.00">
                @error('newProductoListaPrecio.precio') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
            </div>

            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Crear Precio
                </button>
            </div>
        </form>
    </div>
</div>