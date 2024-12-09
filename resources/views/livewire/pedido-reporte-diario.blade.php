<div class="cont_reporte">
    <style>
        main > :first-child{
            padding: calc(-1px + 2vw);
        }
        .cont_reporte {

            & summary{
                padding: calc(-1px + 1vw);
            }
            & :is(th, td) {
                padding: calc(2px + 0.5vw);
            }
            td input{
                width: clamp(35px, calc(34px + 4vw), 70px);
            }
        }
    </style>

    <!-- Cabecera del Reporte -->
    <header class="flex justify-between items-center">
        <h1 class="text-3xl font-bold text-gray-800 dark:text-white">
            Reporte Diario de Pedidos
        </h1>
        <input
            type="date"
            wire:model.live="fecha"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
        >
    </header>

    <!-- Contenido del Reporte -->
    <section>
        @forelse($pedidosPorVendedor as $vendedorId => $pedidosVendedor)
            <details class="mb-2 my-1 border rounded-lg dark:border-gray-700 overflow-hidden group">
                <summary class="bg-blue-50 dark:bg-blue-800 p-4 flex justify-between items-center cursor-pointer group-open:bg-blue-100 dark:group-open:bg-blue-700 transition-colors">
                    <div class="flex items-center gap-2">
                        <span class="flex-none rounded-full bg-blue-100 p-2 dark:bg-blue-900">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </span>
                        <span class="text-lg font-semibold text-gray-800 dark:text-white">
                            {{ $pedidosVendedor->first()->vendedor->name }}
                        </span>
                    </div>
                    <span class="px-3 py-1 text-sm font-medium text-blue-600 bg-blue-100 rounded-full dark:bg-blue-900 dark:text-blue-400">
                        Total Pedidos: {{ $pedidosVendedor->count() }}
                    </span>
                </summary>
                <div class="space-y-2 p-1 bg-blue-50 dark:bg-blue-800">
                    @foreach($pedidosVendedor->groupBy('ruta_id') as $rutaId => $pedidosRuta)
                        <details class="border rounded-lg dark:border-gray-700 overflow-hidden group">
                            <summary class="bg-green-100 dark:bg-green-800 p-3 flex items-center cursor-pointer group-open:bg-green-200 dark:group-open:bg-green-700 transition-colors">
                                <span class="flex-none rounded-full bg-green-100 p-1.5 mr-3 dark:bg-green-900">
                                    <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                                    </svg>
                                </span>
                                <span class="font-semibold text-gray-700 dark:text-gray-300">
                                    Ruta: {{ $pedidosRuta->first()->ruta->name }}
                                </span>
                                <span class="ml-auto px-3 py-1 text-sm font-medium text-green-600 bg-green-100 rounded-full dark:bg-green-900 dark:text-green-400" style="margin-left:auto">
                                    Monto: S/. {{ number_format($pedidosRuta->sum('importe_total'), 2) }}
                                </span>
                            </summary>
                            <div class="p-1 bg-green-100 dark:bg-green-800">
                                @foreach($pedidosRuta->groupBy('cliente_id') as $clienteId => $pedidosCliente)
                                    <details class="mb-1 border rounded-lg dark:border-gray-700 overflow-hidden group">
                                        <summary class="bg-purple-100 dark:bg-purple-800 p-3 flex items-center cursor-pointer group-open:bg-purple-200 dark:group-open:bg-purple-700 transition-colors">
                                            <span class="flex-none rounded-full bg-purple-100 p-1.5 mr-3 dark:bg-purple-900">
                                                <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                                </svg>
                                            </span>
                                            <div>
                                                <span class="text-gray-800 dark:text-gray-300 font-medium">
                                                    Cliente: {{ $pedidosCliente->first()->cliente->razon_social }}
                                                </span>
                                                <span class="block text-sm text-gray-600 dark:text-gray-400">
                                                    {{ $pedidosCliente->first()->cliente->direccion }}
                                                </span>
                                            </div>
                                            <span class="ml-auto px-3 py-1 text-sm font-medium text-purple-600 bg-purple-100 rounded-full dark:bg-purple-900 dark:text-purple-400" style="margin-left:auto">
                                                Total: S/. {{ number_format($pedidosCliente->sum('importe_total'), 2) }} |
                                                    Lista: {{ $pedidosCliente->first()->listaPrecio->name ?? 'Sin lista' }}
                                            </span>
                                        </summary>
                                        <div class="bg-purple-50 dark:bg-purple-800">
                                            <div class="overflow-x-auto">
                                                <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                                                    <thead class="bg-gray-100 dark:bg-gray-700 dark:text-white">
                                                        <tr>
                                                            <th class="px-6 py-3">Producto</th>
                                                            <th class="px-6 py-3">Cantidad</th>
                                                            <th class="px-6 py-3">Precio</th>
                                                            <th class="px-6 py-3">Importe</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700 dark:text-gray-400">
                                                        @foreach($pedidosCliente->first()->pedidoDetalles as $detalle)
                                                            <tr>
                                                                <td class="px-6 py-4">{{ $detalle->producto_name }}</td>
                                                                <td class="px-6 py-4">{{ $detalle->cantidad }}</td>
                                                                <td class="px-6 py-4">S/. {{ number_format($detalle->producto_precio, 2) }}</td>
                                                                <td class="px-6 py-4">S/. {{ number_format($detalle->importe, 2) }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                    <tfoot class="bg-gray-100 dark:bg-gray-700">
                                                        <tr>
                                                            <td colspan="3" class="px-6 py-4 text-right font-medium text-gray-800 dark:text-white">
                                                                Total Pedido:
                                                            </td>
                                                            <td class="px-6 py-4 text-gray-800 dark:text-white">
                                                                S/. {{ number_format($pedidosCliente->first()->importe_total, 2) }}
                                                            </td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        </div>
                                    </details>
                                @endforeach
                            </div>
                        </details>
                    @endforeach
                </div>
            </details>
        @empty
            <div class="text-center py-12 bg-gray-50 dark:bg-gray-800 rounded-lg">
                <p class="text-gray-500 dark:text-gray-400">
                    No hay pedidos registrados para esta fecha
                </p>
            </div>
        @endforelse
    </section>
</div>
