@props([
    'options',
    'selected' => null,
    'wireModel' => null,
    'field' => null,
    'placeholder' => 'Buscar...',
    'isEditing' => false,
    'modelId' => null,
    'triggerAction' => null, // 1. NUEVA PROPIEDAD: Nombre del método a ejecutar
])

<div x-data="{
    open: false,
    search: '',
    cursor: -1,
    selectedId: @entangle($wireModel),
    options: {{ $options->toJson() }},

    get filteredOptions() {
        if (!this.search) return this.options;
        const terms = this.search.toLowerCase().split(' ').filter(t => t.trim() !== '');
        return this.options.filter(option => {
            const name = option.name.toLowerCase();
            const id = option.id.toString();
            return terms.every(term => name.includes(term) || id.includes(term));
        });
    },

    init() {
        let initial = this.options.find(o => o.id == this.selectedId);
        if (initial) this.search = initial.name;

        Livewire.on('reset-select-{{ $field }}', () => {
            this.search = '';
            this.selectedId = null;
        });
    },

    selectOption(option) {
        if (!option) return;
        this.selectedId = option.id;
        this.search = option.name;
        this.open = false;
        this.cursor = -1;

        @if($isEditing)
        // Lógica de edición en línea (fila de tabla)
        $wire.updateField('{{ $field }}', option.id, {{ $modelId }});
        @else
            // 2. Lógica de selección estándar
            @if($triggerAction)
            // CASO A: Si definiste una acción, llama al método en PHP
            // Ejemplo: public function seleccionarVendedor($id) { ... }
            $wire.call('{{ $triggerAction }}', option.id);
            @else
            // CASO B: Si no, solo actualiza la variable (comportamiento normal)
            $wire.set('{{ $wireModel }}', option.id);
            @endif
        @endif
    },

    moverCursor(direction) {
        if (!this.open) { this.open = true; return; }

        let max = this.filteredOptions.length - 1;
        this.cursor += direction;

        // Lógica de rotación (ciclo infinito)
        if (this.cursor < 0) this.cursor = max;
        if (this.cursor > max) this.cursor = 0;

        this.$nextTick(() => {
            const el = this.$refs.list.querySelector(`[data-index='${this.cursor}']`);
            if (el) {
                el.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            }
        });
    },

    reset() {
        this.search = '';
        this.selectedId = null;
        this.open = true;
    }
}" class="relative" @click.away="open = false">

    <div class="relative">
        <input type="text" x-model="search" @focus="open = true" @keydown.arrow-down.prevent="moverCursor(1)"
            @keydown.arrow-up.prevent="moverCursor(-1)" @keydown.enter.prevent="selectOption(filteredOptions[cursor])"
            @keydown.escape="open = false"
            class="block px-2.5 pb-2.5 pt-4 w-full text-sm text-gray-900 bg-transparent rounded-lg border border-gray-300 dark:text-white dark:border-gray-600 focus:ring-2 focus:ring-blue-500 peer"
            placeholder=" " autocomplete="off">

        <label class="pointer-events-none absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-4 scale-8 top-2 z-10 origin-[0] bg-[#f1f5f9] dark:bg-[#1A222C] px-2 peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-8 peer-focus:-translate-y-4 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto start-1">
            {{ $placeholder }}
        </label>

        <button x-show="search || selectedId" @click="reset" type="button"
            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 transform scale-95"
        class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-md shadow-xl max-h-60 overflow-y-auto"
        x-ref="list">

        <template x-for="(option, index) in filteredOptions" :key="option.id">
            <div :data-index="index" @click="selectOption(option)" @mouseenter="cursor = index"
                :class="{
                    'bg-blue-600 text-white': index === cursor,
                    'text-gray-900 dark:text-white': index !== cursor
                }"
                class="px-4 py-2.5 cursor-pointer text-sm flex justify-between items-center transition-colors scroll-m-2">

                <div>
                    <span class="font-medium" x-text="option.name"></span>
                    <span class="text-xs opacity-70" x-text="' - COD: ' + option.id"></span>
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
</div>
