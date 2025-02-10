<div>
    <input type="date" wire:model="fecha_inicio"
        class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring focus:ring-indigo-200 focus:border-indigo-500 transition-colors duration-200 ease-in-out">
    <input type="date" wire:model="fecha_fin"
        class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring focus:ring-indigo-200 focus:border-indigo-500 transition-colors duration-200 ease-in-out">
    <select wire:model="marca_id"
        class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring focus:ring-indigo-200 focus:border-indigo-500 transition-colors duration-200 ease-in-out">
        <option value="">Selecciona una marca</option>
        @foreach ($marcas as $marca)
            <option value="{{ $marca->id }}">{{ $marca->id."-".$marca->name }}</option>
        @endforeach
    </select>

    <select wire:model="vendedor_id"
        class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring focus:ring-indigo-200 focus:border-indigo-500 transition-colors duration-200 ease-in-out">
        <option value="">Selecciona un vendedor</option>
        @foreach ($vendedores as $vendedor)
            <option value="{{ $vendedor->id }}">{{ $vendedor->id."-".$vendedor->name }}</option>
        @endforeach
    </select>

    <select wire:model="producto_id"
        class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring focus:ring-indigo-200 focus:border-indigo-500 transition-colors duration-200 ease-in-out">
        <option value="">Selecciona un producto</option>
        @foreach ($productos as $producto)
            <option value="{{ $producto->id }}">{{ $producto->id."-".$producto->name }}</option>
        @endforeach
    </select>

    <button wire:click="exportar_reporte"
        class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 focus:outline-none focus:ring focus:ring-indigo-200 focus:border-indigo-500 transition-colors duration-200 ease-in-out">
        Exportar
    </button>
    <div class="mb-2" wire:loading wire:target="exportar_reporte">
        Cargando...
    </div>
    <br />
    <br />
    <br />

    <div>
        <input type="checkbox" id="marcas_name" wire:model="marcas_name">
        <label for="marcas_name">Marca Nombre</label>
    </div>
    <div>
        <input type="checkbox" id="tipo_documento" wire:model="tipo_documento">
        <label for="tipo_documento">Tipo Documento</label>
    </div>
    <div>
        <input type="checkbox" id="conductor" wire:model="conductor">
        <label for="conductor">Conductor</label>
    </div>
    <div>
        <input type="checkbox" id="vendedor" wire:model="vendedor">
        <label for="vendedor">Vendedor</label>
    </div>
    <div>
        <input type="checkbox" id="cliente" wire:model="cliente">
        <label for="cliente">Cliente</label>
    </div>
    <div>
        <input type="checkbox" id="num_documento" wire:model="num_documento">
        <label for="num_documento">Num. Documento</label>
    </div>
    <div>
        <input type="checkbox" id="producto" wire:model="producto">
        <label for="producto">Producto</label>
    </div>
    <div>
        <input type="checkbox" id="fecha_emision" wire:model="fecha_emision">
        <label for="fecha_emision">Fecha Emision</label>
    </div>

</div>
