@props([
    'options',
    'selected' => null,
    'wireModel' => null,
    'field' => null,
    'placeholder' => 'Buscar...',
    'isEditing' => false,
    'modelId' => null
])

<div x-data="{
    open: false,
    search: '',
    selected: {{ $selected ? json_encode($options->firstWhere('id', $selected)) : 'null' }},
    options: {{ $options->toJson() }},
    filteredOptions() {
        return this.options.filter(option => 
            option.name.toLowerCase().includes(this.search.toLowerCase())
        );
    },
    selectOption(option) {
        this.selected = option;
        this.search = option.name;
        @if($isEditing)
            $wire.updateField('{{ $field }}', option.id, {{ $modelId }});
        @else
            $wire.set('{{ $wireModel }}', option.id);
        @endif
        this.open = false;
    },
    init() {
        if (this.selected) {
            this.search = this.selected.name;
        }
        @if(!$isEditing)
        this.$watch('search', value => {
            if (!value) {
                this.selected = null;
                @if($wireModel)
                    $wire.set('{{ $wireModel }}', null);
                @endif
            }
        });
        @endif
    }
}" 
class="relative">
    <div class="relative">
        <input
            type="text"
            x-model="search"
            @focus="open = true"
            @click.away="open = false"
            class="block px-2.5 pb-2.5 pt-4 w-full text-sm text-gray-900 bg-transparent rounded-lg border border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer"
            placeholder=" "
        >
        <label class="pointer-events-none absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-4 scale-8 top-2 z-10 origin-[0] bg-[#f1f5f9] dark:bg-[#1A222C] px-2 peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-8 peer-focus:-translate-y-4 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto start-1">
            {{ $placeholder }}
        </label>

        <button 
            x-show="search"
            @click="search = ''; selected = null; open = true; $wire.set('{{ $wireModel }}', null)"
            class="absolute right-0 top-0 mt-3 mr-2 text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 transition-colors duration-200"
            type="button"
        >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <div x-show="open" x-cloak
        class="absolute z-50 w-full mt-1 bg-white rounded-md shadow-lg max-h-60 overflow-auto">
        <template x-for="option in filteredOptions()" :key="option.id">
            <div
                @click="selectOption(option)"
                class="px-4 py-2 cursor-pointer hover:bg-gray-100"
                x-text="option.name"
            ></div>
        </template>
        <div x-show="filteredOptions().length === 0" class="px-4 py-2 text-gray-500">
            No se encontraron resultados
        </div>
    </div>
</div>
