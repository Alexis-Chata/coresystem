<div x-data="{ open: false }" class="relative">
    <button @click="open = !open" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
        {{ $showCreateForm ? 'Cerrar' : 'Crear Nueva Empresa' }}
    </button>

    <div x-show="open" @click.away="open = false" class="absolute z-10 mt-2 w-96 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
        <form wire:submit.prevent="createEmpresa" class="p-4">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="ruc">
                    RUC
                </label>
                <input wire:model="newEmpresa.ruc" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="ruc" type="text" placeholder="RUC">
                @error('newEmpresa.ruc') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="razon_social">
                    Razón Social
                </label>
                <input wire:model="newEmpresa.razon_social" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="razon_social" type="text" placeholder="Razón Social">
                @error('newEmpresa.razon_social') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="name_comercial">
                    Nombre Comercial
                </label>
                <input wire:model="newEmpresa.name_comercial" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="name_comercial" type="text" placeholder="Nombre Comercial">
                @error('newEmpresa.name_comercial') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="direccion">
                    Dirección
                </label>
                <input wire:model="newEmpresa.direccion" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="direccion" type="text" placeholder="Dirección">
                @error('newEmpresa.direccion') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="logo_path">
                    Logo
                </label>
                <input wire:model="newEmpresa.logo_path" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="logo_path" type="file" placeholder="Logo">
                @error('newEmpresa.logo_path') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="cert_path">
                    Certificado
                </label>
                <input wire:model="newEmpresa.cert_path" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="cert_path" type="file" placeholder="Certificado">
                @error('newEmpresa.cert_path') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="sol_user">
                    Usuario SOL
                </label>
                <input wire:model="newEmpresa.sol_user" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="sol_user" type="text" placeholder="Usuario SOL">
                @error('newEmpresa.sol_user') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="sol_pass">
                    Clave SOL
                </label>
                <input wire:model="newEmpresa.sol_pass" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="sol_pass" type="text" placeholder="Clave SOL">
                @error('newEmpresa.sol_pass') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="client_id">
                    Cliente id
                </label>
                <input wire:model="newEmpresa.client_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="client_id" type="text" placeholder="Cliente id">
                @error('newEmpresa.client_id') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="client_secret">
                    Cliente Secret
                </label>
                <input wire:model="newEmpresa.client_secret" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="client_secret" type="text" placeholder="Cliente Secret">
                @error('newEmpresa.client_secret') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="production">
                    Production
                </label>
                <select wire:model="newEmpresa.production" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="production">
                    <option value="">Seleccione production</option>
                    @foreach($productionSelectOptions as $key => $SelectOptions)
                        <option value="{{ $key }}">{{ $SelectOptions }}</option>
                    @endforeach
                </select>
                @error('newEmpresa.production') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
            </div>

            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Crear Cliente
                </button>
            </div>
        </form>
    </div>
</div>
