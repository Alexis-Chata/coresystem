<div>
    {{-- In work, do what you enjoy. --}}
    <div class="flex items-center gap-4 mb-4">
        <div class="relative w-72 mb-3">
            <input type="date" wire:model.live="fecha_reparto"
                class="block px-2.5 pb-2.5 pt-4 text-sm text-gray-900 bg-transparent rounded-lg border border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer">
            <label
                class="absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-4 scale-8 top-2 z-10 origin-[0] bg-white dark:bg-[#1A222C] px-2 peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-8 peer-focus:-translate-y-4 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto start-1">
                Fecha de Reparto
            </label>

        </div>
        <div>

            <button wire:click="generar_movimiento"
                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors duration-200 ease-in-out">
                <span class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd"></path>
                    </svg>
                    Generar Movimiento
                </span>
            </button>
        </div>
        <div>
            @error('fecha_reparto')
                {{ $message }}
            @enderror
        </div>
        <div>
            @error('checkbox_conductor_seleccionados')
                {{ $message }}
            @enderror
        </div>
    </div>
    <div class="container mx-auto">
        <h1 class="text-2xl font-bold text-gray-700 mb-4">Pedidos Por Generar Movimiento Carga</h1>
        <div class="overflow-x-auto">
            <table class="table-auto w-full border-collapse border border-gray-200 shadow-md">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-gray-600 font-semibold border border-gray-300">...</th>
                        <th class="px-4 py-2 text-left text-gray-600 font-semibold border border-gray-300">Reparto</th>
                        <th class="px-4 py-2 text-left text-gray-600 font-semibold border border-gray-300"># Clientes
                        </th>
                        <th class="px-4 py-2 text-left text-gray-600 font-semibold border border-gray-300">Importe</th>
                        <th class="px-4 py-2 text-left text-gray-600 font-semibold border border-gray-300">F.Reparto
                        </th>
                        <th class="px-4 py-2 text-left text-gray-600 font-semibold border border-gray-300">F.Liquidacion
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pedidosAgrupados as $pedidosAgrupado)
                        <tr class="bg-gray-50 hover:bg-gray-100">
                            <td class="px-4 py-2 border border-gray-300">
                                <input type="checkbox" value="{{ $pedidosAgrupado->conductor_id }}"
                                    wire:loading.attr="disabled" wire:target="generar_movimiento"
                                    wire:model="checkbox_conductor_seleccionados">
                            </td>
                            <td class="px-4 py-2 border border-gray-300">{{ $pedidosAgrupado->conductor_id }} -
                                {{ $conductores->find($pedidosAgrupado->conductor_id)->name }}</td>
                            <td class="px-4 py-2 border border-gray-300">{{ $pedidosAgrupado->total_clientes }}</td>
                            <td class="px-4 py-2 border border-gray-300">
                                {{ number_format($pedidosAgrupado->total_importe, 2) }}</td>
                            <td class="px-4 py-2 border border-gray-300">{{ $fecha_reparto }}</td>
                            <td class="px-4 py-2 border border-gray-300">{{ $fecha_liquidacion }}</td>
                        </tr>
                    @empty
                        <tr class="bg-white hover:bg-gray-100">
                            <td class="px-4 py-2 border border-gray-300" colspan="100%">... Sin Pedidos Asignados ...
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="container mx-auto">
        <br>
        <br>
        <h1 class="text-2xl font-bold text-gray-700 mb-4">Movimiento Carga Generadas</h1>
        <div class="overflow-x-auto">
            <table class="table-auto w-full border-collapse border border-gray-200 shadow-md">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-gray-600 font-semibold border border-gray-300">...</th>
                        <th class="px-4 py-2 text-left text-gray-600 font-semibold border border-gray-300">Conductor</th>
                        <th class="px-4 py-2 text-left text-gray-600 font-semibold border border-gray-300">Vehiculo
                        </th>
                        <th class="px-4 py-2 text-left text-gray-600 font-semibold border border-gray-300">Almacen</th>
                        <th class="px-4 py-2 text-left text-gray-600 font-semibold border border-gray-300">F.Reparto
                        </th>
                        <th class="px-4 py-2 text-left text-gray-600 font-semibold border border-gray-300">F.Liquidacion
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($cargas_generadas as $cargas_generada)
                        <tr class="bg-gray-50 hover:bg-gray-100">
                            <td class="px-4 py-2 border border-gray-300">
                                <button wire:click="exportarMovimientoCargaPDF"
                                    class="px-4 py-2 bg-red-500 hover:bg-red-700 text-white rounded-lg transition-colors duration-200 ease-in-out">
                                    <span class="flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                            fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                        PDF
                                    </span>
                                </button>
                            </td>
                            <td class="px-4 py-2 border border-gray-300">{{ $cargas_generada->conductor_id }} -
                                {{ $cargas_generada->conductor->name }}</td>
                            <td class="px-4 py-2 border border-gray-300">{{ $cargas_generada->vehiculo->id }}-{{ $cargas_generada->vehiculo->modelo }}-{{ $cargas_generada->vehiculo->placa }}</td>
                            <td class="px-4 py-2 border border-gray-300">
                                {{ $cargas_generada->almacen->name }}</td>
                            <td class="px-4 py-2 border border-gray-300">{{ $fecha_reparto }}</td>
                            <td class="px-4 py-2 border border-gray-300">{{ $fecha_liquidacion }}</td>
                        </tr>
                    @empty
                        <tr class="bg-white hover:bg-gray-100">
                            <td class="px-4 py-2 border border-gray-300" colspan="100%">... Sin Pedidos Asignados ...
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
