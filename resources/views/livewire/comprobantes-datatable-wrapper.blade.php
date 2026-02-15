<div>
    {{-- HTML antes --}}
    <div wire:loading.class="opacity-50 pointer-events-none" class="mb-8">
        <div class="space-y-2">
            <input type="date" wire:model.live="fecha_emision"
                class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring focus:ring-indigo-200 focus:border-indigo-500 transition-colors duration-200 ease-in-out">
            <input type="date" wire:model.live="fecha_emision_fin"
                class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring focus:ring-indigo-200 focus:border-indigo-500 transition-colors duration-200 ease-in-out">
            <input type="text" id="serach" wire:model.live.debounce.450ms="buscar_search"
                class="w-64 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring focus:ring-indigo-200 focus:border-indigo-500 transition-colors duration-200 ease-in-out"
                placeholder="buscar cod_cliente o correlativo">
            <select wire:model.live="estado_envio"
                class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring focus:ring-indigo-200 focus:border-indigo-500 transition-colors duration-200 ease-in-out">
                <option value="">todos</option>
                <option value="pendiente">pendiente</option>
                <option value="aceptado">aceptado</option>
                <option value="rechazado">rechazado</option>
            </select>
            <select wire:model.live="tipo_comprobante"
                class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring focus:ring-indigo-200 focus:border-indigo-500 transition-colors duration-200 ease-in-out">
                <option value="">todos</option>
                <option value="01">factura</option>
                <option value="03">boleta</option>
                <option value="07">nota credito</option>
                <option value="00">nota_pedido</option>
            </select>
            <button class="bg-blue-200 py-2 px-3 rounded" wire:click="descargar_comprobantes">Descargar</button>
            {{-- <button class="bg-blue-200 py-2 px-3 rounded"><a href="{{ route('comprobantes.invoice.nota') }}">Nota de
                    Credito</a></button> --}}
        </div>
    </div>

    {{-- Render oficial del datatable --}}
    <div wire:loading.class="hidden opacity-50 pointer-events-none">
        @include('livewire-tables::datatable')
    </div>

    {{-- HTML después --}}
    <div wire:loading.delay
        class="h-16 w-16 animate-spin rounded-full border-4 border-solid border-primary border-t-transparent m-1">
    </div>
</div>
