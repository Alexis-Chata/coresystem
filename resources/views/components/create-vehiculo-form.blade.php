<div x-data="{ open: false }" class="relative">
    <button @click="open = !open" class="mb-2 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
        {{ $showCreateForm ? 'Cerrar' : 'Crear Nuevo Vehículo' }}
    </button>

    <div x-show="open" @click.away="open = false" class="absolute z-10 mt-2 w-[32rem] rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
        <form wire:submit.prevent="createVehiculo" class="p-4 grid grid-cols-2 gap-4">
            <div class="col-span-2 sm:col-span-1">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="marca">Marca</label>
                <input wire:model="newVehiculo.marca" id="marca" type="text" placeholder="Marca" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="col-span-2 sm:col-span-1">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="modelo">Modelo</label>
                <input wire:model="newVehiculo.modelo" id="modelo" type="text" placeholder="Modelo" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="col-span-2 sm:col-span-1">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="placa">Placa</label>
                <input wire:model="newVehiculo.placa" id="placa" type="text" placeholder="Placa" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                @error('newVehiculo.placa') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
            </div>
            <div class="col-span-2 sm:col-span-1">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="color">Color</label>
                <input wire:model="newVehiculo.color" id="color" type="text" placeholder="Color" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="col-span-2">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="certificado_inscripcion">Certificado de Inscripción</label>
                <input wire:model="newVehiculo.certificado_inscripcion" id="certificado_inscripcion" type="text" placeholder="Certificado de inscripción" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="col-span-2 sm:col-span-1">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="numero_tarjeta">Número de Tarjeta</label>
                <input wire:model="newVehiculo.numero_tarjeta" id="numero_tarjeta" type="text" placeholder="Número de tarjeta" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="col-span-2 sm:col-span-1">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="tonelaje_maximo">Tonelaje Máximo</label>
                <input wire:model="newVehiculo.tonelaje_maximo" id="tonelaje_maximo" type="text" placeholder="Tonelaje máximo" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>

            <div class="col-span-2 flex items-center justify-between">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Crear Vehículo
                </button>
            </div>
        </form>
    </div>
</div>
