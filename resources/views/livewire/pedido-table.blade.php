<div>
    <div class="grid md:grid-cols-2 gap-6">
        <!-- Columna Izquierda -->
        <div class="space-y-4">
            <!-- Fecha -->
            <div class="relative">
                <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                    <x-svg_calendar class="w-4 h-4 text-gray-500 dark:text-gray-400" />
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
                @if ($user->can('admin pedido'))
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
            {{-- <div class="relative">
                <livewire:cliente-select :vendedor_id="$vendedor_id" :key="'cliente-select-' . $vendedor_id" wire:model.live="cliente_id" />
            </div> --}}
            <!-- Cliente (unificado en PedidoTable) -->
            <div class="relative" x-data="clienteSelect({
                vendedorIdEntangle: $wire.entangle('vendedor_id').live,
                clienteIdEntangle: $wire.entangle('cliente_id').live
            })" @click.outside="open=false">

                <div class="relative">
                    <input type="text" x-model="search" @focus="open=true; asegurarCarga()" @input="open=true"
                        @keydown.arrow-down.prevent="mover(1)" @keydown.arrow-up.prevent="mover(-1)"
                        @keydown.enter.prevent="seleccionar(filtrados[cursor])" @keydown.escape="open=false"
                        class="block px-2.5 pb-2.5 pt-4 w-full text-sm text-gray-900 bg-transparent rounded-lg border border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer {{ !$vendedor_id ? 'bg-gray-100' : '' }}"
                        placeholder=" " {{ !$vendedor_id ? 'disabled' : '' }} />

                    <label
                        class="pointer-events-none absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-4 scale-8 top-2 z-10 origin-[0] bg-[#f1f5f9] dark:bg-[#1A222C] px-2 peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-8 peer-focus:-translate-y-4 start-1">
                        Cliente
                    </label>

                    <!-- Botones -->
                    <div class="absolute right-2 top-1/2 -translate-y-1/2 flex gap-2">
                        <button type="button" class="text-gray-500 hover:text-black" title="Recargar"
                            @click="recargarClientes()">🔄</button>

                        <button type="button" class="text-gray-500 hover:text-red-600" title="Limpiar"
                            @click="limpiar()" x-show="selectedId">✖</button>
                    </div>
                </div>

                <!-- Dropdown -->
                <ul wire:ignore x-show="open"
                    class="absolute z-20 bg-white text-black w-full border mt-1 rounded shadow overflow-y-auto text-sm max-h-72">
                    <template x-for="(c, index) in filtrados" :key="c.id ?? index">

                        <li class="px-3 py-2 cursor-pointer"
                            :class="index === cursor ? 'bg-gray-200' : 'hover:bg-gray-100'" @mousedown.prevent
                            @click="seleccionar(c)">
                            <div class="font-semibold" x-text="`${c.id} - ${c.name}`"></div>

                            <div class="text-xs text-gray-600" x-show="c.listaPrecio">
                                <span x-text="`Lista: ${c.listaPrecio}`"></span>
                            </div>

                            <div class="flex flex-wrap gap-1 mt-1">
                                <template x-for="(m, idx) in (c.marcas || [])" :key="m.id ?? idx">
                                    <span class="px-2 py-0.5 rounded text-white text-[11px]"
                                        :style="`background:${m.color}`" x-text="m.name"></span>
                                </template>
                            </div>
                        </li>
                    </template>

                    <li x-show="filtrados.length === 0" class="px-3 py-2 text-gray-500">Sin resultados</li>
                </ul>
            </div>

            <!-- Tipo de Comprobante -->
            <div class="relative">
                <select wire:model.defer="f_tipo_comprobante_id"
                    wire:key="tipo-comp-{{ $cliente_id }}-{{ count($tipoComprobantes) }}"
                    class="block px-2.5 pb-2.5 pt-4 w-full text-sm text-gray-900 bg-transparent rounded-lg border border-gray-300 appearance-none peer">
                    <option value="">Selecciona un Tipo de Comprobante</option>

                    @foreach ($tipoComprobantes as $tipo)
                        <option value="{{ $tipo->id }}">{{ $tipo->name }}</option>
                    @endforeach
                </select>

                <label
                    class="absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-4 scale-8 top-2 z-10 origin-[0] bg-[#f1f5f9] dark:bg-[#1A222C] px-2 peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-8 peer-focus:-translate-y-4 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto start-1">
                    Tipo de Comprobante
                </label>
                <x-svg_chevron_down
                    class="h-5 w-5 text-gray-400 dark:text-gray-500 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" />
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
    <div class="my-2" wire:loading
        wire:target="guardar_pedido_items, guardarPedido, ajustarCantidad, eliminarDetalle, agregarProducto, cliente_id, selectCliente, clearClienteSelection, loadClientesOptions">
        Cargando...
    </div>
    <div class="mt-6" wire:loading.class="hidden"
        wire:target="guardar_pedido_items, guardarPedido, ajustarCantidad, eliminarDetalle, agregarProducto, cliente_id, selectCliente, clearClienteSelection, loadClientesOptions">
        <div wire:loading wire:target="search">
            Buscando...
        </div>

        <!-- Buscador de Productos -->
        <div x-data="selectProductos({ clienteIdEntangle: $wire.entangle('cliente_id').live })" class="relative">

            <div class="mb-4 relative">
                <input type="number" x-model="cantidad_ofrecida" min="0.01" step="0.01"
                    class="block px-2.5 pb-2.5 pt-4 w-full text-sm text-gray-900 bg-transparent rounded-lg border border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer {{ !$cliente_id ? 'bg-gray-100' : '' }}"
                    placeholder=" " {{ !$cliente_id ? 'disabled' : '' }} autocomplete="false" />
                <label
                    class="pointer-events-none absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-4 scale-8 top-2 z-10 origin-[0] bg-[#f1f5f9] dark:bg-[#1A222C] px-2 peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-8 peer-focus:-translate-y-4 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto start-1">
                    Cantidad Ofrecida
                </label>
            </div>

            <div class="relative" @click.outside="open = false">
                <input type="text" x-model="search" @focus="open = true" @input="open = true"
                    @keydown.arrow-down.prevent="moverCursor(1)" @keydown.arrow-up.prevent="moverCursor(-1)"
                    @keydown.enter.prevent="agregar_producto_item(productosFiltrados[cursor])"
                    @keydown.escape="open = false" @click="open = true"
                    class="block px-2.5 pb-2.5 pt-4 w-full text-sm text-gray-900 bg-transparent rounded-lg border border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer {{ !$cliente_id ? 'bg-gray-100' : '' }}"
                    placeholder=" " {{ !$cliente_id ? 'disabled' : '' }} />
                <label
                    class="pointer-events-none absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-4 scale-8 top-2 z-10 origin-[0] bg-[#f1f5f9] dark:bg-[#1A222C] px-2 peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-8 peer-focus:-translate-y-4 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto start-1">
                    {{ !$cliente_id ? 'Seleccione un Cliente primero' : 'Buscar por código o nombre del producto' }}
                </label>

                <!-- Botón recargar -->
                <button type="button" @click="recargarProductos"
                    class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-500 hover:text-black">
                    🔄
                </button>
            </div>

            <!-- Lista -->
            <ul x-show="open"
                class="absolute z-10 bg-white text-black w-full border mt-1 rounded shadow overflow-y-auto text-sm pb-3">
                <template x-for="(producto, index) in productosFiltrados" :key="producto.id">
                    <li @mousedown.prevent @click="agregar_producto_item(producto)"
                        :class="{
                            'bg-gray-300 rounded-md': index === cursor,
                            'hover:bg-gray-300 hover:rounded-md': index !== cursor
                        }"
                        class="flex items-start gap-2 px-3 py-2 cursor-pointer">

                        <!-- Checkbox -->
                        <input type="checkbox" class="w-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500"
                            :value="producto.id" x-model.number="seleccionados" @click.stop />

                        <!-- Contenido del producto -->
                        <div class="flex-1">
                            <div class="font-semibold" x-text="`${producto.id} - ${producto.nombre}`"></div>
                            <div>
                                <span
                                    x-text="`Marca: ${producto.marca} | Precio: S/. ${parseFloat(producto.precio).toFixed(2)} | Factor: ${producto.factor}`"></span>
                                <template x-if="producto.deleted_at">
                                    <span><x-svg_circle_equis /></span>
                                </template>
                            </div>
                        </div>
                    </li>
                </template>

                <li x-show="productosFiltrados.length === 0" class="px-3 py-2 text-gray-500">Sin resultados
                </li>
                <!-- Botón centrado -->
                <li class="px-3 py-2 text-center">
                    <button type="button" @click="agregar_seleccionados()"
                        class="px-4 py-1 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">
                        Agregar productos seleccionados
                    </button>
                </li>
            </ul>

            <!-- Lista temporal de ítems del pedido -->
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
                            width: clamp(35px, calc(50px + 4vw), 80px);
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
                            <th scope="col">Importe <x-svg_tacho /></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(item, index) in items" :key="item.id">
                            <tr class="text-xs bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                <td class="p-2 sm:px-6 sm:py-4" x-text="`${item.id} - ${item.nombre}`"></td>
                                <td class="p-2 sm:px-6 sm:py-4">
                                    <input type="number" class="min-w-[60px] px-1 py-1 text-sm border rounded"
                                        x-model="item.cantidad" :min="calcularMinStep(item.factor).min"
                                        :step="calcularMinStep(item.factor).step" @input="actualizar_importe(index)"
                                        @blur="actualizar_importe_items(index)">
                                </td>
                                <td class="p-2 sm:px-6 sm:py-4">
                                    <span x-text="`S/. ${item.importe}`"></span>
                                    <button type="button" @click="eliminar_item(index)"
                                        class="font-medium text-red-600 dark:text-red-500 hover:underline">
                                        <x-svg_equis />
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                    <tfoot>
                        <tr class="font-semibold text-gray-900 dark:text-white">
                            <td class="px-6 py-6" colspan="2" rowspan="3">
                                <!-- Comentarios (si deseas mostrar) -->
                            </td>
                            <td class="px-6 py-3 text-right">Subtotal: <span
                                    x-text="`S/. ${subtotal.toFixed(2)}`"></span>
                            </td>
                        </tr>
                        <tr class="font-semibold text-gray-900 dark:text-white">
                            <td class="px-6 py-3 text-right">IGV (18%): <span x-text="`S/. ${igv.toFixed(2)}`"></span>
                            </td>
                        </tr>
                        <tr class="font-semibold text-gray-900 dark:text-white">
                            <td class="px-6 py-3 text-right">Total: <span
                                    x-text="`S/. ${parseFloat(total).toFixed(2)}`"></span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Botón para enviar todo a Livewire -->
            <button type="button" @click="guardar" :disabled="cargando" wire:loading.attr="disabled"
                wire:dirty.attr="disabled"
                wire:target="cliente_id, selectCliente, clearClienteSelection, loadClientesOptions, guardarPedido"
                wire:loading.class :class="(cargando ? 'opacity-50 cursor-not-allowed' : '')"
                class="mt-4 mb-4 w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                <span x-show="!cargando">Guardar Pedido</span>
                <span x-show="cargando">Guardando...</span>
            </button>

        </div>
    </div>
    @if ($this->getErrorBag()->count())
        <div>
            @error('vendedor_id')
                {{ $message }}
            @enderror
        </div>
        <div>
            @error('cliente_id')
                {{ $message }}
            @enderror
        </div>
        <div>
            @error('error_guardar')
                {!! $message !!}
            @enderror
        </div>
    @endif
