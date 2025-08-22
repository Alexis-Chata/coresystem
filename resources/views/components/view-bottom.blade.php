<div>
    <br />
    <br />
    <br />
    <table class="min-w-full divide-y divide-gray-200 border mt-6">
        <thead class="bg-gray-100">
            <tr>
                <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Dia Reparto</th>
                <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">ID - Conductor</th>
                <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">ID - Ruta</th>
                <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">ID - Vendedor</th>
                <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">
                    Action *
                    <button wire:click="asignacion_rapida"
                        wire:loading.attr="disabled" wire:target="asignacion_rapida, asignacion_sugerida"
                        class="px-2 py-1 bg-gray-500 hover:bg-gray-700 text-white rounded-lg transition-colors duration-200 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed">
                        <span class="flex items-center gap-2">
                            Asignar
                        </span>
                        {{-- Texto de carga --}}
                        <span wire:loading wire:target="asignacion_rapida, asignacion_sugerida">Procesando...</span>
                    </button>
                </th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($pedidosUltimoMes as $pedido)
                <tr>
                    <td class="px-4 py-2 text-sm text-gray-800">{{ $pedido->dia_semana }}</td>
                    <td class="px-4 py-2 text-sm text-gray-800">{{ $pedido->conductor_id }} -
                        {{ $pedido->conductor_nombre }}</td>
                    <td class="px-4 py-2 text-sm text-gray-800">{{ $pedido->ruta_id }} - {{ $pedido->ruta_nombre }}</td>
                    <td class="px-4 py-2 text-sm text-gray-800">{{ $pedido->vendedor_id }} -
                        {{ $pedido->vendedor_nombre }}</td>
                    <td class="px-4 py-2 text-sm text-gray-800 font-bold">
                        <button wire:click="asignacion_sugerida({{ $pedido->conductor_id }}, {{ $pedido->ruta_id }})"
                            wire:loading.attr="disabled" wire:target="asignacion_sugerida, asignacion_rapida"
                            class="px-3 py-2 bg-gray-500 hover:bg-gray-700 text-white rounded-lg transition-colors duration-200 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed">

                            <span class="flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd"></path>
                                </svg>
                                Asignar
                            </span>

                            {{-- Texto de carga --}}
                            <span wire:loading wire:target="asignacion_sugerida, asignacion_rapida">Procesando...</span>
                        </button>
                        @error('fecha_reparto')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-2 text-center text-gray-500">No hay pedidos en el último mes</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
