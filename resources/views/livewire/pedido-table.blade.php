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
                            <option value="{{ $tipo->id }}" @selected($tipo->id == $f_tipo_comprobante_id)>{{ $tipo->name }}
                            </option>
                        @elseif (!str_starts_with($documento, 'RUC') && $tipo->id != 2)
                            <option value="{{ $tipo->id }}" @selected($tipo->id == $f_tipo_comprobante_id)>{{ $tipo->name }}
                            </option>
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
    <div class="mb-2" wire:loading
        wire:target="guardar_pedido_items, guardarPedido, ajustarCantidad, eliminarDetalle, agregarProducto">
        Cargando...
    </div>
    <div class="mt-6" wire:loading.class="hidden"
        wire:target="guardar_pedido_items, guardarPedido, ajustarCantidad, eliminarDetalle, agregarProducto">
        <div wire:loading wire:target="search">
            Buscando...
        </div>

        <!-- Buscador de Productos -->
        <div x-data="selectProductos(@entangle('listado_productos'))" class="relative">

            <div class="mb-4 relative">
                <input type="number" x-model="cantidad_ofrecida" min="0.01" step="0.01"
                    class="block px-2.5 pb-2.5 pt-4 w-full text-sm text-gray-900 bg-transparent rounded-lg border border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer {{ !$cliente_id ? 'bg-gray-100' : '' }}"
                    placeholder=" " {{ !$cliente_id ? 'disabled' : '' }} autocomplete="false"/>
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
                    <li @mousedown.prevent
                        :class="{
                            'bg-gray-300 rounded-md': index === cursor,
                            'hover:bg-gray-300 hover:rounded-md': index !== cursor
                        }"
                        class="flex items-start gap-2 px-3 py-2 cursor-pointer">

                        <!-- Checkbox -->
                        <input type="checkbox" class="w-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500"
                            :value="producto.id" x-model.number="seleccionados" @click.stop />

                        <!-- Contenido del producto -->
                        <div @click="agregar_producto_item(producto)" class="flex-1">
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
                            <th scope="col">Importe <svg width="25" height="25" viewBox="0 0 16 16"
                                    class="inline-block" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                        d="M10 3h3v1h-1v9l-1 1H4l-1-1V4H2V3h3V2a1 1 0 0 1 1-1h3a1 1 0 0 1 1 1v1zM9 2H6v1h3V2zM4 13h7V4H4v9zm2-8H5v7h1V5zm1 0h1v7H7V5zm2 0h1v7H9V5z"
                                        fill="currentColor"></path>
                                </svg></th>
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
                                        <svg width="20" height="20" viewBox="0 0 17 17"
                                            class="inline-block w-4" xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M12.566,8 L15.611,4.956 C16.031,4.535 16.031,3.853 15.611,3.434 L12.566,0.389 C12.146,-0.031 11.464,-0.031 11.043,0.389 L7.999,3.433 L4.955,0.389 C4.534,-0.031 3.852,-0.031 3.432,0.389 L0.388,3.434 C-0.034,3.854 -0.034,4.536 0.387,4.956 L3.431,8 L0.387,11.044 C-0.034,11.465 -0.034,12.147 0.388,12.567 L3.432,15.611 C3.852,16.032 4.534,16.032 4.955,15.611 L7.999,12.567 L11.043,15.611 C11.464,16.032 12.146,16.032 12.566,15.611 L15.611,12.567 C16.031,12.146 16.031,11.464 15.611,11.044 L12.566,8 Z"
                                                fill="currentColor"></path>
                                        </svg>
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
            <button @click="guardar"
                class="mt-4 mb-4 w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">Guardar
                Pedido</button>
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

@script
    <script>
        window.selectProductos = function(productosIniciales) {
            return {
                cargando: false,
                open: false,
                search: '',
                cursor: 0,
                cantidad_ofrecida: '0.01',
                productos: productosIniciales,
                seleccionados: [],
                items: [],
                subtotal: 0,
                igv: 0,
                total: 0,

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
                    Livewire.dispatch('recargar-productos');
                },

                // helper: obtiene la cantidad de dígitos para representar (factor - 1), mínimo 2
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
                    //console.log(this.cantidad_ofrecida, typeof this.cantidad_ofrecida);
                    let ofrecida = parseFloat(this.cantidad_ofrecida || 0);
                    if (isNaN(ofrecida) || ofrecida <= 0) return; // validacion

                    const factor = parseFloat(producto.factor || 1);
                    const precio = parseFloat(producto.precio || 0);
                    const f_tipo_afectacion_id = producto.f_tipo_afectacion_id || 10;

                    const {
                        cantidad_convertida,
                        total_unidades,
                        importe
                    } = this.convertirCantidad(this.cantidad_ofrecida, factor, precio, f_tipo_afectacion_id);
                    //console.log(cantidad_convertida, total_unidades, importe);

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
                    // 1) digitos según factor (corrige el error para factores tipo 1000)
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

                    const importe = parseFloat(((precio * total_unidades) / factor).toFixed(2));

                    if (f_tipo_afectacion_id === 21) {
                        // Si es tipo de afectación 21, no se aplica IGV
                        return {
                            cantidad_convertida: cantidad_convertida,
                            total_unidades: total_unidades.toFixed(2),
                            importe: parseFloat((0).toFixed(2))
                        };
                    }

                    return {
                        cantidad_convertida: cantidad_convertida,
                        total_unidades: total_unidades.toFixed(2),
                        importe: importe.toFixed(2)
                    };
                },

                eliminar_item(index) {
                    this.items.splice(index, 1);
                    this.calcularTotales();
                },
                actualizar_importe_items(index) {
                    const item = this.items[index];
                    let digitos = this.calcularDigitos(item.factor);
                    cant = this.actualizar_importe(index)
                    if (cant !== undefined && !isNaN(parseFloat(cant))) {
                        item.cantidad = parseFloat(cant).toFixed(digitos);
                    }
                    //console.log("actualizar_importe_items", item);
                },
                actualizar_importe(index) {
                    //console.log("actualizar_importe");
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
                    const ofrecida = (parseInt(bultosStr) || 0) + (parseInt(unidadesStr || '0') / (10 ** this.calcularDigitos(factor)));

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

                    $wire.call('guardar_pedido_items', this.items).catch(error => {
                        alert(error.message); // O mostrar en tu interfaz personalizada
                    });
                    //$wire.guardar_pedido_items(this.items);
                },
                init() {
                    $wire.on('pedido-guardado', () => {
                        this.limpiarFormulario();
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
                    console.log("seleccionados:", this.seleccionados); // qué valores tienes
                    console.log("tipos:", this.seleccionados.map(s => typeof s));
                    console.log("producto ids:", this.productosFiltrados.map(p => p.id));
                    const ofrecida = this.cantidad_ofrecida;
                    seleccionados_productos.forEach(producto => {
                        this.cantidad_ofrecida = ofrecida;
                        this.agregar_producto_item(producto);
                    });
                    this.seleccionados = []; // limpiar selección
                    this.open = false;
                }

            }
        }
    </script>
@endscript
