<div>
    <div id="data-table">
        <input type="date" wire:model="fecha_emision" wire:change="actualizar_table"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring focus:ring-indigo-200 focus:border-indigo-500 transition-colors duration-200 ease-in-out">
        <input type="text" id="serach" wire:model.live.debounce.450ms="search"
            class="w-64 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring focus:ring-indigo-200 focus:border-indigo-500 transition-colors duration-200 ease-in-out"
            placeholder="buscar cod_cliente o correlativo">
        <select wire:model="estado_envio" wire:change="actualizar_table"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring focus:ring-indigo-200 focus:border-indigo-500 transition-colors duration-200 ease-in-out">
            <option value="">todos</option>
            <option value="pendiente">pendiente</option>
            <option value="aceptado">aceptado</option>
            <option value="rechazado">rechazado</option>
        </select>
        <select wire:model="tipo_comprobante" wire:change="actualizar_table"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring focus:ring-indigo-200 focus:border-indigo-500 transition-colors duration-200 ease-in-out">
            <option value="">todos</option>
            <option value="01">factura</option>
            <option value="03">boleta</option>
            <option value="07">nota credito</option>
            <option value="00">nota_pedido</option>
        </select>
        <button class="bg-blue-200 py-2 px-3 rounded"><a href="{{ route('comprobantes.invoice.nota') }}">Nota de Credito</a></button>
    </div>
    <div wire:loading wire:target="actualizar_table, search">
        Cargando...
    </div>
</div>
