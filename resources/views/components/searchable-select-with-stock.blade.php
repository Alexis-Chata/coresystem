@props([
    'options',
    'selected' => null,
    'wireModel' => null,
    'field' => null,
    'label' => null,
    'placeholder' => 'Buscar...'
])

<div class="relative" 
    x-data="{ 
        open: false,
        search: '',
        selected: null,
        options: {{ $options->toJson() }},
        currentComponent: null,
        init() {
            // Obtener el componente actual del contexto
            this.currentComponent = Alpine.$data(this.$root.parentElement).component;
            
            // Inicializar el valor seleccionado usando el componente actual
            if (this.currentComponent?.producto_id) {
                const selectedOption = this.options.find(opt => opt.id === this.currentComponent.producto_id);
                if (selectedOption) {
                    this.selected = selectedOption;
                    this.search = selectedOption.name;
                }
            }
        },
        filteredOptions() {
            return this.options.filter(option => 
                option.name.toLowerCase().includes(this.search.toLowerCase())
            );
        },
        selectOption(option) {
            this.selected = option;
            this.search = option.name;
            
            // Actualizar el componente actual
            if (this.currentComponent) {
                const previousStock = this.currentComponent.stock;
                const previousCantidad = this.currentComponent.cantidad;
                
                this.currentComponent.producto_id = option.id;
                this.currentComponent.name = option.name;
                
                // Obtener el stock y validar cantidad
                $wire.getProductoStock(option.id, this.currentComponent.id).then(() => {
                    if (this.currentComponent.cantidad > this.currentComponent.stock) {
                        alert(`La cantidad actual (${this.currentComponent.cantidad}) excede el stock disponible del nuevo producto (${this.currentComponent.stock}). Se ajustará automáticamente.`);
                        this.currentComponent.cantidad = this.currentComponent.stock;
                    }
                });
            }
            
            this.open = false;
        }
    }">
    
    @if($label)
        <label class="block text-gray-700 text-sm font-bold mb-2" x-bind:for="'component_' + currentComponent?.id + '_search'">
            {{ $label }}
        </label>
    @endif

    <div class="relative">
        <input
            type="text"
            x-bind:id="'component_' + currentComponent?.id + '_search'"
            x-model="search"
            @focus="open = true"
            @click.away="open = false"
            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            autocomplete="off"
            placeholder="{{ $placeholder }}"
        >
        
        <div x-show="open" 
            class="absolute z-50 w-full mt-1 bg-white rounded-md shadow-lg max-h-60 overflow-auto">
            <template x-for="opt in filteredOptions()" :key="opt.id">
                <div
                    @click="selectOption(opt)"
                    class="px-4 py-2 cursor-pointer hover:bg-gray-100"
                    x-text="opt.name">
                </div>
            </template>
            <div x-show="filteredOptions().length === 0" 
                class="px-4 py-2 text-gray-500">
                No se encontraron resultados
            </div>
        </div>
    </div>

    <!-- Escuchar el evento de actualización de stock -->
    <div x-init="
        window.addEventListener('update-component-stock', (event) => {
            if (currentComponent && event.detail[0].componentId == currentComponent.id) {
                currentComponent.stock = event.detail[0].stock;
            }
        })
    "></div>
</div>