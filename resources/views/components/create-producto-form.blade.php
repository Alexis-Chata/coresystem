<div x-data="{
    open: false,
    isCompuesto: 'estandar',
    components: [],
    nextId: 1,
    showEditComponents: false,
    editingComponents: [],
    cantidadTotal: '',
    init() {
        this.$watch('isCompuesto', value => {
            $wire.set('newProducto.tipo', value);

            if (value === 'compuesto' && this.components.length === 0) {
                this.addComponent();
            }
        });

        window.addEventListener('producto-created', event => {
            this.open = false;
            this.components = [];
            this.isCompuesto = 'estandar';
        });

        window.addEventListener('update-component-stock', event => {
            const { componentId, stock } = event.detail[0];
            const component = this.components.find(c => c.id === componentId);
            if (component) {
                component.stock = stock;
            }
        });

        window.addEventListener('show-edit-components', event => {
            const { components } = event.detail[0];
            this.editingComponents = components.map(comp => ({
                id: comp.id,
                producto_id: comp.producto_id,
                cantidad: comp.cantidad,
                subcantidad: comp.subcantidad,
                stock: comp.stock,
                cantidad_total: comp.cantidad_total
            }));
            this.cantidadTotal = components[0]?.cantidad_total || '';
            this.showEditComponents = true;
        });

        window.addEventListener('components-updated', () => {
            Swal.fire({
                title: 'Éxito',
                text: 'Componentes actualizados exitosamente',
                icon: 'success',
                confirmButtonText: 'Aceptar'
            });
        });

        window.addEventListener('closeEditComponents', () => {
            this.showEditComponents = false;
        });

        window.addEventListener('update-editing-component-stock', event => {
            const { componentId, stock } = event.detail[0];
            const component = this.editingComponents.find(c => c.id === componentId);
            if (component) {
                component.stock = stock;
                if (parseFloat(component.cantidad) > parseFloat(stock)) {
                    component.cantidad = stock;
                }
            }
        });
    },
    addComponent() {
        if (this.components.length < 10) {
            this.components.push({
                id: this.nextId++,
                producto_id: '',
                cantidad: '',
                subcantidad: '',
                stock: '',
                name: ''
            });
            console.log('Nuevo componente agregado:', this.components);
            this.$wire.updateComponents(this.components);
        }
    },
    removeComponent(componentId) {
        if (this.components.length > 1) {
            this.components = this.components.filter(c => c.id !== componentId);
            this.$wire.updateComponents(this.components);
        }
    },
    addEditingComponent() {
        this.editingComponents.push({
            id: 'new_' + Date.now(),
            producto_id: '',
            cantidad: '',
            subcantidad: '',
            stock: '',
            cantidad_total: this.cantidadTotal
        });
    },
    removeEditingComponent(componentId) {
        this.editingComponents = this.editingComponents.filter(c => c.id !== componentId);
    }
}" class="relative mb-3">
    <button @click="open = !open" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
        {{ $showCreateForm ? 'Cerrar' : 'Crear Nuevo Producto' }}
    </button>

    <div x-show="open" @click.away="open = false" class="absolute z-10 mt-2 w-96 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
        <form wire:submit.prevent="createProducto" class="p-4">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="name">
                    Nombre
                </label>
                <input wire:model="newProducto.name" class="focus:ring shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="name" type="text" placeholder="Nombre del producto">
                @error('newProducto.name') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="empresa_id">
                    Empresa
                </label>
                <select wire:model="newProducto.empresa_id" class="focus:ring shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="empresa_id">
                    <option value="">Seleccione una empresa</option>
                    @foreach(App\Models\Empresa::all() as $empresa)
                        <option value="{{ $empresa->id }}">{{ $empresa->razon_social }}</option>
                    @endforeach
                </select>
                @error('newProducto.empresa_id') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="marca_id">
                    Marca
                </label>
                <select wire:model="newProducto.marca_id" class="focus:ring shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="marca_id">
                    <option value="">Seleccione una marca</option>
                    @foreach(App\Models\Marca::all() as $marca)
                        <option value="{{ $marca->id }}">{{ $marca->name }}</option>
                    @endforeach
                </select>
                @error('newProducto.marca_id') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="categoria_id">
                    Categoría
                </label>
                <select wire:model="newProducto.categoria_id" class="focus:ring shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="categoria_id">
                    <option value="">Seleccione una categoría</option>
                    @foreach(App\Models\Categoria::all() as $categoria)
                        <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                    @endforeach
                </select>
                @error('newProducto.categoria_id') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="f_tipo_afectacion_id">
                    Tipo de Afectación
                </label>
                <select wire:model="newProducto.f_tipo_afectacion_id" class="focus:ring shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="f_tipo_afectacion_id">
                    <option value="">Seleccione un tipo de afectación</option>
                    @foreach(App\Models\FTipoAfectacion::all() as $tipoAfectacion)
                        <option value="{{ $tipoAfectacion->id }}">{{ $tipoAfectacion->name }}</option>
                    @endforeach
                </select>
                @error('newProducto.f_tipo_afectacion_id') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="porcentaje_igv">
                    Porcentaje IGV
                </label>
                <input wire:model="newProducto.porcentaje_igv" class="focus:ring shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="porcentaje_igv" type="number" step="0.01" placeholder="Porcentaje IGV">
                @error('newProducto.porcentaje_igv') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="tipo">
                    Tipo de Producto
                </label>
                <select
                    x-model="isCompuesto"
                    wire:model="newProducto.tipo"
                    class="focus:ring shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    id="tipo">
                    <option value="estandar">Estándar</option>
                    <option value="compuesto">Compuesto</option>
                </select>
            </div>

            <div class="mb-4" x-show="isCompuesto === 'estandar'">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="cantidad">
                    Cantidad
                </label>
                <input wire:model="newProducto.cantidad"
                       class="focus:ring shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                       id="cantidad"
                       type="number"
                       placeholder="Cantidad">
                @error('newProducto.cantidad') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4" x-show="isCompuesto === 'estandar'">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="sub_cantidad">
                    Sub Cantidad
                </label>
                <input wire:model="newProducto.sub_cantidad"
                       class="focus:ring shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                       id="sub_cantidad"
                       type="number"
                       placeholder="Sub Cantidad">
                @error('newProducto.sub_cantidad') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
            </div>

            <div x-show="isCompuesto === 'compuesto'" class="border-t mt-4 pt-4">
                <h3 class="block text-gray-700 text-sm font-bold mb-2">Componentes del Producto</h3>

                <template x-for="(component, index) in components" :key="component.id">
                    <div class="mb-6">
                        <!-- Select en línea completa -->
                        <div class="mb-2 w-full">
                            <x-searchable-select-with-stock
                                :options="App\Models\Producto::where('tipo', 'estandar')->get()->map(function($producto) {
                                    return ['id' => $producto->id, 'name' => $producto->name];
                                })"
                                :wire-model="'components'"
                                :field="'component'"
                                placeholder="Buscar producto..."
                            />
                        </div>

                        <!-- Contenedor para los inputs numéricos -->
                        <div class="flex gap-4 items-center mt-2">
                            <!-- Stock -->
                            <div class="flex-1">
                                <label class="block text-gray-700 text-sm mb-1">Stock</label>
                                <input type="text"
                                    x-model="component.stock"
                                    class="w-full border rounded px-3 py-2 bg-gray-100"
                                    placeholder="Stock"
                                    readonly>
                            </div>

                            <!-- Cantidad -->
                            <div class="flex-1">
                                <label class="block text-gray-700 text-sm mb-1">Cantidad</label>
                                <input type="number"
                                    x-model="component.cantidad"
                                    @input="if(parseFloat(component.cantidad) > parseFloat(component.stock)) {
                                        component.cantidad = component.stock;
                                        alert('La cantidad no puede superar el stock disponible');
                                    }
                                    $wire.updateComponents(components)"
                                    class="w-full border rounded px-3 py-2"
                                    placeholder="Cantidad"
                                    min="1"
                                    :max="component.stock">
                            </div>

                            <!-- Subcantidad -->
                            <div class="flex-1">
                                <label class="block text-gray-700 text-sm mb-1">Subcantidad</label>
                                <input type="number"
                                    x-model="component.subcantidad"
                                    @input="$wire.updateComponents(components)"
                                    class="w-full border rounded px-3 py-2"
                                    placeholder="Subcantidad"
                                    min="0">
                            </div>

                            <!-- Botón de eliminar -->
                            <button type="button"
                                @click="removeComponent(component.id)"
                                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded text-sm transition-colors"
                                x-show="components.length > 1">
                                X
                            </button>
                        </div>

                        <!-- Línea divisoria -->
                        <div class="border-b border-gray-300 mt-4"></div>
                    </div>
                </template>

                <!-- Botón para agregar nuevo componente -->
                <button type="button"
                    @click="addComponent"
                    class="w-full bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded mt-2">
                    + Agregar Producto
                </button>

                <div class="mb-4 mt-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="cantidad_total">
                        Cantidad Total de Productos Compuestos
                    </label>
                    <input wire:model="newProducto.cantidad_total"
                           class="focus:ring shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                           id="cantidad_total"
                           type="number"
                           min="1"
                           :max="Math.min(...components.map(comp => comp.cantidad && comp.stock ? Math.floor(comp.stock / comp.cantidad) : Infinity))"
                           @input="
                               const maxPosible = Math.min(...components.map(comp => {
                                   if (!comp.cantidad || !comp.stock) return Infinity;
                                   return Math.floor(parseFloat(comp.stock) / parseFloat(comp.cantidad));
                               }));

                               if (parseFloat($event.target.value) > maxPosible) {
                                   alert('La cantidad total excede el stock disponible de uno o más componentes');
                                   $event.target.value = maxPosible;
                                   $wire.set('newProducto.cantidad_total', maxPosible);
                               }
                           "
                           placeholder="Cantidad total">
                    @error('newProducto.cantidad_total') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="tipo_unidad">
                    Tipo de Unidad
                </label>
                <select wire:model="newProducto.tipo_unidad" class="focus:ring shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="tipo_unidad">
                    <option value="NIU">NIU</option>
                    <option value="otro">Otro</option>
                </select>
                @error('newProducto.tipo_unidad') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                @if (session()->has('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">{{ session('error') }}</span>
                    </div>
                @endif
            </div>

            <div class="flex items-center justify-between">
                <button type="submit" class="focus:ring bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Crear Producto
                </button>
            </div>
        </form>
    </div>

    <div x-show="showEditComponents"
         @click.away="showEditComponents = false"
         class="absolute z-10 mt-2 w-96 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 p-4"
         x-cloak>
        <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Editar Componentes</h3>

        <template x-for="component in editingComponents" :key="component.id">
            <div class="mb-6">
                <!-- Select en línea completa -->
                <div class="mb-2 w-full" x-data="{ component: component }">
                    <x-searchable-select-with-stock
                        :options="App\Models\Producto::where('tipo', 'estandar')->get()->map(function($producto) {
                            return ['id' => $producto->id, 'name' => $producto->name];
                        })"
                        :selected="null"
                        :wire-model="'editingComponents'"
                        :field="'component'"
                        placeholder="Buscar producto..."
                    />
                </div>

                <!-- Contenedor para los inputs numéricos -->
                <div class="flex gap-4 items-center mt-2">
                    <!-- Stock -->
                    <div class="flex-1">
                        <label class="block text-gray-700 text-sm mb-1">Stock</label>
                        <input type="text"
                            x-model="component.stock"
                            class="w-full border rounded px-3 py-2 bg-gray-100"
                            placeholder="Stock"
                            readonly>
                    </div>

                    <!-- Cantidad -->
                    <div class="flex-1">
                        <label class="block text-gray-700 text-sm mb-1">Cantidad</label>
                        <input type="number"
                            x-model="component.cantidad"
                            @input="if(parseFloat(component.cantidad) > parseFloat(component.stock)) {
                                component.cantidad = component.stock;
                                alert('La cantidad no puede superar el stock disponible');
                            }"
                            class="w-full border rounded px-3 py-2"
                            placeholder="Cantidad"
                            min="1"
                            :max="component.stock">
                    </div>

                    <!-- Subcantidad -->
                    <div class="flex-1">
                        <label class="block text-gray-700 text-sm mb-1">Subcantidad</label>
                        <input type="number"
                            x-model="component.subcantidad"
                            class="w-full border rounded px-3 py-2"
                            placeholder="Subcantidad"
                            min="0">
                    </div>

                    <!-- Botón de eliminar -->
                    <button type="button"
                        @click="removeEditingComponent(component.id)"
                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded text-sm transition-colors"
                        x-show="editingComponents.length > 1">
                        X
                    </button>
                </div>

                <!-- Línea divisoria -->
                <div class="border-b border-gray-300 mt-4"></div>
            </div>
        </template>

        <button type="button"
            @click="addEditingComponent"
            class="w-full bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded mt-2">
            + Agregar Producto
        </button>

        <div class="mb-4 mt-4">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="edit_cantidad_total">
                Cantidad Total de Productos Compuestos
            </label>
            <input type="number"
                   x-model="cantidadTotal"
                   @input="
                       const maxPosible = Math.min(...editingComponents.map(comp => {
                           if (!comp.cantidad || !comp.stock) return Infinity;
                           return Math.floor(parseFloat(comp.stock) / parseFloat(comp.cantidad));
                       }));

                       if (parseFloat($event.target.value) > maxPosible) {
                           alert('La cantidad total excede el stock disponible de uno o más componentes');
                           cantidadTotal = maxPosible;
                       } else {
                           editingComponents.forEach(comp => comp.cantidad_total = cantidadTotal);
                       }
                   "
                   :max="Math.min(...editingComponents.map(comp => comp.cantidad && comp.stock ? Math.floor(comp.stock / comp.cantidad) : Infinity))"
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                   id="edit_cantidad_total"
                   min="1"
                   placeholder="Cantidad total">
        </div>

        <div class="flex justify-end gap-2 mt-4">
            <button
                @click="showEditComponents = false"
                class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                Cancelar
            </button>
            <button
                @click="$wire.updateEditingComponents({components: editingComponents, cantidadTotal: cantidadTotal})"
                class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                Guardar Cambios
            </button>
        </div>
    </div>
</div>
