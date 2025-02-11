<div>
    <div id="data-table">
        <input type="date" wire:model="fecha_emision" wire:change="actualizar_table"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring focus:ring-indigo-200 focus:border-indigo-500 transition-colors duration-200 ease-in-out">
        <input type="text" id="serach" wire:model.live.debounce.450ms="search"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring focus:ring-indigo-200 focus:border-indigo-500 transition-colors duration-200 ease-in-out"
            placeholder="buscar cod_cliente o correlativo">
        <select wire:model="estado_envio" wire:change="actualizar_table"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring focus:ring-indigo-200 focus:border-indigo-500 transition-colors duration-200 ease-in-out">
            <option value="">todos</option>
            <option value="pendientes">pendientes</option>
            <option value="enviados">enviados</option>
        </select>
        <select wire:model="tipo_comprobante" wire:change="actualizar_table"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring focus:ring-indigo-200 focus:border-indigo-500 transition-colors duration-200 ease-in-out">
            <option value="">todos</option>
            <option value="01">factura</option>
            <option value="03">boleta</option>
            <option value="00">nota_pedido</option>
        </select>
    </div>
    <div wire:loading wire:target="actualizar_table, search">
        Cargando...
    </div>
</div>
