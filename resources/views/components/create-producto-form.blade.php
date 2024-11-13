<div x-data="{ open: false }" class="relative">
    <button @click="open = !open" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
        {{ $showCreateForm ? 'Cerrar' : 'Crear Nuevo Producto' }}
    </button>

    <div x-show="open" @click.away="open = false" class="absolute z-10 mt-2 w-96 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
        <form wire:submit.prevent="createProducto" class="p-4">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="empresa_id">
                    Empresa
                </label>
                <select wire:model="newProducto.empresa_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="empresa_id">
                    <option value="">Seleccione una empresa</option>
                    @foreach(App\Models\Empresa::all() as $empresa)
                        <option value="{{ $empresa->id }}">{{ $empresa->razon_social }}</option>
                    @endforeach
                </select>
                @error('newProducto.empresa_id') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="marca_id">
                    Marca
                </label>
                <select wire:model="newProducto.marca_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="marca_id">
                    <option value="">Seleccione una marca</option>
                    @foreach(App\Models\Marca::all() as $marca)
                        <option value="{{ $marca->id }}">{{ $marca->name }}</option>
                    @endforeach
                </select>
                @error('newProducto.marca_id') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="categoria_id">
                    Categoría
                </label>
                <select wire:model="newProducto.categoria_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="categoria_id">
                    <option value="">Seleccione una categoría</option>
                    @foreach(App\Models\Categoria::all() as $categoria)
                        <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                    @endforeach
                </select>
                @error('newProducto.categoria_id') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="f_tipo_afectacion_id">
                    Tipo de Afectación
                </label>
                <select wire:model="newProducto.f_tipo_afectacion_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="f_tipo_afectacion_id">
                    <option value="">Seleccione un tipo de afectación</option>
                    @foreach(App\Models\FTipoAfectacion::all() as $tipoAfectacion)
                        <option value="{{ $tipoAfectacion->id }}">{{ $tipoAfectacion->name }}</option>
                    @endforeach
                </select>
                @error('newProducto.f_tipo_afectacion_id') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="porcentaje_igv">
                    Porcentaje IGV
                </label>
                <input wire:model="newProducto.porcentaje_igv" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="porcentaje_igv" type="number" step="0.01" placeholder="Porcentaje IGV">
                @error('newProducto.porcentaje_igv') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
            </div>

            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Crear Producto
                </button>
            </div>
        </form>
    </div>
</div>
