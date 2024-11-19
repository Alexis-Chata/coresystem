<div x-data="{ open: false }" class="relative">
    <button @click="open = !open" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
        {{ $showCreateForm ? 'Cerrar' : 'Crear Nueva Serie' }}
    </button>

    <div x-show="open" @click.away="open = false" class="absolute z-10 mt-2 w-96 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
        <form wire:submit.prevent="createFSerie" class="p-4">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="serie">
                    serie
                </label>
                <input wire:model="newFSerie.serie" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="serie" type="text" placeholder="serie">
                @error('newFSerie.serie') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="correlativo">
                    Correlativo
                </label>
                <input wire:model="newFSerie.correlativo" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="correlativo" type="text" placeholder="Correlativo">
                @error('newFSerie.correlativo') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="fechaemision">
                    Fecha Emision
                </label>
                <input wire:model="newFSerie.fechaemision" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="fechaemision" type="date" placeholder="Fecha Emision">
                @error('newFSerie.fechaemision') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="f_sede_id">
                    Sede
                </label>
                <select wire:model="newFSerie.f_sede_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="f_sede_id">
                    <option value="">Seleccione Sede</option>
                    @foreach($f_sedes as $SelectOptions)
                        <option value="{{ $SelectOptions->id }}">{{ $SelectOptions->name }}</option>
                    @endforeach
                </select>
                @error('newFSerie.f_sede_id') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="f_tipo_comprobante_id">
                    Tipo Comprobante
                </label>
                <select wire:model="newFSerie.f_tipo_comprobante_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="f_tipo_comprobante_id">
                    <option value="">Seleccione Tipo Comprobante</option>
                    @foreach($f_tipo_comprobantes as $SelectOptions)
                        <option value="{{ $SelectOptions->id }}">{{ $SelectOptions->name }}</option>
                    @endforeach
                </select>
                @error('newFSerie.f_tipo_comprobante_id') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
            </div>

            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Crear Serie
                </button>
            </div>
        </form>
    </div>
</div>
