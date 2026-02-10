<div class="w-full p-4 md:p-6 space-y-4">

    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <h1 class="text-xl md:text-2xl font-semibold text-gray-900">
            Avance por ítems vendidos
        </h1>
    </div>

    {{-- Filtros --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Desde</label>
                <input type="date"
                    class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                    wire:model.live="desde">
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Hasta</label>
                <input type="date"
                    class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                    wire:model.live="hasta">
            </div>

            @can('admin avance')
                <div class="md:col-span-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Vendedor</label>
                    <select class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                        wire:model.live="vendedorId">
                        <option value="">Todos</option>
                        @foreach ($vendedores as $v)
                            <option value="{{ $v->id }}">{{ $v->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endcan

            <div class="md:col-span-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">Buscar item</label>
                <input type="text" placeholder="Código o descripción..."
                    class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                    wire:model.live.debounce.400ms="buscarItem">
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Por página</label>
                <select class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                    wire:model.live="porPagina">
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Resumen --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <div class="text-xs uppercase tracking-wide text-gray-500">Ventas</div>
            <div class="mt-1 text-2xl font-bold text-gray-900">
                {{ number_format((float) ($totales->ventas ?? 0), 2) }}
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <div class="text-xs uppercase tracking-wide text-gray-500">Clientes únicos</div>
            <div class="mt-1 text-2xl font-bold text-gray-900">
                {{ number_format((float) ($totales->clientes_unicos ?? 0), 0) }}
            </div>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden" wire:loading.class='hidden'>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th
                            class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-[120px]">
                            Código
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Descripción
                        </th>
                        <th
                            class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider w-[160px]">
                            Ventas
                        </th>
                        <th
                            class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider w-[160px]">
                            Clientes
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($items as $it)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-500">
                                {{ $it->codProducto ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900">
                                {{ $it->descripcion ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900 text-right font-semibold">
                                {{ 'S/. ' . number_format((float) $it->ventas, 2) }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700 text-right">
                                {{ number_format((float) $it->clientes_unicos, 0) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-10 text-center text-sm text-gray-500">
                                No hay resultados con los filtros seleccionados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        <div class="p-4 border-t border-gray-200">
            {{ $items->onEachSide(1)->links() }}
        </div>
    </div>
    <div class="p-4 border-t border-gray-200 flex items-center justify-between gap-3">
        <div wire:loading.delay
            class="flex items-center gap-2 text-gray-600">
            <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                    stroke-width="4" fill="none"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
            </svg>
            <span class="text-xs">Cargando...</span>
        </div>
    </div>

</div>
