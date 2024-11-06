@props([
    'options',
    'selected' => null,
    'wireModel' => null,
    'field' => null,
    'label' => null,
    'placeholder' => 'Buscar...',
    'isEditing' => false,
    'modelId' => null
])

<div class="relative">
    @if($label)
    <label class="block text-gray-700 text-sm font-bold mb-2" for="{{ $field }}_search">
        {{ $label }}
    </label>
    @endif
    
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
    @if(!$isEditing)
    @cliente-created.window="search = ''; selected = null; open = false"
    @endif
    class="relative">
        <div class="relative">
            <input
                type="text"
                id="{{ $field }}_search"
                x-model="search"
                @focus="open = true"
                @click.away="open = false"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                autocomplete="off"
                placeholder="{{ $placeholder }}"
            >
            <div x-show="open" 
                class="absolute z-50 w-full mt-1 bg-white rounded-md shadow-lg max-h-60 overflow-auto">
                <template x-for="option in filteredOptions()" :key="option.id">
                    <div
                        @click="selectOption(option)"
                        class="px-4 py-2 cursor-pointer hover:bg-gray-100"
                        x-text="option.name">
                    </div>
                </template>
                <div x-show="filteredOptions().length === 0" 
                    class="px-4 py-2 text-gray-500">
                    No se encontraron resultados
                </div>
            </div>
        </div>
        @error($wireModel) 
            <span class="text-red-500 text-xs italic">{{ $message }}</span> 
        @enderror
    </div>
</div>