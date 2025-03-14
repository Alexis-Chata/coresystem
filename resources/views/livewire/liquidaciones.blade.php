<div class="text-xs sm:text-sm">
    @if ($view == 'liquidaciones')
        <div>
            <span>
                Liquidaciones
            </span>
            <input type="date" wire:model.live="fecha_fin"
                class="px-2 sm:px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring focus:ring-indigo-200 focus:border-indigo-500 transition-colors duration-200 ease-in-out">
        </div>
        <br />
        <table class="min-w-full border border-gray-300 shadow-md rounded-lg">
            <thead class="bg-gray-200 text-gray-700">
                <tr>
                    <th class="px-2 sm:px-4 py-2 text-left border-b">ID</th>
                    <th class="px-2 sm:px-4 py-2 text-left border-b">Fecha</th>
                    <th class="px-2 sm:px-4 py-2 text-left border-b">Conductor</th>
                    <th class="px-2 sm:px-4 py-2 text-left border-b">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-300">
                @forelse ($movimientos as $liquidacion)
                    <tr class="hover:bg-gray-100">
                        <td class="px-2 sm:px-4 py-2 border-b">{{ $liquidacion->id }}</td>
                        <td class="px-2 sm:px-4 py-2 border-b">
                            {{ carbon_parse($liquidacion->fecha_liquidacion)->format('d-m-Y') }}</td>
                        <td class="px-2 sm:px-4 py-2 border-b">{{ $liquidacion->conductor_id }} -
                            {{ $liquidacion->conductor->name }}</td>
                        <td class="px-2 sm:px-4 py-2 border-b">
                            @if ($liquidacion->estado == 'por liquidar')
                                <button wire:click="liquidar({{ $liquidacion->id }})"
                                    class="px-3 py-1 md:text-sm text-white bg-blue-600 rounded-md hover:bg-blue-700">Liquidar</button>
                            @else
                                <button wire:click="ver_liquidacion({{ $liquidacion->id }})"
                                    class="px-3 py-1 md:text-sm text-white bg-blue-600 rounded-md">Ver</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="100%" class="px-2 sm:px-4 py-2 text-center text-gray-500">No hay registros
                            disponibles</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @elseif ($view == 'liquidacion comprobantes')
        @if ($regresa)
            <div class="flex justify-between">
                <button wire:click="regresar"
                    class="px-3 py-1 md:text-sm text-white bg-blue-600 rounded-md">Volver</button>
                <button wire:click="guardar_anulados"
                    class="px-3 py-1 md:text-sm text-white bg-blue-600 rounded-md">Guardar Anulados</button>
            </div>
        @endif
        <br />
        <br />
        <div>
            <p>Fecha Liquidacion: {{ $movimientos->first()->fecha_liquidacion }}</p>
            <p>Conductor: {{ $movimientos->first()->conductor_id }} - {{ $movimientos->first()->conductor->name }}</p>
        </div>
        <br />
        <table class="min-w-full border border-gray-300 shadow-md rounded-lg">
            <thead class="bg-gray-200 text-gray-700">
                <tr>
                    <th class="px-2 sm:px-4 py-2 text-left border-b">Tipo Comp.</th>
                    <th class="px-2 sm:px-4 py-2 text-left border-b">Serie - Correlativo</th>
                    <th class="px-2 sm:px-4 py-2 text-left border-b">Cod - Cliente</th>
                    <th class="px-2 sm:px-4 py-2 text-left border-b">Imp.Venta</th>
                    <th class="px-2 sm:px-4 py-2 text-left border-b">Estado</th>
                    <th class="px-2 sm:px-4 py-2 text-left border-b">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-300">
                @forelse ($comprobantes as $comprobante)
                    <tr class="hover:bg-gray-100">
                        <td class="px-2 sm:px-4 py-2 border-b">{{ $comprobante->tipoDoc_name }}</td>
                        <td class="px-2 sm:px-4 py-2 border-b">{{ $comprobante->serie }} -
                            {{ $comprobante->correlativo }}
                        </td>
                        <td class="px-2 sm:px-4 py-2 border-b">{{ $comprobante->cliente_id }} -
                            {{ $comprobante->clientRazonSocial }}</td>
                        <td class="px-2 sm:px-4 py-2 border-b">{{ $comprobante->mtoImpVenta }}</td>
                        <td class="px-2 sm:px-4 py-2 border-b">{{ $comprobante->estado_reporte ? '' : 'Anulado' }}</td>
                        <td class="px-2 sm:px-4 py-2 border-b">
                            @if ($comprobante->estado_reporte)
                                <button wire:click="anular_cp({{ $comprobante->id }})"
                                    class="px-3 py-1 md:text-sm text-white bg-blue-600 rounded-md">Anular</button>
                            @else
                                <button wire:click="desanular_cp({{ $comprobante->id }})"
                                    class="px-3 py-1 md:text-sm text-white bg-blue-600 rounded-md">Desanular</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="100%" class="px-2 sm:px-4 py-2 text-center text-gray-500">No hay registros
                            disponibles</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @elseif ($view == 'liquidacion detalle')
        @if ($regresa)
            <button wire:click="regresar" class="px-3 py-1 md:text-sm text-white bg-blue-600 rounded-md">Volver</button>
            <br />
        @else
            <div class="flex justify-between">
                <div class="flex flex-wrap gap-1">
                    <button wire:click="volver"
                        class="px-3 py-1 md:text-sm text-white bg-blue-600 rounded-md">Volver</button>
                    <button wire:click="diferencias"
                        class="px-3 py-1 md:text-sm text-white bg-yellow-600 rounded-md">Diferencias</button>
                    <button wire:click="diferencias"
                        class="px-3 py-1 md:text-sm text-white bg-red-600 rounded-md">Add.Salida</button>
                    <button wire:click="agregar_ingreso"
                        class="px-3 py-1 md:text-sm text-white bg-green-600 rounded-md">Add.Ingreso</button>
                    <button wire:click="liquidacion_comprobantes"
                        class="px-3 py-1 md:text-sm text-white bg-gray-600 rounded-md">Comprobantes</button>
                </div>
                <button wire:click="liquidacion_comprobantes"
                    class="px-3 py-1 md:text-sm text-white bg-indigo-600 rounded-md">Grabar Liquidacion</button>
            </div>
        @endif
        <br />
        <div>
            <p>Fecha Liquidacion: {{ $movimientos->first()->fecha_liquidacion }}</p>
            <p>Conductor: {{ $movimientos->first()->conductor_id }} - {{ $movimientos->first()->conductor->name }}</p>
        </div>
        <br />
        <div class="w-full overflow-x-auto">
            <table class="w-full min-w-max border border-gray-300 shadow-md rounded-lg">
                <thead class="bg-gray-200 text-gray-700">
                    <tr>
                        <th class="px-2 sm:px-4 py-2 text-left border-b">Cod - Producto</th>
                        <th class="px-2 sm:px-4 py-2 text-left border-b">Diferencia</th>
                        <th class="px-2 sm:px-4 py-2 text-left border-b">Carga</th>
                        <th class="px-2 sm:px-4 py-2 text-left border-b">Venta</th>
                        <th class="px-2 sm:px-4 py-2 text-left border-b">Ingr.Dev</th>
                        <th class="px-2 sm:px-4 py-2 text-left border-b">Doc.vtas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-300">
                    @forelse ($productos as $producto)
                        <tr class="hover:bg-gray-100"
                            :class="{ 'bg-red-200': {{ $producto->diferencia_cajas == 0 ? 'false' : 'true' }} }">
                            <td class="px-2 sm:px-4 py-2 border-b">{{ $producto->id }} - {{ $producto->name }}</td>
                            <td class="px-2 sm:px-4 py-2 border-b">{{ $producto->diferencia_cajas }}</td>
                            <td class="px-2 sm:px-4 py-2 border-b">{{ $producto->movimiento_cantidad_cajas }}</td>
                            <td class="px-2 sm:px-4 py-2 border-b">{{ $producto->comprobantes_cantidad_cajas }}</td>
                            <td class="px-2 sm:px-4 py-2 border-b">{{ $producto->diferencia_cajas }}</td>
                            <td class="px-2 sm:px-4 py-2 border-b">{{ $producto->cantidad_comprobantes }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-2 sm:px-4 py-2 text-center text-gray-500">
                                No hay registros disponibles
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @elseif ($view == 'agregar ingreso')
        @if ($regresa)
            <div class="flex justify-between">
                <button wire:click="regresar"
                    class="px-3 py-1 md:text-sm text-white bg-blue-600 rounded-md">Volver</button>
                <button wire:click="guardar_anulados"
                    class="px-3 py-1 md:text-sm text-white bg-blue-600 rounded-md">Guardar Anulados</button>
            </div>
        @endif
        <!-- Tabla -->
        <div class="mt-8">
            <!-- Buscador de Productos -->
            <div class="relative">
                <input type="text" wire:model.live.debounce.300ms="search"
                    class="block p-2 w-full text-sm text-white bg-transparent rounded-lg border border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                    placeholder="Buscar por código o nombre del producto" />

                <!-- Resultados de búsqueda -->
                @if (!empty($search_productos))
                    <div class="absolute z-10 w-full mt-1 bg-white rounded-lg shadow-lg dark:bg-gray-700">
                        @foreach ($search_productos as $producto)
                            @php
                                $precio =
                                    $producto->listaPrecios->where('id', $this->lista_precio)->first()?->pivot
                                        ?->precio ?? 0;
                            @endphp
                            <div wire:click="agregarProducto({{ $producto->id }})"
                                class="px-4 py-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 hover:rounded-t-md">
                                <div class="text-sm text-gray-900 dark:text-white">
                                    {{ $producto->id }} - {{ $producto->name }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    Marca: {{ $producto->marca->name ?? 'N/A' }} |
                                    Precio: S/. {{ number_format($precio, 2) }} |
                                    Stock disp.:
                                    {{ number_format($producto->almacenProductos->sum('stock_disponible'), 2) }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
            <table class="w-full table-auto border-collapse bg-gray-700 rounded-lg overflow-hidden mt-2">
                <thead>
                    <tr class="bg-gray-500 text-white text-left text-sm">
                        <th class="p-3 border-b border-gray-600">CÓDIGO - PRODUCTO</th>
                        <th class="p-3 border-b border-gray-600">CANTIDAD</th>
                        <th class="p-3 border-b border-gray-600">COSTO</th>
                        <th class="p-3 border-b border-gray-600 justify-items-center"><x-svg_circle_menu /></th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @forelse ($detalles as $index => $detalle)
                        <tr>
                            <td class="p-3 border-b border-gray-600">{{ $detalle['producto_id'] }} -
                                {{ $detalle['producto_name'] }}</td>
                            <td class="p-3 border-b border-gray-600">
                                <input type="number" step="0.01"
                                    wire:model.lazy="detalles.{{ $index }}.cantidad"
                                    wire:change="ajustarCantidad({{ $index }})"
                                    class="w-20 px-2 py-1 text-sm border rounded text-right text-black" />
                            </td>
                            <td class="p-3 border-b border-gray-600">{{ $detalle['precio_venta_total'] }}</td>
                            <td class="p-3 border-b border-gray-600 grid justify-items-center">
                                <button wire:click="eliminarDetalle({{ $index }})"
                                    class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-md font-semibold shadow-md">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        width="20px" height="20px" stroke-width="1.7" stroke="currentColor"
                                        class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>

                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-3 border-b border-gray-600 text-center">No hay productos
                                registrados</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    @endif
</div>
