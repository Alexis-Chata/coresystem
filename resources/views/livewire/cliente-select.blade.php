<div class="relative" x-data="{
    open: false,
    search: @entangle('search'),
    cursor: -1,

    selectedId: @entangle('cliente_id').live,

    options: @js($clientesOptions),

    // ✅ cuando Livewire re-renderiza (cambio vendedor), actualizamos options sin reiniciar todo
    refreshOptions() {
        this.options = @js($clientesOptions);
        // si ya no existe el seleccionado en la nueva lista, limpia
        if (this.selectedId && !this.options.find(o => o.id == this.selectedId)) {
            this.selectedId = null;
            this.search = '';
        }
    },

    get filteredOptions() {
        if (!this.search) return this.options;

        const terms = this.search.toLowerCase().split(' ').filter(t => t.trim() !== '');
        return this.options.filter(o => {
            const name = (o.name ?? '').toLowerCase();
            const id = (o.id ?? '').toString();
            return terms.every(t => name.includes(t) || id.includes(t));
        });
    },

    init() {
        this.refreshOptions();

        // si hay selectedId, asegurar que search muestre el nombre
        const initial = this.options.find(o => o.id == this.selectedId);
        if (initial && !this.search) this.search = initial.name;

        this.$watch('selectedId', (v) => {
            if (!v) return;
            const found = this.options.find(o => o.id == v);
            if (found) this.search = found.name;
        });

        Livewire.on('cliente-dropdown-open', () => {
            this.open = true;
            this.cursor = -1;
        });
        Livewire.on('cliente-dropdown-close', () => {
            this.open = false;
            this.cursor = -1;
        });
    },

    selectOption(option) {
        if (!option) return;

        this.selectedId = option.id;
        this.search = option.name;
        this.open = false;
        this.cursor = -1;

        // ✅ Solo 1 request al seleccionar
        $wire.selectCliente(option.id);
    },

    moverCursor(dir) {
        if (!this.open) { this.open = true; return; }

        const max = this.filteredOptions.length - 1;
        if (max < 0) return;

        this.cursor += dir;
        if (this.cursor < 0) this.cursor = max;
        if (this.cursor > max) this.cursor = 0;

        this.$nextTick(() => {
            const el = this.$refs.list?.querySelector(`[data-index='${this.cursor}']`);
            el?.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        });
    },

    selectByCursor() {
        if (!this.filteredOptions.length) return;
        const option = this.filteredOptions[this.cursor] ?? this.filteredOptions[0];
        this.selectOption(option);
    },

    reset() {
        this.search = '';
        this.selectedId = null;
        this.open = true;

        // ✅ 1 request al limpiar (no al escribir)
        $wire.clearSelection();
    }
}" x-effect="options = @js($clientesOptions)"
    x-effect="if (selectedId && !options.find(o => o.id == selectedId)) { selectedId = null; search = '' }"
    @click.away="open = false">
    <div class="relative">
        <input type="text" x-model="search" @focus="open = true" @keydown.arrow-down.prevent="moverCursor(1)"
            @keydown.arrow-up.prevent="moverCursor(-1)" @keydown.enter.prevent="selectByCursor()"
            @keydown.escape="open = false"
            class="block px-2.5 pb-2.5 pt-4 w-full text-sm text-gray-900 bg-transparent rounded-lg border border-gray-300 dark:text-white dark:border-gray-600 focus:ring-2 focus:ring-blue-500 peer {{ $vendedor_id ? '' : 'bg-gray-100' }}"
            placeholder=" " autocomplete="off" {{ !$vendedor_id ? 'disabled' : '' }}>

        <label
            class="pointer-events-none absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-4 scale-8 top-2 z-10 origin-[0] bg-[#f1f5f9] dark:bg-[#1A222C] px-2
                   peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500
                   peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2
                   peer-focus:top-2 peer-focus:scale-8 peer-focus:-translate-y-4 start-1">
            {{ $vendedor_id ? 'Buscar cliente...' : 'Seleccione un vendedor primero' }}
        </label>

        <button x-show="search || selectedId" x-cloak @click="reset()" type="button"
            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    @if ($vendedor_id)
        <div x-show="open" x-cloak
            class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-md shadow-xl max-h-60 overflow-y-auto"
            x-ref="list">
            <template x-for="(option, index) in filteredOptions" :key="option.id">
                <div :data-index="index" @mouseenter="cursor = index" @click="selectOption(option)"
                    :class="cursor === index ? 'bg-blue-600 text-white' :
                        'text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-600'"
                    class="px-4 py-2.5 cursor-pointer text-sm flex justify-between items-center transition-colors">
                    <div class="min-w-0">
                        <span class="opacity-80" x-text="'#' + option.id"></span>
                        <span class="font-medium" x-text="' - ' + option.name"></span>
                        <span class="opacity-80" x-text="option.listaPrecio ? (' - ' + option.listaPrecio) : ''"></span>

                        <template x-if="option.marcas && option.marcas.length">
                            <span class="inline-flex items-center gap-1 ml-2 align-middle">
                                <template x-for="(m, mi) in option.marcas.slice(0,4)" :key="m.id">
                                    <span class="w-2.5 h-2.5 rounded-full border border-gray-300"
                                        :style="`background-color:${m.color}`" :title="m.name"></span>
                                </template>

                                <template x-if="option.marcas.length > 4">
                                    <span class="text-xs opacity-70" x-text="'+' + (option.marcas.length - 4)"></span>
                                </template>
                            </span>
                        </template>
                    </div>

                    <svg x-show="selectedId == option.id" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                    </svg>
                </div>
            </template>

            <div x-show="filteredOptions.length === 0" class="px-4 py-3 text-sm text-gray-500 italic">
                No hay resultados para "<span x-text="search"></span>"
            </div>
        </div>
    @endif

    @error('cliente_id')
        <span class="text-red-500 text-xs italic">{{ $message }}</span>
    @enderror
</div>
