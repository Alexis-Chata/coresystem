<div>
    <div class="grid md:grid-cols-2 gap-6">
        <!-- Columna Izquierda -->
        <div class="space-y-4">
            <!-- Fecha -->
            <div class="relative">
                <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                    <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true"
                        xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
                    </svg>
                </div>
                <input type="text"
                    class="block ps-10 px-2.5 pb-2.5 pt-4 w-full text-sm text-gray-900 bg-transparent rounded-lg border border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                    value="{{ $fecha_emision }}" disabled />
                <label
                    class="absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-4 scale-8 top-2 z-10 origin-[0] bg-[#f1f5f9] dark:bg-[#1A222C] px-2 peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-8 peer-focus:-translate-y-4 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto start-1">
                    Fecha
                </label>
            </div>

            <!-- Vendedor -->
            <div class="relative">
                @if ($user->hasRole('admin'))
                    <x-floating-searchable-select :options="$vendedores" :wire-model="'vendedor_id'" :placeholder="'Vendedor'" />
                @else
                    <input type="text" autocomplete="off"
                        class="block px-2.5 pb-2.5 pt-4 w-full text-sm text-gray-900 bg-transparent rounded-lg border border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                        value="{{ $empleado->name }}" disabled />
                    <label
                        class="absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-4 scale-8 top-2 z-10 origin-[0] bg-[#f1f5f9] dark:bg-[#1A222C] px-2 peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-8 peer-focus:-translate-y-4 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto start-1">
                        Vendedor
                    </label>
                @endif
            </div>

            <!-- Cliente -->
            <div class="relative">
                <livewire:cliente-select :vendedor_id="$vendedor_id" :key="'cliente-select-' . $vendedor_id" wire:model="cliente_id" />
            </div>

            <!-- Tipo de Comprobante -->
            <div class="relative">
                <select wire:model.live="f_tipo_comprobante_id"
                    class="block px-2.5 pb-2.5 pt-4 w-full text-sm text-gray-900 bg-transparent rounded-lg border border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                    @disabled(str_starts_with($documento, 'RUC'))>
                    <option value="">Selecciona un Tipo de Comprobante</option>
                    @foreach ($tipoComprobantes as $tipo)
                        @if (str_starts_with($documento, 'RUC') && $tipo->id == 2)
                            <option value="{{ $tipo->id }}" @selected($tipo->id == $f_tipo_comprobante_id)>{{ $tipo->name }}</option>
                        @elseif (!str_starts_with($documento, 'RUC') && $tipo->id != 2)
                            <option value="{{ $tipo->id }}" @selected($tipo->id == $f_tipo_comprobante_id)>{{ $tipo->name }}</option>
                        @endif
                    @endforeach
                </select>
                <label
                    class="absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-4 scale-8 top-2 z-10 origin-[0] bg-[#f1f5f9] dark:bg-[#1A222C] px-2 peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-8 peer-focus:-translate-y-4 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto start-1">
                    Tipo de Comprobante
                </label>
                <svg class="-translate-y-1/2 absolute dark:text-gray-500 h-5 pointer-events-none right-3 text-gray-400 top-1/2 w-5"
                    fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path clip-rule="evenodd"
                        d="M5.23 7.21a.75.75 0 011.06-.02L10 10.585l3.71-3.395a.75.75 0 011.04 1.08l-4 3.75a.75.75 0 01-1.04 0l-4-3.75a.75.75 0 01-.02-1.06z"
                        fill-rule="evenodd" />
                </svg>
            </div>
            @error('f_tipo_comprobante_id')
                <p class="!mt-0 text-sm text-red-600">{{ $message }}</p>
            @enderror

            <!-- Compañía -->
            <div class="relative" style="display: none;">
                <input type="text" id="compañia"
                    class="block px-2.5 pb-2.5 pt-4 w-full text-sm text-gray-900 bg-transparent rounded-lg border border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                    value="{{ $empresa->razon_social }}" disabled />
                <label for="compañia"
                    class="absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-4 scale-8 top-2 z-10 origin-[0] bg-[#f1f5f9] dark:bg-[#1A222C] px-2 peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-8 peer-focus:-translate-y-4 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto start-1">
                    Compañía
                </label>
            </div>

        </div>

        <!-- Columna Derecha -->
        <div class="space-y-4">
            <!-- Dirección -->
            <div class="relative">
                <input type="text"
                    class="block px-2.5 pb-2.5 pt-4 w-full text-sm text-gray-900 bg-transparent rounded-lg border border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                    value="{{ $direccion }}" disabled />
                <label
                    class="absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-4 scale-8 top-2 z-10 origin-[0] bg-[#f1f5f9] dark:bg-[#1A222C] px-2 peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-8 peer-focus:-translate-y-4 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto start-1">
                    Dirección
                </label>
            </div>

            <!-- Ruta -->
            <div class="relative">
                <input type="text"
                    class="block px-2.5 pb-2.5 pt-4 w-full text-sm text-gray-900 bg-transparent rounded-lg border border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                    value="{{ $this->rutaName }}" disabled />
                <label
                    class="absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-4 scale-8 top-2 z-10 origin-[0] bg-[#f1f5f9] dark:bg-[#1A222C] px-2 peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-8 peer-focus:-translate-y-4 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto start-1">
                    Ruta
                </label>
            </div>

            <!-- Lista de Precios -->
            <div class="relative">
                <input type="text"
                    class="block px-2.5 pb-2.5 pt-4 w-full text-sm text-gray-900 bg-transparent rounded-lg border border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                    value="{{ $this->listaPrecioName }}" disabled />
                <label
                    class="absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-4 scale-8 top-2 z-10 origin-[0] bg-[#f1f5f9] dark:bg-[#1A222C] px-2 peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-8 peer-focus:-translate-y-4 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto start-1">
                    Lista de Precios
                </label>
            </div>

            <!-- Documento -->
            <div class="relative">
                <input type="text"
                    class="block px-2.5 pb-2.5 pt-4 w-full text-sm text-gray-900 bg-transparent rounded-lg border border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                    value="{{ $documento }}" disabled />
                <label
                    class="absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-4 scale-8 top-2 z-10 origin-[0] bg-[#f1f5f9] dark:bg-[#1A222C] px-2 peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-8 peer-focus:-translate-y-4 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto start-1">
                    Documento
                </label>
            </div>
        </div>
    </div>

    <!-- Nueva sección de búsqueda y detalles -->
    <div class="mb-2" wire:loading wire:target="guardarPedido, ajustarCantidad, eliminarDetalle, agregarProducto">
        Cargando...
    </div>
    <div class="mt-8" wire:loading.class="hidden" wire:target="guardarPedido, ajustarCantidad, eliminarDetalle, agregarProducto">
        <div wire:loading wire:target="search">
            Buscando...
        </div>
        <!-- Buscador de Productos -->
        <div class="relative">
            <input type="text" wire:model.live.debounce.300ms="search"
                class="block px-2.5 pb-2.5 pt-4 w-full text-sm text-gray-900 bg-transparent rounded-lg border border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer {{ !$cliente_id ? 'bg-gray-100' : '' }}"
                placeholder=" " {{ !$cliente_id ? 'disabled' : '' }} />
            <label
                class="pointer-events-none absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-4 scale-8 top-2 z-10 origin-[0] bg-[#f1f5f9] dark:bg-[#1A222C] px-2 peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-8 peer-focus:-translate-y-4 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto start-1">
                {{ !$cliente_id ? 'Seleccione un Cliente primero' : 'Buscar por código o nombre del producto' }}
            </label>

            <!-- Resultados de búsqueda -->
            @if ($cliente_id && strlen($search) > 0)
                <div class="absolute z-10 w-full mt-1 bg-white rounded-lg shadow-lg dark:bg-gray-700">
                    @if ($productos && count($productos) > 0)
                        @foreach ($productos as $producto)
                            @php
                                $precio =
                                    $producto->listaPrecios->where('id', $this->lista_precio)->first()?->pivot
                                        ?->precio ?? 0;
                            @endphp
                            <div wire:click="agregarProducto({{ $producto->id }})"
                                class="px-4 py-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600">
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

        <!-- Tabla de Detalles -->
        <div class="cont_detalles mt-4 relative overflow-x-auto shadow-md sm:rounded-lg">
            <style>
                main> :first-child {
                    padding: calc(2px + 2vw);
                }

                .cont_detalles {
                    & :is(th, td) {
                        padding: calc(2px + 0.5vw);
                    }

                    td input {
                        width: clamp(35px, calc(34px + 4vw), 70px);
                    }

                    thead th:last-child {
                        display: flex;
                        justify-content: space-around;
                        align-items: center;
                    }

                    tbody td:last-child {
                        text-align: center;
                        display: flex;
                        justify-content: space-around;
                        align-items: center;
                    }
                }
            </style>
            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col">Código - Producto</th>
                        <th scope="col">Cantidad</th>
                        <th scope="col">Importe <svg width="25" height="25" viewBox="0 0 16 16" class="inline-block"
                                xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M10 3h3v1h-1v9l-1 1H4l-1-1V4H2V3h3V2a1 1 0 0 1 1-1h3a1 1 0 0 1 1 1v1zM9 2H6v1h3V2zM4 13h7V4H4v9zm2-8H5v7h1V5zm1 0h1v7H7V5zm2 0h1v7H9V5z"
                                    fill="currentColor"></path>
                            </svg></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pedido_detalles as $index => $detalle)
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                            <td class="px-6 py-4">
                                {{ $detalle['codigo'] }} - {{ $detalle['nombre'] }}
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $producto = App\Models\Producto::find($detalle['producto_id']);
                                    $esPaqueteUnico = $producto->cantidad == 1;
                                @endphp

                                @if ($esPaqueteUnico)
                                    <input type="number" min="1"
                                        wire:model.lazy="pedido_detalles.{{ $index }}.cantidad"
                                        wire:change="ajustarCantidad({{ $index }})"
                                        class="w-20 px-2 py-1 text-sm border rounded" />
                                @else
                                    <input type="number" min="0.01" step="0.01"
                                        wire:model.lazy="pedido_detalles.{{ $index }}.cantidad"
                                        wire:change="ajustarCantidad({{ $index }})"
                                        class="w-20 px-2 py-1 text-sm border rounded" />
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                S/. {{ number_format($detalle['importe'], 2) }}
                                <button wire:click="eliminarDetalle({{ $index }})"
                                    class="font-medium text-red-600 dark:text-red-500 hover:underline">
                                    <svg width="20 " height="20" viewBox="0 0 17 17" class="inline-block"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M12.566,8 L15.611,4.956 C16.031,4.535 16.031,3.853 15.611,3.434 L12.566,0.389 C12.146,-0.031 11.464,-0.031 11.043,0.389 L7.999,3.433 L4.955,0.389 C4.534,-0.031 3.852,-0.031 3.432,0.389 L0.388,3.434 C-0.034,3.854 -0.034,4.536 0.387,4.956 L3.431,8 L0.387,11.044 C-0.034,11.465 -0.034,12.147 0.388,12.567 L3.432,15.611 C3.852,16.032 4.534,16.032 4.955,15.611 L7.999,12.567 L11.043,15.611 C11.464,16.032 12.146,16.032 12.566,15.611 L15.611,12.567 C16.031,12.146 16.031,11.464 15.611,11.044 L12.566,8 L12.566,8 Z"
                                            fill="currentColor"></path>
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
                                <textarea rows="4" id="comentarios" wire:model="comentarios"
                                    class="block p-2 pt-4 w-full text-sm text-gray-900 bg-transparent rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                    placeholder="Escribe tus comentarios aquí..."></textarea>
                                <label for="comentarios"
                                    class="absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-4 scale-8 top-2 z-1 origin-[0] bg-[#f1f5f9] dark:bg-gray-800 px-2 peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 start-1 dark:bg-gradient-to-b from-[#1a222c] to-[#1f2937]">
                                    Comentarios Adicionales
                                </label>
                            </div>
                        </td>
                        <td class="px-6 py-3 text-right">Subtotal: S/. {{ number_format($totales['valorVenta'], 2) }}
                        </td>
                    </tr>
                    <tr class="font-semibold text-gray-900 dark:text-white">
                        <td class="px-6 py-3 text-right">IGV (18%): S/.
                            {{ number_format($totales['totalImpuestos'], 2) }}</td>
                    </tr>
                    <tr class="font-semibold text-gray-900 dark:text-white">
                        <td class="px-6 py-3 text-right">Total: S/. {{ number_format($totales['subTotal'], 2) }}</td>
                    </tr>
                </tfoot>
            </table>
            @if (count($pedido_detalles) === 0)
                @error('pedido_detalles')
                    <p class="mb-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            @endif
        </div>
    </div>
    <button wire:click="guardarPedido" wire:loading.class="hidden" wire:target="guardarPedido, ajustarCantidad, eliminarDetalle, agregarProducto"
        class="mt-4 mb-4 w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
        Registrar Pedido
    </button>
    @if ($this->getErrorBag()->count())
        <div>@error('vendedor_id') {{ $message }} @enderror</div>
        <div>@error('cliente_id') {{ $message }} @enderror</div>
        <div>@error('error_guardar') {!! $message !!} @enderror</div>
    @endif
</div>
