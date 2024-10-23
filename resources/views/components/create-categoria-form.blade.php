<div x-data="{ open: false }" class="relative">
    <button @click="open = !open" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
        {{ $showCreateForm ? 'Cerrar' : 'Crear Nueva Categoría' }}
    </button>

    <div x-show="open" @click.away="open = false" class="absolute z-10 mt-2 w-96 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
        <form wire:submit.prevent="createCategoria" class="p-4">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="nombre">
                    Nombre
                </label>
                <input wire:model="newCategoria.nombre" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="nombre" type="text" placeholder="Nombre de la categoría">
                @error('newCategoria.nombre') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="descripcion">
                    Descripción
                </label>
                <textarea wire:model="newCategoria.descripcion" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="descripcion" placeholder="Descripción de la categoría"></textarea>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="empresa_id">
                    Empresa
                </label>
                <select wire:model="newCategoria.empresa_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="empresa_id">
                    <option value="">Seleccione una empresa</option>
                    @foreach(App\Models\Empresa::all() as $empresa)
                        <option value="{{ $empresa->id }}">{{ $empresa->razon_social }}</option>
                    @endforeach
                </select>
                @error('newCategoria.empresa_id') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
            </div>

            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Crear Categoría
                </button>
            </div>
        </form>
    </div>
</div>