<div>
    <div class="grid md:grid-cols-2 gap-6">
        <!-- Columna Izquierda -->
        <div class="space-y-4">
            <!-- Fecha -->
            <div class="relative">
                <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                    <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
                    </svg>
                </div>
                <input
                    type="text"
                    class="block ps-10 px-2.5 pb-2.5 pt-4 w-full text-sm text-gray-900 bg-transparent rounded-lg border border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                    value="{{ $fecha_emision }}"
                    disabled
                />
                <label
                    class="absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-4 scale-8 top-2 z-10 origin-[0] bg-[#f1f5f9] dark:bg-[#1A222C] px-2 peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-8 peer-focus:-translate-y-4 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto start-1"
                >
                    Fecha
                </label>
            </div>

            <!-- Vendedor -->
            <div class="relative">
                @if($user->hasRole('admin'))
                    <select
                        wire:model.live="vendedor_id"
                        class="block px-2.5 pb-2.5 pt-4 w-full text-sm text-gray-900 bg-transparent rounded-lg border border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                    >
                        <option value="">Selecciona un Vendedor</option>
                        @foreach($vendedores as $vendedor)
                            <option value="{{ $vendedor->id }}">{{ $vendedor->name }}</option>
                        @endforeach
                    </select>
                @else
                    <input
                        type="text"
                        class="block px-2.5 pb-2.5 pt-4 w-full text-sm text-gray-900 bg-transparent rounded-lg border border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                        value="{{ $empleado->name }}"
                        disabled
                    />
                @endif
                <label
                    class="absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-4 scale-8 top-2 z-10 origin-[0] bg-[#f1f5f9] dark:bg-[#1A222C] px-2 peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-8 peer-focus:-translate-y-4 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto start-1"
                >
                    Vendedor
                </label>
                <svg class="-translate-y-1/2 absolute dark:text-gray-500 h-5 pointer-events-none right-3 text-gray-400 top-1/2 w-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path clip-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06-.02L10 10.585l3.71-3.395a.75.75 0 011.04 1.08l-4 3.75a.75.75 0 01-1.04 0l-4-3.75a.75.75 0 01-.02-1.06z" fill-rule="evenodd" /></svg>
            </div>

            <!-- Cliente -->
            <div class="relative">
                <select
                    wire:model.live="cliente_id"
                    id="cliente"
                    class="block px-2.5 pb-2.5 pt-4 w-full text-sm text-gray-900 bg-transparent rounded-lg border border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                >
                    <option value="">Selecciona un Cliente</option>
                    @foreach($clientes as $cliente)
                        <option value="{{ $cliente->id }}">{{ $cliente->razon_social }}</option>
                    @endforeach
                </select>
                <label
                    for="cliente"
                    class="pointer-events-none absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-0 scale-8 top-4 z-10 origin-[0] bg-[#f1f5f9] dark:bg-[#1A222C] px-2 peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-valid:top-2 peer-valid:scale-8 peer-valid:-translate-y-4 start-1"
                >
                    Selecciona un Cliente
                </label>
                <svg class="-translate-y-1/2 absolute dark:text-gray-500 h-5 pointer-events-none right-3 text-gray-400 top-1/2 w-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path clip-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06-.02L10 10.585l3.71-3.395a.75.75 0 011.04 1.08l-4 3.75a.75.75 0 01-1.04 0l-4-3.75a.75.75 0 01-.02-1.06z" fill-rule="evenodd" /></svg>
            </div>

            <!-- Tipo de Comprobante -->
            <div class="relative">
                <select
                    wire:model.live="f_tipo_comprobante_id"
                    class="block px-2.5 pb-2.5 pt-4 w-full text-sm text-gray-900 bg-transparent rounded-lg border border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                >
                    <option value="">Selecciona un Tipo de Comprobante</option>
                    @foreach($tipoComprobantes as $tipo)
                        <option value="{{ $tipo->id }}">{{ $tipo->name }}</option>
                    @endforeach
                </select>
                <label
                    class="absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-4 scale-8 top-2 z-10 origin-[0] bg-[#f1f5f9] dark:bg-[#1A222C] px-2 peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-8 peer-focus:-translate-y-4 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto start-1"
                >
                    Tipo de Comprobante
                </label>
                <svg class="-translate-y-1/2 absolute dark:text-gray-500 h-5 pointer-events-none right-3 text-gray-400 top-1/2 w-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path clip-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06-.02L10 10.585l3.71-3.395a.75.75 0 011.04 1.08l-4 3.75a.75.75 0 01-1.04 0l-4-3.75a.75.75 0 01-.02-1.06z" fill-rule="evenodd" />
                </svg>
            </div>

            <!-- Compañía -->
            <div class="relative" style="display: none;">
                <input
                    type="text"
                    id="compañia"
                    class="block px-2.5 pb-2.5 pt-4 w-full text-sm text-gray-900 bg-transparent rounded-lg border border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                    value="{{ $empresa->razon_social }}"
                    disabled
                />
                <label
                    for="compañia"
                    class="absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-4 scale-8 top-2 z-10 origin-[0] bg-[#f1f5f9] dark:bg-[#1A222C] px-2 peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-8 peer-focus:-translate-y-4 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto start-1"
                >
                    Compañía
                </label>
            </div>

        </div>

        <!-- Columna Derecha -->
        <div class="space-y-4">
            <!-- Dirección -->
            <div class="relative">
                <input
                    type="text"
                    class="block px-2.5 pb-2.5 pt-4 w-full text-sm text-gray-900 bg-transparent rounded-lg border border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                    value="{{ $direccion }}"
                    disabled
                />
                <label
                    class="absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-4 scale-8 top-2 z-10 origin-[0] bg-[#f1f5f9] dark:bg-[#1A222C] px-2 peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-8 peer-focus:-translate-y-4 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto start-1"
                >
                    Dirección
                </label>
            </div>

            <!-- Ruta -->
            <div class="relative">
                <input
                    type="text"
                    class="block px-2.5 pb-2.5 pt-4 w-full text-sm text-gray-900 bg-transparent rounded-lg border border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                    value="{{ $this->rutaName }}"
                    disabled
                />
                <label
                    class="absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-4 scale-8 top-2 z-10 origin-[0] bg-[#f1f5f9] dark:bg-[#1A222C] px-2 peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-8 peer-focus:-translate-y-4 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto start-1"
                >
                    Ruta
                </label>
            </div>

            <!-- Lista de Precios -->
            <div class="relative">
                <input
                    type="text"
                    class="block px-2.5 pb-2.5 pt-4 w-full text-sm text-gray-900 bg-transparent rounded-lg border border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                    value="{{ $this->listaPrecioName }}"
                    disabled
                />
                <label
                    class="absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-4 scale-8 top-2 z-10 origin-[0] bg-[#f1f5f9] dark:bg-[#1A222C] px-2 peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-8 peer-focus:-translate-y-4 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto start-1"
                >
                    Lista de Precios
                </label>
            </div>

            <!-- Documento -->
            <div class="relative">
                <input
                    type="text"
                    class="block px-2.5 pb-2.5 pt-4 w-full text-sm text-gray-900 bg-transparent rounded-lg border border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                    value="{{ $documento }}"
                    disabled
                />
                <label
                    class="absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-4 scale-8 top-2 z-10 origin-[0] bg-[#f1f5f9] dark:bg-[#1A222C] px-2 peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-8 peer-focus:-translate-y-4 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto start-1"
                >
                    Documento
                </label>
            </div>
        </div>
    </div>

    <!-- Nueva sección de búsqueda y detalles -->
    <div class="mt-8">
        <!-- Buscador de Productos -->
        <div class="relative">
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                class="block px-2.5 pb-2.5 pt-4 w-full text-sm text-gray-900 bg-transparent rounded-lg border border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                placeholder="Buscar por código o nombre del producto"
            />

            <!-- Resultados de búsqueda -->
            @if(!empty($productos))
                <div class="absolute z-10 w-full mt-1 bg-white rounded-lg shadow-lg dark:bg-gray-700">
                    @foreach($productos as $producto)
                        @php
                            $precio = $producto->listaPrecios
                                ->where('id', $this->lista_precio)
                                ->first()
                                ?->pivot
                                ?->precio ?? 0;
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
                                Stock: {{ $producto->cantidad }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Tabla de Detalles -->
        <div class="mt-4 relative overflow-x-auto shadow-md sm:rounded-lg">
            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3">Código - Producto</th>
                        <th scope="col" class="px-6 py-3">Cantidad</th>
                        <th scope="col" class="px-6 py-3">Importe</th>
                        <th scope="col" class="px-6 py-3">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pedido_detalles as $index => $detalle)
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                            <td class="px-6 py-4">
                                {{ $detalle['codigo'] }} - {{ $detalle['nombre'] }}
                            </td>
                            <td class="px-6 py-4">
                                <input
                                    type="text"
                                    wire:model.lazy="pedido_detalles.{{ $index }}.cantidad"
                                    wire:change="ajustarCantidad({{ $index }})"
                                    class="w-20 px-2 py-1 text-sm border rounded"
                                />
                            </td>
                            <td class="px-6 py-4">
                                S/. {{ number_format($detalle['importe'], 2) }}
                            </td>
                            <td class="px-6 py-4">
                                <button
                                    wire:click="eliminarDetalle({{ $index }})"
                                    class="font-medium text-red-600 dark:text-red-500 hover:underline"
                                >
                                    Eliminar
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
                                    id="comentarios" 
                                    wire:model="comentarios" 
                                    class="block p-2 pt-4 w-full text-sm text-gray-900 bg-transparent rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" 
                                    placeholder="Escribe tus comentarios aquí..."
                                ></textarea>
                                <label 
                                    for="comentarios" 
                                    class="absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-4 scale-8 top-2 z-10 origin-[0] bg-[#f1f5f9] dark:bg-gray-800 px-2 peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 start-1 dark:bg-gradient-to-b from-[#1a222c] to-[#1f2937]"
                                >
                                    Comentarios Adicionales
                                </label>
                            </div>
                        </td>
                        <td class="px-6 py-3 text-right">Subtotal:</td>
                        <td class="px-6 py-3 text-right">S/. {{ number_format($this->calcularSubtotal(), 2) }}</td>
                    </tr>
                    <tr class="font-semibold text-gray-900 dark:text-white">
                        <td class="px-6 py-3 text-right">IGV (18%):</td>
                        <td class="px-6 py-3 text-right">S/.</td>
                    </tr>
                    <tr class="font-semibold text-gray-900 dark:text-white">
                        <td class="px-6 py-3 text-right">Total:</td>
                        <td class="px-6 py-3 text-right">S/. {{ number_format($this->calcularSubtotal(), 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <button
        wire:click="guardarPedido"
        class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
    >
        Guardar Pedido
    </button>
</div>
