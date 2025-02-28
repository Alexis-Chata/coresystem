<div>
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-2xl text-title-md2 font-bold text-black dark:text-white">
            Registrar de Movimiento
        </h2>
    </div>
    <form wire:submit="guardarMovimiento">
        <div class="text-white">
            <div class="mx-auto bg-gray-800 p-4 sm:p-6 rounded-lg shadow-lg">
                <!-- Encabezado -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                    <!-- Almacén -->
                    <div>
                        <label class="block text-sm font-medium mb-2">Almacén</label>
                        <select wire:model="almacen_id" required
                            class="w-full px-4 py-2 bg-gray-700 text-white rounded-md border border-gray-600 focus:outline-none focus:ring focus:ring-blue-500">
                            <option>Elegir Almacén</option>
                            @foreach ($almacenes as $almacen)
                                <option value="{{ $almacen->id }}">{{ $almacen->name }}</option>
                            @endforeach
                        </select>
                        @error('almacen_id')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                    <!-- Fecha -->
                    <div>
                        <label class="block text-sm font-medium mb-2">Fecha Movimiento</label>
                        <input type="date" wire:model="fecha_movimiento" required
                            class="w-full px-4 py-2 bg-gray-700 text-white rounded-md border border-gray-600 focus:outline-none focus:ring focus:ring-blue-500">
                        @error('fecha_movimiento')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                    <!-- Tipo de Movimiento -->
                    <div>
                        <label class="block text-sm font-medium mb-2">Tipo de Movimiento</label>
                        <select wire:model.live="tipo_movimiento_id" required
                            class="w-full px-4 py-2 bg-gray-700 text-white rounded-md border border-gray-600 focus:outline-none focus:ring focus:ring-blue-500">
                            <option>Elegir Tipo de Movimiento</option>
                            @foreach ($tipoMovimientos as $tipoMovimiento)
                                <option value="{{ $tipoMovimiento->id }}">{{ $tipoMovimiento->codigo }} -
                                    {{ $tipoMovimiento->name }}</option>
                            @endforeach
                        </select>
                        @error('tipo_movimiento_id')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                    @if ($datos_liquidacion)
                        <!-- Conductor -->
                        <div>
                            <label class="block text-sm font-medium mb-2">Conductor</label>
                            <select required wire:model="conductor_id"
                                class="w-full px-4 py-2 bg-gray-700 text-white rounded-md border border-gray-600 focus:outline-none focus:ring focus:ring-blue-500">
                                <option value="">Elegir Conductor</option>
                                @foreach ($conductores as $conductor)
                                    <option value="{{ $conductor->id }}">{{ $conductor->id }} - {{ $conductor->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('conductor_id')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                        <!-- Vehículo -->
                        <div>
                            <label class="block text-sm font-medium mb-2">Vehículo</label>
                            <select required wire:model="vehiculo_id"
                                class="w-full px-4 py-2 bg-gray-700 text-white rounded-md border border-gray-600 focus:outline-none focus:ring focus:ring-blue-500">
                                <option value="">Elegir Vehículo</option>
                                @foreach ($vehiculos as $vehiculo)
                                    <option value="{{ $vehiculo->id }}">{{ $vehiculo->id }} - {{ $vehiculo->marca }}
                                        -
                                        {{ $vehiculo->placa }}</option>
                                @endforeach
                            </select>
                            @error('vehiculo_id')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                        <!-- Fecha de liquidación -->
                        <div>
                            <label class="block text-sm font-medium mb-2">Fecha de liquidación</label>
                            <input type="date" required wire:model="fecha_liquidacion"
                                class="w-full px-4 py-2 bg-gray-700 text-white rounded-md border border-gray-600 focus:outline-none focus:ring focus:ring-blue-500">
                            @error('fecha_liquidacion')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                    @endif
                </div>
                <!-- Tabla -->
                <div class="mt-8">
                    @if ($tipo_movimiento_id)
                        <!-- Buscador de Productos -->
                        <div class="relative">
                            <input type="text" wire:model.live.debounce.300ms="search"
                                class="block p-2 w-full text-sm text-white bg-transparent rounded-lg border border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                                placeholder="Buscar por código o nombre del producto" />

                            <!-- Resultados de búsqueda -->
                            @if (!empty($productos))
                                <div class="absolute z-10 w-full mt-1 bg-white rounded-lg shadow-lg dark:bg-gray-700">
                                    @foreach ($productos as $producto)
                                        @php
                                            $precio =
                                                $producto->listaPrecios->where('id', $this->lista_precio)->first()
                                                    ?->pivot?->precio ?? 0;
                                        @endphp
                                        <div wire:click="agregarProducto({{ $producto->id }})"
                                            class="px-4 py-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 hover:rounded-t-md">
                                            <div class="text-sm text-gray-900 dark:text-white">
                                                {{ $producto->id }} - {{ $producto->name }}
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                Marca: {{ $producto->marca->name ?? 'N/A' }} |
                                                Precio: S/. {{ number_format($precio, 2) }} |
                                                Stock disp.: {{ number_format($producto->almacenProductos->sum("stock_disponible"), 2) }}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif
                    <table class="w-full table-auto border-collapse bg-gray-700 rounded-lg overflow-hidden mt-2">
                        <thead>
                            <tr class="bg-gray-500 text-white text-left text-sm">
                                <th class="p-3 border-b border-gray-600">CÓDIGO - PRODUCTO</th>
                                <th class="p-3 border-b border-gray-600">CANTIDAD</th>
                                <th class="p-3 border-b border-gray-600">COSTO</th>
                                <th class="p-3 border-b border-gray-600 justify-items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.7" stroke="currentColor" class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>

                                </th>
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

                <!-- Comentarios -->
                <div class="mt-6">
                    <label class="block text-sm font-medium mb-2">Comentarios Adicionales</label>
                    <textarea
                        class="w-full px-4 py-2 bg-gray-700 text-white rounded-md border border-gray-600 focus:outline-none focus:ring focus:ring-blue-500"
                        rows="3" placeholder="Escribe tus comentarios aquí..."></textarea>
                </div>

                <!-- Totales -->
                <div class="mt-6 flex justify-between items-center">
                    <div class="text-sm">
                    </div>
                    <button
                        class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md font-semibold shadow-md">
                        Guardar
                    </button>
                </div>
                <div class="mt-6 flex justify-between items-center">
                    <div class="text-sm">
                    </div>
                    @error('detalles')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>
    </form>
</div>
