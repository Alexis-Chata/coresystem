<div x-data="{ open: @entangle('showCreateForm') }" class="relative mb-3">
    <button type="button" @click="open = !open" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
        <span x-text="open ? 'Cerrar' : 'Crear Nueva Marca'"></span>
    </button>

    <div x-show="open" x-transition @click.away="open = false" class="absolute z-10 mt-2 w-80 rounded-md shadow-lg bg-white ring-1 ring-black/5">
        <form wire:submit.prevent="createMarca" class="p-4 space-y-4">
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-1" for="codigo">
                    Código
                </label>
                <input wire:model.defer="newMarca.codigo" id="codigo" type="text" placeholder="Código"
                    class="shadow border rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('newMarca.codigo') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-1" for="name">Nombre</label>
                <input wire:model.defer="newMarca.name" id="name" type="text" placeholder="Nombre"
                    class="shadow border rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('newMarca.name') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-1" for="empresa_id">Empresa</label>
                <select wire:model.defer="newMarca.empresa_id" id="empresa_id"
                    class="shadow border rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Seleccione una empresa</option>
                    @foreach (\App\Models\Empresa::all() as $empresa)
                        <option value="{{ $empresa->id }}">{{ $empresa->razon_social }}</option>
                    @endforeach
                </select>
                @error('newMarca.empresa_id') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
            </div>

            {{-- Resaltar cobertura (switch) --}}
            <div class="flex items-center justify-between">
                <div>
                    <label class="block text-gray-700 text-sm font-bold">Resaltar cobertura</label>
                    <p class="text-xs text-gray-500">Marca visualmente esta marca en listados.</p>
                </div>

                <label class="inline-flex items-center cursor-pointer select-none">
                    <input type="checkbox" class="sr-only peer" wire:model.defer="newMarca.resaltar_cobertura">
                    <div
                        class="relative w-11 h-6 bg-gray-200 rounded-full peer peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-500
                                peer-checked:bg-blue-600
                                after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full
                                after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full">
                    </div>
                </label>
            </div>
            @error('newMarca.resaltar_cobertura')
                <span class="text-red-500 text-xs italic">{{ $message }}</span>
            @enderror

            {{-- Color identificador --}}
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-1">Color identificador</label>

                <div class="flex items-center gap-3">
                    {{-- Picker --}}
                    <input type="color" wire:model.defer="newMarca.color_identificador"
                        class="h-10 w-12 p-1 border rounded" title="Elegir color">

                    {{-- Hex editable --}}
                    <input type="text" wire:model.defer="newMarca.color_identificador" placeholder="#RRGGBB"
                        class="shadow border rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                @error('newMarca.color_identificador')
                    <span class="text-red-500 text-xs italic">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" @click="open = false"
                    class="bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold py-2 px-4 rounded">
                    Cancelar
                </button>

                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Crear Marca
                </button>
            </div>
        </form>
    </div>
</div>
