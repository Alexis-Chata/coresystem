<div class="flex flex-col gap-2">
    <fieldset class="space-x-4">
        <label class="inline-flex items-center cursor-pointer space-x-2">
            <input type="radio" wire:model="date_field" name="date_field" value="1"
                class="w-6 h-6 accent-green-600 cursor-pointer">
            <span>Fecha</span>
        </label>

        <label class="inline-flex items-center cursor-pointer space-x-2">
            <input type="radio" wire:model="date_field" name="date_field" value="2"
                class="w-6 h-6 accent-red-500 cursor-pointer">
            <span>Fecha Reparto</span>
        </label>
    </fieldset>

    <div class="flex flex-wrap gap-2 items-center sm:flex-row">
        <input type="date" wire:model.live="fecha_inicio"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring focus:ring-indigo-200 focus:border-indigo-500 transition-colors duration-200 ease-in-out">
        <input type="date" wire:model.live="fecha_fin"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring focus:ring-indigo-200 focus:border-indigo-500 transition-colors duration-200 ease-in-out">
    </div>
    <div class="flex flex-wrap gap-2 items-center sm:flex-row">
        <select wire:model="ruta_id"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring focus:ring-indigo-200 focus:border-indigo-500 transition-colors duration-200 ease-in-out">
            <option value="NULL">Selecciona una ruta</option>
            @foreach ($rutas as $ruta)
                <option value="{{ $ruta->id }}">{{ $ruta->id . '-' . $ruta->name }}</option>
            @endforeach
        </select>

        <select wire:model="marca_id"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring focus:ring-indigo-200 focus:border-indigo-500 transition-colors duration-200 ease-in-out">
            <option value="NULL">Selecciona una marca</option>
            @foreach ($marcas as $marca)
                <option value="{{ $marca->id }}">{{ $marca->id . '-' . $marca->name }}</option>
            @endforeach
        </select>

        <select wire:model="vendedor_id"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring focus:ring-indigo-200 focus:border-indigo-500 transition-colors duration-200 ease-in-out">
            <option value="NULL">Selecciona un vendedor</option>
            @foreach ($vendedores as $vendedor)
                <option value="{{ $vendedor->id }}">{{ $vendedor->id . '-' . $vendedor->name }}</option>
            @endforeach
        </select>

        <select wire:model="producto_id"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring focus:ring-indigo-200 focus:border-indigo-500 transition-colors duration-200 ease-in-out">
            <option value="NULL">Selecciona un producto</option>
            @foreach ($productos as $producto)
                <option value="{{ $producto->id }}">{{ $producto->id . '-' . $producto->name }}</option>
            @endforeach
        </select>

        <button wire:click="exportar_reporte"
            class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 focus:outline-none focus:ring focus:ring-indigo-200 focus:border-indigo-500 transition-colors duration-200 ease-in-out">
            Exportar
        </button>
        <div class="mb-2" wire:loading wire:target="exportar_reporte, fecha_inicio, fecha_fin">
            Cargando...
        </div>
    </div>

    <div class="flex flex-col gap-1.5">
        <br />
        <div>
            <label class="cursor-pointer space-x-1">
                <input type="checkbox" wire:model="producto_factor"
                    class="cursor-pointer w-4 h-4 text-blue-600 accent-blue-500">
                <span class="text-lg">Producto Factor</span>
            </label>
        </div>
        <div>
            <label class="cursor-pointer space-x-1">
                <input type="checkbox" wire:model="rutas_name"
                    class="cursor-pointer w-4 h-4 text-blue-600 accent-blue-500">
                <span class="text-lg">Rutas</span>
            </label>
        </div>
        <div>
            <label class="cursor-pointer space-x-1">
                <input type="checkbox" wire:model="usuario"
                    class="cursor-pointer w-4 h-4 text-blue-600 accent-blue-500">
                <span class="text-lg">Usuario</span>
            </label>
        </div>
        <div>
            <label class="cursor-pointer space-x-1">
                <input type="checkbox" wire:model="tipo_documento"
                    class="cursor-pointer w-4 h-4 text-blue-600 accent-blue-500">
                <span class="text-lg">Tipo Documento</span>
            </label>
        </div>
        <div>
            <label class="cursor-pointer space-x-1">
                <input type="checkbox" wire:model="conductor"
                    class="cursor-pointer w-4 h-4 text-blue-600 accent-blue-500">
                <span class="text-lg">Conductor</span>
            </label>
        </div>
        <div>
            <label class="cursor-pointer space-x-1">
                <input type="checkbox" wire:model="marcas_name"
                    class="cursor-pointer w-4 h-4 text-blue-600 accent-blue-500">
                <span class="text-lg">Marca Nombre</span>
            </label>
        </div>
        <div>
            <label class="cursor-pointer space-x-1">
                <input type="checkbox" wire:model="vendedor"
                    class="cursor-pointer w-4 h-4 text-blue-600 accent-blue-500">
                <span class="text-lg">Vendedor</span>
            </label>
        </div>
        <div>
            <label class="cursor-pointer space-x-1">
                <input type="checkbox" wire:model="cliente"
                    class="cursor-pointer w-4 h-4 text-blue-600 accent-blue-500">
                <span class="text-lg">Cliente</span>
            </label>
        </div>
        <div>
            <label class="cursor-pointer space-x-1">
                <input type="checkbox" wire:model="num_documento"
                    class="cursor-pointer w-4 h-4 text-blue-600 accent-blue-500">
                <span class="text-lg">Num. Documento</span>
            </label>
        </div>
        <div>
            <label class="cursor-pointer space-x-1">
                <input type="checkbox" wire:model="producto"
                    class="cursor-pointer w-4 h-4 text-blue-600 accent-blue-500">
                <span class="text-lg">Producto</span>
            </label>
        </div>
        <div>
            <label class="cursor-pointer space-x-1">
                <input type="checkbox" wire:model="fecha_emision"
                    class="cursor-pointer w-4 h-4 text-blue-600 accent-blue-500">
                <span class="text-lg">Fecha Emision</span>
            </label>
        </div>
    </div>
</div>
