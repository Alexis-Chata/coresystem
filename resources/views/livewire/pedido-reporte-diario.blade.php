<div class="cont_reporte">
    <style>
        main > :first-child{
            padding: calc(-1px + 2vw);
        }
        .cont_reporte section{

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
        <h1 class="text-xl font-bold text-gray-800 sm:text-2xl dark:text-white">
            Reporte Diario de Pedidos
        </h1>
        <input
            type="date"
            wire:loading.attr="disabled"
            wire:model.live="fecha"
            class="px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
        >
        <div wire:loading wire:target="fecha">
            Cargando...
        </div>
    </header>

    <!-- Contenido del Reporte -->
    <section class="mb-5.5" wire:loading.class="hidden" wire:target="fecha">
        @forelse($pedidosPorVendedor as $vendedorId => $pedidosVendedor)
            <details class="overflow-hidden my-1 mb-2 rounded-lg border dark:border-gray-700 group">
                <summary class="flex justify-between items-center p-4 bg-blue-50 transition-colors cursor-pointer dark:bg-blue-800 group-open:bg-blue-100 dark:group-open:bg-blue-700">
                    <div class="flex gap-2 items-center">
                        <span class="flex-none p-2 bg-blue-100 rounded-full dark:bg-blue-900">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </span>
                        <span class="text-lg font-semibold text-gray-800 dark:text-white">
                            #{{ $pedidosVendedor->first()->vendedor->id }} - {{ $pedidosVendedor->first()->vendedor->name }}
                        </span>
                    </div>
                    <span class="px-3 py-1 text-sm font-medium text-blue-600 bg-blue-100 rounded-full dark:bg-blue-900 dark:text-blue-400">
                        Total Pedidos: {{ $pedidosVendedor->count() }}
                    </span>
                </summary>
                <div class="p-1 space-y-2 bg-blue-50 dark:bg-blue-800">
                    @foreach($pedidosVendedor->groupBy('ruta_id') as $rutaId => $pedidosRuta)
                        <details class="overflow-hidden rounded-lg border dark:border-gray-700 group">
                            <summary class="flex items-center p-3 bg-green-100 transition-colors cursor-pointer dark:bg-green-800 group-open:bg-green-200 dark:group-open:bg-green-700">
                                <span class="flex-none p-1.5 mr-3 bg-green-100 rounded-full dark:bg-green-900">
                                    <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                                    </svg>
                                </span>
                                <span class="font-semibold text-gray-700 dark:text-gray-300">
                                    #{{ $pedidosRuta->first()->ruta->id }} - {{ $pedidosRuta->first()->ruta->name }}
                                </span>
                                <span class="px-3 py-1 ml-auto text-sm font-medium text-green-600 bg-green-100 rounded-full dark:bg-green-900 dark:text-green-400" style="margin-left:auto">
                                    Monto: S/. {{ number_format($pedidosRuta->sum('importe_total'), 2) }}
                                </span>
                            </summary>
                            <div class="p-1 bg-green-100 dark:bg-green-800">
                                @foreach($pedidosRuta as $pedido)
                                    <details class="overflow-hidden mb-1 rounded-lg border dark:border-gray-700 group">
                                        <summary class="flex items-center p-3 bg-purple-100 transition-colors cursor-pointer dark:bg-purple-800 group-open:bg-purple-200 dark:group-open:bg-purple-700">
                                            <span class="flex-none p-1.5 mr-3 bg-purple-100 rounded-full dark:bg-purple-900">
                                                <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                                </svg>
                                            </span>
                                            <div>
                                                <span class="font-medium text-gray-800 dark:text-gray-300">
                                                    #{{ $pedido->cliente->id }} - {{ $pedido->cliente->razon_social }}
                                                </span>
                                                <span class="block text-sm text-gray-600 dark:text-gray-400">
                                                    {{ $pedido->cliente->direccion }}
                                                </span>
                                            </div>
                                            <span class="px-3 py-1 ml-auto text-sm font-medium text-purple-600 bg-purple-100 rounded-full dark:bg-purple-900 dark:text-purple-400" style="margin-left:auto">
                                                Total: S/. {{ number_format($pedido->importe_total, 2) }} |
                                                Lista: {{ $pedido->listaPrecio->name ?? 'Sin lista' }}
                                            </span>
                                            @if($pedido->estado === 'pendiente')
                                            <button
                                                wire:loading.attr="disabled"
                                                wire:click="editarPedido({{ $pedido->id }})"
                                                class="p-1.5 text-blue-600 bg-blue-100 rounded-full hover:bg-blue-200"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                </svg>
                                            </button>
                                            @endif
                                            <div wire:loading wire:target="editarPedido">
                                                Cargando...
                                            </div>
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
                                                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700 dark:text-gray-400">
                                                        @foreach($pedido->pedidoDetalles as $detalle)
                                                            <tr>
                                                                <td class="px-6 py-4">#{{ $detalle->producto_id }} - {{ $detalle->producto_name }}</td>
                                                                <td class="px-6 py-4">{{ number_format($detalle->cantidad, 2) }}</td>
                                                                <td class="px-6 py-4">S/. {{ number_format($detalle->producto_precio, 2) }}</td>
                                                                <td class="px-6 py-4">S/. {{ number_format($detalle->importe, 2) }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                    <tfoot class="bg-gray-100 dark:bg-gray-700">
                                                        <tr>
                                                            <td colspan="2" class="px-6 py-4">
                                                                @if($pedido->comentario)
                                                                    <div class="text-sm text-gray-600 dark:text-gray-400">
                                                                        <span class="font-medium">Nota:</span>
                                                                        {{ $pedido->comentario }}
                                                                    </div>
                                                                @endif
                                                            </td>
                                                            <td class="px-6 py-4 font-medium text-right text-gray-800 dark:text-white">
                                                                Total Pedido:
                                                            </td>
                                                            <td class="px-6 py-4 text-gray-800 dark:text-white">
                                                                S/. {{ number_format($pedido->importe_total, 2) }}
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
            <div class="py-12 text-center bg-gray-50 rounded-lg dark:bg-gray-800">
                <p class="text-gray-500 dark:text-gray-400">
                    No hay pedidos registrados para esta fecha
                </p>
            </div>
        @endforelse
    </section>
<div x-data="{ open: false }"
     x-show="open"
     @open-modal.window="open = true"
     @close-modal.window="open = false"
     class="overflow-y-auto fixed inset-0 z-50 edit_modal"
     style="display: none;">
         <style>
             .edit_modal {
                 z-index: 9999999 !important;
                 &>div{
                     padding: calc(0px + 0.5vw);
                     @media (width < 550px){
                         padding:0;
                     }
                 }
                 .modal_pad{
                     padding: 0 calc(0px + 0.5vw);
                     @media (width < 550px){
                         border-radius:0;
                     }
                 }
                 & :is(th, td) {
                     padding: calc(2px + 0.5vw);
                 }
                 td input{
                     width: clamp(35px, calc(34px + 4vw), 70px);
                 }
                 thead th:last-child{
                     display: flex;
                     justify-content: space-around;
                     align-items: center;
                 }

                 tbody td:last-child{
                     text-align: center;
                     display: flex;
                     justify-content: space-around;
                     align-items: center;
                 }
             }
         </style>
    <div class="flex justify-center items-center min-h-screen">
        <!-- Overlay -->
        <div class="fixed inset-0 bg-black opacity-50"></div>

        <!-- Modal -->
        <div class="relative w-full max-w-6xl bg-white rounded-lg shadow-xl modal_pad dark:bg-gray-800 sm:px-6">
            <!-- Modal Header -->
            <div class="flex justify-between items-center py-4 border-b dark:border-gray-700">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                    Editar Pedido
                </h3>
                <button @click="open = false" class="text-gray-400 hover:text-gray-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Modal Content -->
            <div class="py-6">
                @if($pedidoEnEdicion)
                    <!-- Información básica -->
                    <div class="grid gap-6 mb-4 md:grid-cols-2">
                        <!-- Cliente -->
                        <div class="relative">
                            <input
                                type="text"
                                class="block px-2.5 pt-4 pb-2.5 w-full text-sm text-gray-900 bg-transparent rounded-lg border border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                                value="#{{ $pedidoEnEdicion->cliente->id }} - {{ $pedidoEnEdicion->cliente->razon_social }}"
                                disabled
                            />
                            <label
                                class="absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-4 scale-8 top-2 z-10 origin-[0] bg-[#f1f5f9] dark:bg-[#1A222C] px-2 peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-8 peer-focus:-translate-y-4 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto start-1"
                            >
                                Cliente
                            </label>
                        </div>
                        <!-- Fecha -->
                        <div class="relative">
                            <input
                                type="text"
                                class="block px-2.5 pt-4 pb-2.5 w-full text-sm text-gray-900 bg-transparent rounded-lg border border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                                value="{{ $pedidoEnEdicion->fecha_emision }}"
                                disabled
                            />
                            <label
                                class="absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-4 scale-8 top-2 z-10 origin-[0] bg-[#f1f5f9] dark:bg-[#1A222C] px-2 peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-8 peer-focus:-translate-y-4 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto start-1"
                            >
                                Fecha
                            </label>
                        </div>
                    </div>
                    <!-- Buscador de Productos -->
                    <div wire:loading wire:target="search">
                        Buscando...
                    </div>
                            <div class="relative mb-4" wire:loading.class="hidden" wire:target="guardarCambios, eliminarPedido, actualizarCantidadDetalle, eliminarDetalle, agregarProducto">
                                <input
                                    type="text"
                                    wire:model.live.debounce.300ms="search"
                                    class="block px-2.5 pt-4 pb-2.5 w-full text-sm text-gray-900 bg-transparent rounded-lg border border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                                    placeholder=" "
                                />
                                <label class="pointer-events-none absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-4 scale-8 top-2 z-10 origin-[0] bg-white dark:bg-gray-800 px-2 peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-8 peer-focus:-translate-y-4 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto start-1">
                                    Buscar por código o nombre del producto
                                </label>

                                <!-- Resultados de búsqueda -->
                                @if(strlen($search) > 0)
                                    <div class="absolute z-20 mt-1 w-full bg-white rounded-lg shadow-lg dark:bg-gray-700">
                                        @if($productos && count($productos) > 0)
                                            @foreach($productos as $producto)
                                                @php
                                                    $precio = $producto->listaPrecios->first()?->pivot?->precio ?? 0;
                                                @endphp
                                                <div
                                                    wire:click="agregarProducto({{ $producto->id }})"
                                                    class="px-4 py-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600"
                                                >
                                                    <div class="text-sm text-gray-900 dark:text-white">
                                                        {{ $producto->id }} - {{ $producto->name }}
                                                    </div>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                                        Marca: {{ $producto->marca->name ?? 'N/A' }} |
                                                        Precio: S/. {{ number_format($precio, 2) }} |
                                                        Cantidad: {{ $producto->cantidad }}
                                                    </div>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                                No se encontraron productos que coincidan con la búsqueda
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>

                    <!-- Tabla de productos -->
                    <table class="mt-4 w-full" wire:loading.class="hidden" wire:target="guardarCambios, eliminarPedido, actualizarCantidadDetalle, eliminarDetalle, agregarProducto">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th scope="col">Código - Producto</th>
                                <th scope="col">Cantidad</th>
                                <th scope="col">Importe <svg width="25" height="25" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M10 3h3v1h-1v9l-1 1H4l-1-1V4H2V3h3V2a1 1 0 0 1 1-1h3a1 1 0 0 1 1 1v1zM9 2H6v1h3V2zM4 13h7V4H4v9zm2-8H5v7h1V5zm1 0h1v7H7V5zm2 0h1v7H9V5z" fill="currentColor"></path></svg></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pedidoEnEdicion->pedidoDetalles as $detalle)
                                <tr>
                                    <td>#{{ $detalle->producto_id }} - {{ $detalle->producto_name }}</td>
                                    <td>
                                            @php
                                                $producto = App\Models\Producto::withTrashed()->find($detalle->producto_id);
                                                $esPaqueteUnico = $producto->cantidad == 1;
                                            @endphp

                                            @if($esPaqueteUnico)
                                                <input
                                                    type="number"
                                                    min="1"
                                                    value="{{ $detalle->cantidad }}"
                                                    wire:model.lazy="detallesEdit.{{ $detalle->id }}.cantidad"
                                                    wire:change="actualizarCantidadDetalle({{ $detalle->id }}, $event.target.value)"
                                                    class="px-2 py-1 w-20 text-sm rounded border"
                                                />
                                            @else
                                                <input
                                                    type="number"
                                                    min="0.01"
                                                    step="0.01"
                                                    value="{{ $detalle->cantidad }}"
                                                    wire:model.lazy="detallesEdit.{{ $detalle->id }}.cantidad"
                                                    wire:change="actualizarCantidadDetalle({{ $detalle->id }}, $event.target.value)"
                                                    class="px-2 py-1 w-20 text-sm rounded border"
                                                />
                                            @endif
                                        </td>
                                    <td>S/. {{ number_format($detallesEdit[$detalle->id]['importe'] ?? $detalle->importe, 2) }}
                                        <button
                                            wire:click="eliminarDetalle({{ $detalle->id }})"
                                            wire:confirm="¿Estás seguro de eliminar este producto?"
                                            class="text-red-600 hover:text-red-800"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <!-- Pie de la tabla con totales y comentarios -->
                        <tfoot>
                            <tr class="font-semibold text-gray-900 dark:text-white">
                                <td class="px-6 py-6" colspan="2" rowspan="3">
                                    <!-- Textarea para comentarios -->
                                    <div class="relative">
                                        <textarea
                                            rows="4"
                                            id="comentarios_edit"
                                            wire:model="comentarios"
                                            class="block p-2 pt-4 w-full text-sm text-gray-900 bg-transparent rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                            placeholder="Escribe tus comentarios aquí..."
                                        ></textarea>
                                        <label
                                            for="comentarios_edit"
                                            class="absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-4 scale-8 top-2 z-1 origin-[0] bg-[#f1f5f9] dark:bg-gray-800 px-2 peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 start-1 dark:bg-gradient-to-b from-[#1a222c] to-[#1f2937]"
                                        >
                                            Comentarios Adicionales
                                        </label>
                                    </div>
                                </td>
                                <td class="px-6 py-3 text-right">Subtotal: S/. {{ number_format($totales['valorVenta'], 2) }}</td>
                            </tr>
                            <tr class="font-semibold text-gray-900 dark:text-white">
                                <td class="px-6 py-3 text-right">IGV (18%): S/. {{ number_format($totales['totalImpuestos'], 2) }}</td>
                            </tr>
                            <tr class="font-semibold text-gray-900 dark:text-white">
                                <td class="px-6 py-3 text-right">Total: S/. {{ number_format($totales['subTotal'], 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                @endif
                <div wire:loading wire:target="guardarCambios, eliminarPedido, actualizarCantidadDetalle, eliminarDetalle, agregarProducto">
                    Cargando...
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex gap-3 justify-end py-4 border-t dark:border-gray-700" wire:loading.class="hidden" wire:target="guardarCambios, eliminarPedido, actualizarCantidadDetalle, eliminarDetalle, agregarProducto">
                @if($pedidoEnEdicion)
                    <button
                        wire:click="eliminarPedido"
                        wire:confirm="¿Está seguro de eliminar este pedido? Esta acción no se puede deshacer."
                        class="px-4 py-2 text-white bg-red-600 rounded hover:bg-red-700"
                    >
                        Eliminar Pedido
                    </button>
                    <button
                        wire:click="guardarCambios"
                        class="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700"
                    >
                        Guardar Cambios
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>
</div>
