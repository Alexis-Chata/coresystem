<div>
    {{-- In work, do what you enjoy. --}}
    <div class="flex items-center gap-4 mb-4">
        <div class="relative w-72">
            <input type="date" wire:model.live="fecha_reparto"
                class="block px-2.5 pb-2.5 pt-4 text-sm text-gray-900 bg-transparent rounded-lg border border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer">
            <label
                class="absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-4 scale-8 top-2 z-10 origin-[0] bg-white dark:bg-[#1A222C] px-2 peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-8 peer-focus:-translate-y-4 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto start-1">
                Fecha de Reparto
            </label>

        </div>
        <div class="relative w-72">
            <select wire:model="serie_factura_seleccionada"
                class="cursor-pointer block px-2.5 pb-2.5 pt-4 w-full text-sm text-gray-900 bg-transparent rounded-lg border border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer">
                <option value="">Seleccione un Serie</option>';

                @foreach ($serie_facturas as $serie_factura)
                    <option value="{{ $serie_factura->id }}">{{ $serie_factura->serie }}</option>
                @endforeach
            </select>
            <label
                class="absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-4 scale-8 top-2 z-10 origin-[0] bg-white dark:bg-[#1A222C] px-2 peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-8 peer-focus:-translate-y-4 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto start-1">
                Serie Factura
            </label>
        </div>
        <div class="relative w-72">
            <select wire:model="serie_boleta_seleccionada"
                class="cursor-pointer block px-2.5 pb-2.5 pt-4 w-full text-sm text-gray-900 bg-transparent rounded-lg border border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer">
                <option value="">Seleccione un Serie</option>';

                @foreach ($serie_boletas as $serie_boleta)
                    <option value="{{ $serie_boleta->id }}">{{ $serie_boleta->serie }}</option>
                @endforeach
            </select>
            <label
                class="absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-4 scale-8 top-2 z-10 origin-[0] bg-white dark:bg-[#1A222C] px-2 peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-8 peer-focus:-translate-y-4 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto start-1">
                Serie Boleta
            </label>
        </div>
        <div class="relative w-72">
            <select wire:model="serie_nota_pedido_seleccionada"
                class="cursor-pointer block px-2.5 pb-2.5 pt-4 w-full text-sm text-gray-900 bg-transparent rounded-lg border border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer">
                <option value="">Seleccione un Serie</option>';

                @foreach ($serie_nota_pedidos as $serie_nota_pedido)
                    <option value="{{ $serie_nota_pedido->id }}">{{ $serie_nota_pedido->serie }}</option>
                @endforeach
            </select>
            <label
                class="absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-4 scale-8 top-2 z-10 origin-[0] bg-white dark:bg-[#1A222C] px-2 peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-8 peer-focus:-translate-y-4 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto start-1">
                Serie Nt.Pedido
            </label>
        </div>
        <div>
            <button wire:click="generar_comprobantes"
                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors duration-200 ease-in-out">
                <span class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd"></path>
                    </svg>
                    Generar Comprobantes
                </span>
            </button>
        </div>
        <div>
            @error('fecha_reparto')
                {{ $message }}
            @enderror
        </div>
        <div>
            @error('serie_factura_seleccionada')
                {{ $message }}
            @enderror
        </div>
        <div>
            @error('serie_boleta_seleccionada')
                {{ $message }}
            @enderror
        </div>
        <div>
            @error('serie_nota_pedido_seleccionada')
                {{ $message }}
            @enderror
        </div>
        <div>
            @error('checkboxValues')
                {{ $message }}
            @enderror
        </div>
    </div>
</div>