</div>

@once
    @script
        <script>
            window.selectProductos = function({
                clienteIdEntangle
            }) {
                return {
                    cargando: false,
                    open: false,
                    search: '',
                    cursor: 0,
                    cantidad_ofrecida: '0.01',

                    productos: [],
                    listaPrecioId: null,

                    seleccionados: [],
                    items: [],
                    subtotal: 0,
                    igv: 0,
                    total: 0,

                    clienteIdEntangle,

                    // =========================
                    // Cache helpers
                    // =========================
                    cacheKeyProductos(listaPrecioId) {
                        return `PedidoTable:productos:v1:${listaPrecioId ?? 'null'}`;
                    },

                    leerCacheProductos(listaPrecioId) {
                        try {
                            const raw = localStorage.getItem(this.cacheKeyProductos(listaPrecioId));
                            return raw ? JSON.parse(raw) : null;
                        } catch (e) {
                            return null;
                        }
                    },

                    guardarCacheProductos(listaPrecioId, data) {
                        try {
                            localStorage.setItem(this.cacheKeyProductos(listaPrecioId), JSON.stringify(data));
                        } catch (e) {}
                    },

                    // =========================
                    // Computed / UI
                    // =========================
                    get productosFiltrados() {
                        const texto = this.search.trim().toLowerCase();
                        const palabras = texto.split(/\s+/).filter(Boolean);

                        return this.productos
                            .filter(p => {
                                const campo = `${p.id} ${p.nombre} ${p.marca}`.toLowerCase();
                                return palabras.every(w => campo.includes(w));
                            })
                            .slice(0, 15);
                    },
                    moverCursor(direccion) {
                        const total = this.productosFiltrados.length;
                        if (total === 0) return;
                        this.cursor = (this.cursor + direccion + total) % total;
                    },

                    recargarProductos() {
                        this.open = false;

                        // si sabes la lista, borras cache para forzar reload real
                        if (this.listaPrecioId) {
                            try {
                                localStorage.removeItem(this.cacheKeyProductos(this.listaPrecioId));
                            } catch (e) {}
                        }

                        // llama al backend para que dispare 'productos-cargados'
                        Livewire.dispatch('recargar-productos');
                    },

                    // =========================
                    // helpers existentes (tuyos)
                    // =========================
                    calcularDigitos(factor) {
                        const f = Math.max(1, Number(factor) || 0);
                        const maxUnits = Math.max(0, f - 1); // p.ej. factor=1000 -> maxUnits=999
                        const digits = Math.max(2, String(Math.abs(Math.floor(maxUnits))).length);
                        return digits;
                    },

                    calcularMinStep(factor) {
                        let decimales = this.calcularDigitos(factor);
                        let step = "0." + "0".repeat(decimales - 1) + "1"; // ejemplo: 0.001, 0.0001, etc.
                        return {
                            min: step,
                            step: step
                        };
                    },

                    agregar_producto_item(producto) {
                        let ofrecidaStr = String(this.cantidad_ofrecida ?? '').trim();
                        if (ofrecidaStr === '' || ofrecidaStr === '.' || ofrecidaStr === '0.') {
                            ofrecidaStr = '0.01'; // valor mínimo seguro
                        }

                        let ofrecida = parseFloat(ofrecidaStr);
                        if (isNaN(ofrecida) || ofrecida <= 0) {
                            alert('Ingrese una cantidad válida');
                            return;
                        }

                        const factor = parseFloat(producto.factor || 1);
                        const precio = parseFloat(producto.precio || 0);
                        const f_tipo_afectacion_id = producto.f_tipo_afectacion_id || 10;

                        const {
                            cantidad_convertida,
                            total_unidades,
                            importe
                        } = this.convertirCantidad(ofrecida, factor, precio, f_tipo_afectacion_id);

                        const existe = this.items.find(i => i.id === producto.id);
                        if (!existe) {
                            this.items.push({
                                ...producto,
                                cantidad: cantidad_convertida, // bultos.unidades
                                unidades: total_unidades,
                                importe: importe
                            });
                            this.calcularTotales();
                        }

                        this.search = '';
                        this.cantidad_ofrecida = '0.01';
                        this.cursor = 0;
                        this.open = false;
                    },
                    convertirCantidad(ofrecida, factor, precio, f_tipo_afectacion_id) {
                        const digitos = this.calcularDigitos(factor);

                        // 2) normalizar input como string y arreglar casos como ".015" o "" o null
                        let ofrecidaStr = String(ofrecida ?? "0").trim();
                        if (ofrecidaStr === "") ofrecidaStr = "0";
                        if (ofrecidaStr.startsWith(".")) ofrecidaStr = "0" + ofrecidaStr; // ".015" -> "0.015"
                        if (ofrecidaStr.endsWith(".")) ofrecidaStr = ofrecidaStr + "0"; // "1." -> "1.0"

                        // 3) separar entero y decimal (asegurando que entero no quede vacío)
                        let [enteroStr = "0", decimalStr = ""] = ofrecidaStr.split(".");
                        if (enteroStr === "") enteroStr = "0";
                        decimalStr = decimalStr || "";

                        // 4) truncar a 'digitos' y rellenar a la derecha para que tenga exactamente 'digitos'
                        decimalStr = decimalStr.slice(0, digitos);
                        if (decimalStr.length < digitos) decimalStr = decimalStr.padEnd(digitos, "0");

                        // 5) parseos seguros
                        const parte_entera = parseInt(enteroStr, 10) || 0;
                        const parte_decimal = parseInt(decimalStr, 10) || 0;

                        // 6) cálculo en unidades
                        const total_unidades = parte_entera * factor + parte_decimal;

                        const nuevos_bultos = Math.floor(total_unidades / factor);
                        const nuevas_unidades = total_unidades % factor;

                        const cantidad_convertida = `${nuevos_bultos}.${nuevas_unidades.toString().padStart(digitos, "0")}`;

                        const importeCalc = parseFloat(((precio * total_unidades) / factor).toFixed(2));

                        if (f_tipo_afectacion_id === 21) {
                            return {
                                cantidad_convertida,
                                total_unidades: total_unidades.toFixed(2),
                                importe: parseFloat((0).toFixed(2))
                            };
                        }

                        return {
                            cantidad_convertida,
                            total_unidades: total_unidades.toFixed(2),
                            importe: importeCalc.toFixed(2)
                        };
                    },

                    eliminar_item(index) {
                        this.items.splice(index, 1);
                        this.calcularTotales();
                    },
                    actualizar_importe_items(index) {
                        const item = this.items[index];
                        let digitos = this.calcularDigitos(item.factor);
                        const cant = this.actualizar_importe(index);
                        if (cant !== undefined && !isNaN(parseFloat(cant))) {
                            item.cantidad = parseFloat(cant).toFixed(digitos);
                        }
                    },
                    actualizar_importe(index) {
                        const item = this.items[index];
                        const cantidadStr = item.cantidad?.toString() || '';

                        if (
                            cantidadStr.endsWith('.') ||
                            cantidadStr.endsWith('.0') ||
                            cantidadStr.endsWith('.00') ||
                            cantidadStr.endsWith('.000') ||
                            cantidadStr === '' ||
                            isNaN(parseFloat(cantidadStr)) ||
                            parseFloat(cantidadStr) === 0
                        ) {
                            return;
                        }

                        const factor = parseFloat(item.factor || 1);
                        const precio = parseFloat(item.precio || 0);
                        const cantidad = parseFloat(item.cantidad).toFixed(this.calcularDigitos(factor));
                        const f_tipo_afectacion_id = item.f_tipo_afectacion_id || 10;

                        const [bultosStr, unidadesStr] = cantidad.split('.');
                        const ofrecida =
                            (parseInt(bultosStr) || 0) +
                            (parseInt(unidadesStr || '0') / (10 ** this.calcularDigitos(factor)));

                        const {
                            cantidad_convertida,
                            total_unidades,
                            importe
                        } = this.convertirCantidad(ofrecida, factor, precio, f_tipo_afectacion_id);

                        item.unidades = total_unidades;
                        item.importe = importe;

                        this.calcularTotales();
                        return cantidad_convertida;
                    },

                    calcularTotales() {
                        this.total = this.items.reduce((sum, i) => {
                            const importe = parseFloat(i.importe);
                            return sum + (isNaN(importe) ? 0 : importe);
                        }, 0);

                        if (this.total > 0) {
                            this.subtotal = parseFloat((this.total / 1.18).toFixed(2));
                            this.igv = parseFloat((this.total - this.subtotal).toFixed(2));
                        } else {
                            this.subtotal = 0;
                            this.igv = 0;
                        }
                    },

                    guardar() {
                        if (this.cargando) return; // corta doble click

                        if (this.items.length === 0) {
                            alert("No hay productos agregados al pedido.");
                            return;
                        }

                        const errores = this.items.filter(item => {
                            const cantidadValida = item.cantidad && !isNaN(item.cantidad) && parseFloat(item
                                .cantidad) > 0;
                            const unidadesValidas = item.unidades && !isNaN(item.unidades) && parseInt(item
                                .unidades) > 0;
                            const importeValido = item.f_tipo_afectacion_id == 21 ?
                                true :
                                (item.importe && !isNaN(item.importe) && parseFloat(item.importe) > 0);

                            return !(cantidadValida && unidadesValidas && importeValido);
                        });

                        if (errores.length > 0) {
                            alert(
                                "Hay productos con datos inválidos (cantidades, unidades o importes). Corrige antes de guardar."
                            );
                            return;
                        }

                        if (isNaN(this.total) || this.total <= 0) {
                            alert("El total del pedido debe ser mayor a cero.");
                            return;
                        }

                        this.cargando = true;

                        $wire.call('guardar_pedido_items', this.items)
                            .catch(error => alert(error.message))
                            .finally(() => {
                                this.cargando = false;
                            });
                    },
                    limpiarFormulario() {
                        this.items = [];
                        this.search = '';
                        this.cantidad_ofrecida = '0.01';
                        this.subtotal = 0;
                        this.igv = 0;
                        this.total = 0;
                    },
                    agregar_seleccionados() {
                        const seleccionados_ids = this.seleccionados;
                        const seleccionados_productos = this.productosFiltrados.filter(p => seleccionados_ids.includes(p
                            .id));
                        const ofrecida = this.cantidad_ofrecida;
                        seleccionados_productos.forEach(producto => {
                            this.cantidad_ofrecida = ofrecida;
                            this.agregar_producto_item(producto);
                        });
                        this.seleccionados = [];
                        this.open = false;
                    },

                    // ✅ UN SOLO init, con TODO dentro
                    init() {
                        // 1) cuando backend manda productos
                        $wire.on('productos-cargados', ({
                            productos,
                            lista_precio_id
                        }) => {
                            this.listaPrecioId = lista_precio_id ?? null;

                            if (this.listaPrecioId) {
                                this.guardarCacheProductos(this.listaPrecioId, productos || []);
                            }

                            this.productos = productos || [];
                        });

                        // 2) limpiar productos si se limpia cliente
                        this.$watch('clienteIdEntangle', (val) => {
                            if (!val) {
                                this.productos = [];
                                this.listaPrecioId = null;
                            }
                        });

                        // 3) evento que ya tenías
                        $wire.on('pedido-guardado', () => {
                            this.limpiarFormulario();
                        });
                    },
                }
            }

            window.clienteSelect = function({
                vendedorIdEntangle,
                clienteIdEntangle
            }) {
                return {
                    open: false,
                    cursor: 0,

                    clientes: [],
                    search: '',
                    selectedId: null,

                    vendedorIdEntangle,
                    clienteIdEntangle,

                    cacheKeyClientes(vendedorId) {
                        return `PedidoTable:clientes:v1:${vendedorId ?? 'null'}`;
                    },

                    leerCacheClientes(vendedorId) {
                        try {
                            const raw = localStorage.getItem(this.cacheKeyClientes(vendedorId));
                            return raw ? JSON.parse(raw) : null;
                        } catch (e) {
                            return null;
                        }
                    },

                    guardarCacheClientes(vendedorId, data) {
                        try {
                            localStorage.setItem(this.cacheKeyClientes(vendedorId), JSON.stringify(data));
                        } catch (e) {}
                    },

                    // SOLO UI (sin backend)
                    resetUi() {
                        this.selectedId = null;
                        this.search = '';
                        this.open = false;
                        this.cursor = 0;
                    },

                    asegurarCarga() {
                        const vendedorId = this.vendedorIdEntangle;
                        if (!vendedorId) {
                            this.clientes = [];
                            return;
                        }

                        const cached = this.leerCacheClientes(vendedorId);
                        if (cached && Array.isArray(cached) && cached.length) {
                            this.clientes = cached;
                            return;
                        }

                        // primera carga (UNA sola vez)
                        this.$wire.loadClientesOptions();
                    },

                    get filtrados() {
                        const texto = (this.search || '').trim().toLowerCase();
                        const palabras = texto.split(/\s+/).filter(Boolean);

                        if (!palabras.length) return this.clientes;

                        return this.clientes.filter(c => {
                            const campo = `${c.id} ${c.name} ${c.listaPrecio ?? ''}`.toLowerCase();
                            return palabras.every(w => campo.includes(w));
                        });
                    },

                    mover(dir) {
                        const total = this.filtrados.length;
                        if (!total) return;
                        this.cursor = (this.cursor + dir + total) % total;
                    },

                    seleccionar(c) {
                        if (!c) return;

                        this.selectedId = c.id;
                        this.search = c.name;
                        this.open = false;

                        // setea en Livewire solo al seleccionar (no en cada tecla)
                        this.clienteIdEntangle = c.id;
                        this.$wire.selectCliente(c.id);
                    },

                    limpiar() {
                        // UI primero
                        this.resetUi();
                        this.open = true;

                        // Livewire
                        this.clienteIdEntangle = "";
                        this.$wire.clearClienteSelection();
                    },

                    recargarClientes() {
                        this.open = false;

                        const vendedorId = this.vendedorIdEntangle;
                        if (vendedorId) {
                            try {
                                localStorage.removeItem(this.cacheKeyClientes(vendedorId));
                            } catch (e) {}
                        }

                        this.$wire.loadClientesOptions();
                    },

                    init() {
                        // ✅ payload desde backend
                        $wire.on('clientes-cargados', ({
                            clientes,
                            vendedor_id
                        }) => {
                            const vid = vendedor_id ?? this.vendedorIdEntangle ?? null;
                            const list = clientes || [];

                            if (vid) this.guardarCacheClientes(vid, list);
                            this.clientes = list;
                        });

                        // CLAVE: si Livewire limpia cliente_id (por ejemplo tras guardar), limpia el input visible
                        this.$watch('clienteIdEntangle', (val) => {
                            if (!val) {
                                this.resetUi(); // ⬅aquí se quita "JUNIOR MECHATO"
                            }
                        });

                        // cuando cambia vendedor, solo UI + recarga (sin doble llamada a clearClienteSelection)
                        this.$watch('vendedorIdEntangle', (newVal) => {
                            this.resetUi();
                            this.clienteIdEntangle = "";
                            this.clientes = [];

                            if (!newVal) return;

                            const cached = this.leerCacheClientes(newVal);
                            if (cached && Array.isArray(cached) && cached.length) {
                                this.clientes = cached;
                                return;
                            }

                            this.$wire.loadClientesOptions();
                        });

                        // si ya hay vendedor al cargar, intenta cache
                        this.asegurarCarga();
                    },
                }
            }
        </script>
    @endscript
@endonce
