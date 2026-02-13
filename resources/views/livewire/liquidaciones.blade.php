<div class="text-xs sm:text-sm">
    @if ($view == 'liquidaciones')
        <div wire:loading.class="opacity-50 pointer-events-none">
            <h2 class="text-lg font-semibold mb-4">Liquidaciones</h2>
            <label>Fecha reparto:
                <input type="date" wire:model.live="fecha_fin"
                    class="px-2 sm:px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring focus:ring-indigo-200 focus:border-indigo-500 transition-colors duration-200 ease-in-out">
            </label>
        </div>
        <br />
        <div wire:loading.class='hidden'>
            <table class="min-w-full border border-gray-300 shadow-md rounded-lg" id="dataTable_example"
                x-data="{ liquidar(id) { $wire.call('liquidar', id) } }">
                <thead class="bg-gray-200 text-gray-700">
                    <tr>
                        <th class="px-2 sm:px-4 py-2 text-left border-b">ID</th>
                        <th class="px-2 sm:px-4 py-2 text-left border-b">Fecha</th>
                        <th class="px-2 sm:px-4 py-2 text-left border-b">Conductor</th>
                        <th class="px-2 sm:px-4 py-2 text-left border-b">Monto</th>
                        <th class="px-2 sm:px-4 py-2 text-left border-b">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-300">
                    @foreach ($movimientos as $liquidacion)
                        <tr class="hover:bg-gray-100">
                            <td class="px-2 sm:px-4 py-2 border-b">{{ $liquidacion->id }}</td>
                            <td class="px-2 sm:px-4 py-2 border-b">
                                {{ carbon_parse($liquidacion->fecha_liquidacion)->format('d-m-Y') }}</td>
                            <td class="px-2 sm:px-4 py-2 border-b">{{ $liquidacion->conductor_id }} -
                                {{ $liquidacion->conductor->name }}</td>
                            <td class="px-2 sm:px-4 py-2 border-b">
                                {{ number_format($liquidacion->pedidos->sum('importe_total'), 2) }}</td>
                            <td class="px-2 sm:px-4 py-2 border-b">
                                @if ($liquidacion->estado == 'por liquidar')
                                    <button @click="liquidar({{ $liquidacion->id }})"
                                        class="px-3 py-1 md:text-sm text-white bg-blue-600 rounded-md hover:bg-blue-700">Liquidar</button>
                                @else
                                    <button wire:click="ver_liquidacion({{ $liquidacion->id }})"
                                        class="px-3 py-1 md:text-sm text-white bg-blue-600 rounded-md">Ver</button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @elseif ($view == 'liquidacion comprobantes')
        <div wire:loading.class="opacity-50 pointer-events-none">
            @if ($regresa)
                <div class="flex justify-between">
                    <button wire:click="regresar"
                        class="px-3 py-1 md:text-sm text-white bg-blue-600 rounded-md">Volver</button>
                    {{-- <button wire:click="guardar_anulados"
                    class="px-3 py-1 md:text-sm text-white bg-blue-600 rounded-md">Guardar Anulados</button> --}}
                </div>
            @endif
        </div>
        <br />
        <br />
        <div>
            <p class="px-2">Fecha Reparto: {{ format_date_long($movimientos->first()->fecha_liquidacion) }}</p>
            <p class="px-2">Conductor: {{ $movimientos->first()->conductor_id }} -
                {{ $movimientos->first()->conductor->name }}</p>
            <p class="px-2">Cantidad de Comprobantes: {{ $comprobantes->count() }}</p>
            <p class="px-2">Importe Total: {{ number_format($comprobantes->sum('mtoImpVenta'), 2) }}</p>
            <p class="px-2 text-blue-700">Devuelto: {{ number_format($comprobantes->sum('total_devuelto'), 2) }}</p>
        </div>
        <br />
        <div wire:loading.class='hidden'>
            <table class="min-w-full border border-gray-300 shadow-md rounded-lg">
                <thead class="bg-gray-200 text-gray-700">
                    <tr>
                        <th class="px-2 sm:px-4 py-2 text-left border-b">Tipo Comp.</th>
                        <th class="px-2 sm:px-4 py-2 text-left border-b">Serie - Correlativo</th>
                        <th class="px-2 sm:px-4 py-2 text-left border-b">Cod - Cliente</th>
                        <th class="px-2 sm:px-4 py-2 text-left border-b">Imp.Venta</th>
                        <th class="px-2 sm:px-4 py-2 text-left border-b text-blue-700">Valor Devuelto</th>
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
                            <td class="px-2 sm:px-4 py-2 border-b">{{ $comprobante->total_devuelto }}</td>
                            <td class="px-2 sm:px-4 py-2 border-b">{{ $comprobante->estado_reporte ? '' : 'Devol.' }}
                            </td>
                            <td class="px-2 sm:px-4 py-2 border-b">
                                <!-- Botón Devoluciones -->
                                <button wire:click="mostrarDevolucion({{ $comprobante->id }})"
                                    class="px-3 py-1 md:text-sm text-white bg-green-600 rounded-md">
                                    Devolucion
                                </button>
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
            <div x-data="{
                open: @entangle('modalDevolucion').live,
                detalles: $wire.entangle('detalleSeleccionado'),
                comprobanteSeleccionado: $wire.entangle('comprobanteSeleccionado_array'),
                devolucionTotal: false,
                procesando: false,

                validarCantidad(item) {
                    let valor = item.cantidad_devuelta;

                    // Si el usuario ingresa texto tipo ++ o --, lo limpiamos
                    if (isNaN(valor) || typeof valor !== 'number') {
                        item.cantidad_devuelta = 0;
                    }

                    // Convertir a entero
                    item.cantidad_devuelta = Math.floor(item.cantidad_devuelta || 0);

                    // Evitar negativos
                    if (item.cantidad_devuelta < 0) {
                        item.cantidad_devuelta = 0;
                    }

                    // Evitar que supere la cantidad original
                    if (item.cantidad_devuelta > item.cantidad) {
                        item.cantidad_devuelta = item.cantidad;
                        item._error = true;
                        setTimeout(() => item._error = false, 1500);
                    }
                },
                decimalto2(item) {
                    if (item.cantidad_devuelta !== '') {
                        item.cantidad_devuelta = parseFloat(item.cantidad_devuelta).toFixed(2);
                    }
                },
                toggleDevolucionTotal() {
                    // Si se marca el checkbox, asignamos cantidad_devuelta = cantidad
                    this.detalles.forEach(item => {
                        item.cantidad_devuelta = this.devolucionTotal ? item.cantidad : 0;
                    });
                }
            }" x-show="open" x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">

                <div class="bg-white w-full max-w-4xl rounded-lg shadow-lg p-6 relative"
                    x-on:livewire:load.window="
                Livewire.hook('message.sent', () => procesando = true)
                Livewire.hook('message.processed', () => procesando = false)">
                    <button @click="$wire.cerrarModal()" wire:target="guardarDevoluciones, cerrarModal"
                        wire:loading.attr="disabled"
                        class="absolute top-2 right-2 text-gray-500 hover:text-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">✕</button>

                    <h2 class="text-lg font-semibold mb-4">Detalles del comprobante</h2>

                    @if ($comprobanteSeleccionado)
                        <div class="mb-4">
                            <p><strong>Tipo:</strong> {{ $comprobanteSeleccionado->tipoDoc_name }}</p>
                            <p><strong>Serie:</strong> {{ $comprobanteSeleccionado->serie }} -
                                {{ $comprobanteSeleccionado->correlativo }}</p>
                            <p><strong>Cliente:</strong> {{ $comprobanteSeleccionado->clientRazonSocial }}</p>
                            <p><strong>Importe:</strong> S/
                                {{ number_format($comprobanteSeleccionado->mtoImpVenta, 2) }}
                            </p>
                        </div>

                        <h3 class="text-md font-semibold mb-2">Detalle de productos</h3>
                        <!-- Checkbox de devolución total -->
                        <template x-if="comprobanteSeleccionado.estado_reporte">
                            <div class="flex items-center justify-end mb-2">
                                <label class="flex items-center text-sm text-gray-700 cursor-pointer">
                                    <input type="checkbox" x-model="devolucionTotal" @change="toggleDevolucionTotal"
                                        :disabled="!comprobanteSeleccionado.estado_reporte"
                                        class="mr-2 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    Devolución total
                                </label>
                            </div>
                        </template>

                        <template x-if="detalles.length">
                            <table class="min-w-full border border-gray-300 text-sm">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="px-2 py-1 border-b text-left">Cod</th>
                                        <th class="px-2 py-1 border-b text-left">Producto</th>
                                        <th class="px-2 py-1 border-b text-right">Cant.</th>
                                        <th class="px-2 py-1 border-b text-right">Precio</th>
                                        <th class="px-2 py-1 border-b text-right">Total</th>
                                        <th class="px-2 py-1 border-b text-right text-blue-700">Cant. Devuelta</th>
                                        <th class="px-2 py-1 border-b text-right text-blue-700">Valor Devuelto</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(item, index) in detalles" :key="index">
                                        <tr :class="item._error ? 'bg-red-100' : ''">
                                            <td class="px-2 py-1 border-b" x-text="item.codProducto"></td>
                                            <td class="px-2 py-1 border-b" x-text="item.descripcion"></td>
                                            <td class="px-2 py-1 border-b text-right" x-text="item.cantidad"></td>
                                            <td class="px-2 py-1 border-b text-right"
                                                x-text="Number(item.mtoPrecioUnitario).toFixed(2)"></td>
                                            <td class="px-2 py-1 border-b text-right"
                                                x-text="(item.cantidad * item.mtoPrecioUnitario).toFixed(2)"></td>

                                            <!-- Cantidad devuelta -->
                                            <td class="px-2 py-1 border-b text-right">
                                                <template x-if="comprobanteSeleccionado.estado_reporte">
                                                    <input type="number" min="0" :max="item.cantidad"
                                                        step="1" x-model.number="item.cantidad_devuelta"
                                                        @input="validarCantidad(item)" @blur="decimalto2(item)"
                                                        class="w-20 border rounded-md text-right px-1 py-0.5"
                                                        :class="item._error ? 'border-red-500 bg-red-50' : ''">
                                                </template>
                                                <template x-if="!comprobanteSeleccionado.estado_reporte">
                                                    <span x-text="item.cantidad_devuelta || 0"></span>
                                                </template>
                                            </td>

                                            <!-- Valor devuelto -->
                                            <td class="px-2 py-1 border-b text-right text-blue-700 font-semibold"
                                                x-text="(Number(item.cantidad_devuelta || 0) * Number(item.mtoPrecioUnitario)).toFixed(2)">
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </template>

                        <template x-if="!detalles.length">
                            <p class="text-gray-500 italic mt-4 text-center">No hay detalles disponibles</p>
                        </template>

                        <!-- Total devolución -->
                        <div class="text-right mt-4 font-semibold">
                            Total devolución:
                            <span class="text-blue-700"
                                x-text="detalles.reduce((acc, i) => acc + (Number(i.cantidad_devuelta || 0) * Number(i.mtoPrecioUnitario)), 0).toFixed(2)">
                            </span>
                        </div>
                    @endif
                    @if (optional($comprobanteSeleccionado)->estado_reporte)
                        <button
                            @click="if (confirm('(IRREVERSIBLE): Esta acción permitirá registrar devoluciones para este comprobante. ¿Desea continuar?')) { $wire.guardarDevoluciones(detalles); }"
                            wire:target="guardarDevoluciones, cerrarModal" wire:loading.attr="disabled"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="guardarDevoluciones, cerrarModal">💾 Guardar
                                devolución</span>
                            <span wire:loading wire:target="guardarDevoluciones, cerrarModal">⏳ Guardando...</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @elseif ($view == 'liquidacion detalle')
        <div wire:loading.class="opacity-50 pointer-events-none">
            @if ($regresa)
                <button wire:click="regresar"
                    class="px-3 py-1 md:text-sm text-white bg-blue-600 rounded-md">Volver</button>
                <br />
            @else
                <div class="flex justify-between" wire:loading.class="opacity-50 pointer-events-none">
                    <div class="flex flex-wrap gap-1">
                        <button wire:click="volver"
                            class="px-3 py-1 md:text-sm text-white bg-blue-600 rounded-md">Volver</button>
                        <button wire:click="diferencias"
                            class="px-3 py-1 md:text-sm text-white bg-yellow-600 rounded-md">Diferencias</button>
                        {{-- <button wire:click="agregar_salida"
                        class="px-3 py-1 md:text-sm text-white bg-red-600 rounded-md">Add.Salida</button>
                    <button wire:click="agregar_ingreso"
                        class="px-3 py-1 md:text-sm text-white bg-green-600 rounded-md">Add.Ingreso</button> --}}
                        <button wire:click="liquidacion_comprobantes"
                            class="px-3 py-1 md:text-sm text-white bg-gray-600 rounded-md">Comprobantes</button>
                    </div>
                    {{-- <button wire:click="liquidacion_comprobantesmmmm"
                    class="px-3 py-1 md:text-sm text-white bg-indigo-600 rounded-md">Grabar Liquidacion</button> --}}
                </div>
            @endif
        </div>
        <br />
        <div>
            <p>Fecha Reparto: {{ $movimientos->first()->fecha_liquidacion }}</p>
            <p>Conductor: {{ $movimientos->first()->conductor_id }} - {{ $movimientos->first()->conductor->name }}</p>
        </div>
        <br />
        <div wire:loading.class='hidden' class="w-full overflow-x-auto">
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
                            <td class="px-2 sm:px-4 py-2 border-b">{{ $producto->extras_ingreso_paquetes }}</td>
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
    @elseif ($view == 'agregar ingreso/salida')
        @if ($regresa)
            <div class="flex justify-between">
                <button wire:click="regresar"
                    class="px-3 py-1 md:text-sm text-white bg-blue-600 rounded-md">Volver</button>
                <button wire:click="guardarMovimiento"
                    class="px-3 py-1 md:text-sm text-white bg-blue-600 rounded-md">Guardar</button>
            </div>
        @endif
        <br />
        <div class="mx-auto bg-gray-800 p-4 sm:p-6 rounded-lg shadow-lg text-white">
            <!-- Tabla -->
            <div>
                <!-- Buscador de Productos -->
                <div x-data="selectProductos(@entangle('listado_productos'))" class="relative w-full max-w-2xl">

                    <div class="relative">
                        <input type="text" placeholder="Buscar por código o nombre del producto" x-model="search"
                            @focus="open = true" @input="open = true" @keydown.arrow-down.prevent="moverCursor(1)"
                            @keydown.arrow-up.prevent="moverCursor(-1)"
                            @keydown.enter.prevent="seleccionarProducto(productosFiltrados[cursor])"
                            @click.away="open = false"
                            class="w-full border rounded px-3 py-2 pr-10 bg-white text-black" />

                        <!-- Botón recargar -->
                        <button type="button" @click="recargarProductos"
                            class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-500 hover:text-black">
                            🔄
                        </button>
                    </div>

                    <!-- Lista -->
                    <ul x-show="open"
                        class="absolute z-10 bg-white text-black w-full border mt-1 rounded shadow max-h-[80vh] overflow-y-auto text-[11px]">
                        <template x-for="(producto, index) in productosFiltrados" :key="producto.id">
                            <li @click="seleccionarProducto(producto)"
                                :class="{
                                    'bg-gray-300 rounded-md': index === cursor,
                                    'hover:bg-gray-300 hover:rounded-md': index !== cursor
                                }"
                                class="px-3 py-2 cursor-pointer">
                                <div class="font-semibold" x-text="`${producto.id} - ${producto.nombre}`"></div>
                                <div>
                                    <span
                                        x-text="`Marca: ${producto.marca} | Precio: S/. ${parseFloat(producto.precio).toFixed(2)} | Stock disp.: ${parseFloat(producto.stock).toFixed(2)}`"></span>
                                    <template x-if="producto.deleted_at">
                                        <span><x-svg_circle_equis /></span>
                                    </template>
                                </div>
                            </li>
                        </template>

                        <li x-show="productosFiltrados.length === 0" class="px-3 py-2 text-gray-500">Sin resultados
                        </li>
                    </ul>

                    <input type="hidden" name="producto_id" :value="seleccionado ? seleccionado.id : ''">

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
            <div class="mt-6 flex justify-between items-center">
                <div class="text-sm">
                </div>
                <div>

                    <button wire:click="guardarMovimiento"
                        class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md font-semibold shadow-md">
                        Guardar
                    </button>
                    @error('detalles')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>
    @endif
    <div wire:loading.delay
        class="h-16 w-16 animate-spin rounded-full border-4 border-solid border-primary border-t-transparent">
    </div>
</div>

@script
    <script>
        window.selectProductos = function(productosIniciales) {
            return {
                open: false,
                search: '',
                seleccionado: null,
                cursor: 0,
                productos: productosIniciales,

                seleccionarProducto(producto) {
                    this.seleccionado = producto;
                    this.search = '';
                    this.open = false;
                    @this.call('agregarProducto', producto.id); // También puedes usar: @this.call()
                },

                moverCursor(direccion) {
                    const total = this.productosFiltrados.length;
                    this.cursor = (this.cursor + direccion + total) % total;
                },

                recargarProductos() {
                    Livewire.dispatch('recargar-productos');
                },

                get productosFiltrados() {
                    const texto = this.search.trim().toLowerCase();
                    if (!Array.isArray(this.productos)) return [];

                    const palabras = texto.split(/\s+/).filter(Boolean);
                    const filtrados = this.productos.filter(p => {
                        const campo = `${p.id} ${p.nombre} ${p.marca}`.toLowerCase();
                        return palabras.every(w => campo.includes(w));
                    });

                    if (this.cursor >= filtrados.length) this.cursor = 0;
                    return filtrados.slice(0, 15);
                }
            }
        }

        $wire.on('DataTable-initialize', () => {
            requestAnimationFrame(() => {
                const table = document.querySelector('#dataTable_example');
                if (table) {
                    console.log("✅ DataTable ready to initialize");
                    new DataTable('#dataTable_example');

                    queueMicrotask(() => {
                        Alpine.initTree(document.getElementById('dataTable_example'));
                    });
                } else {
                    console.warn("❌ Tabla no encontrada al momento de inicializar");
                }
            });
        });
    </script>
@endscript
