<div class="space-y-6">
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-6 py-4">
            <h2 class="text-lg font-semibold text-slate-800">
                Reporte de ventas por vendedor y marca
            </h2>
        </div>

        <div class="grid gap-4 px-6 py-5 md:grid-cols-3">
            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">Desde</label>
                <input type="date" wire:model.live="desde"
                    class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">Hasta</label>
                <input type="date" wire:model.live="hasta"
                    class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700">Campo fecha</label>
                <select wire:model.live="dateField"
                    class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="f_comprobante_sunats.pedido_fecha_factuacion">Pedido fecha facturación</option>
                    <option value="f_comprobante_sunats.fechaEmision">Fecha emisión</option>
                </select>
            </div>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Desde</p>
            <p class="mt-2 text-xl font-bold text-slate-900">{{ $desde }}</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Hasta</p>
            <p class="mt-2 text-xl font-bold text-slate-900">{{ $hasta }}</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Vendedores con venta</p>
            <p class="mt-2 text-xl font-bold text-slate-900">{{ $pivot['totalVendedores'] }}</p>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-6 py-4">
            <h3 class="text-base font-semibold text-slate-800">
                Matriz de ventas
            </h3>
        </div>

        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="min-w-max border-collapse text-sm">
                    <thead>
                        <tr class="bg-slate-950 text-white">
                            <th class="border border-slate-700 px-3 py-2 text-center font-semibold">Cod</th>
                            <th class="border border-slate-700 px-3 py-2 text-left font-semibold">Nombre Prevendedor
                            </th>

                            @foreach ($pivot['marcas'] as $marca)
                                <th class="border border-slate-700 px-3 py-2 text-center font-semibold uppercase">
                                    {{ $marca['name'] }}
                                </th>
                            @endforeach

                            <th class="border border-blue-700 bg-blue-600 px-3 py-2 text-center font-semibold">
                                Total
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($pivot['filas'] as $fila)
                            <tr class="odd:bg-slate-50">
                                <td class="border border-slate-200 px-3 py-2 text-center font-medium text-slate-800">
                                    {{ $fila['cod_prevendedor'] }}
                                </td>

                                <td class="border border-slate-200 px-3 py-2 font-medium text-slate-800">
                                    {{ $fila['vendedor'] }}
                                </td>

                                @foreach ($pivot['marcas'] as $marca)
                                    @php
                                        $valor = (float) ($fila['marcas'][$marca['name']] ?? 0);
                                    @endphp

                                    <td
                                        class="border border-slate-200 px-3 py-2 text-right {{ $valor == 0 ? 'bg-slate-50 text-slate-400' : 'bg-amber-50 text-slate-800' }}">
                                        {{ number_format($valor, 2) }}
                                    </td>
                                @endforeach

                                <td
                                    class="border border-blue-200 bg-blue-50 px-3 py-2 text-right font-bold text-blue-700">
                                    {{ number_format((float) $fila['total_fila'], 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 3 + count($pivot['marcas']) }}"
                                    class="border border-slate-200 px-4 py-8 text-center text-slate-500">
                                    No hay datos para el rango seleccionado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    @if (count($pivot['filas']) > 0)
                        <tfoot>
                            <tr class="bg-slate-100 font-bold">
                                <td colspan="2" class="border border-slate-300 px-3 py-2 text-center text-slate-800">
                                    TOTAL GENERAL
                                </td>

                                @foreach ($pivot['marcas'] as $marca)
                                    <td class="border border-slate-300 px-3 py-2 text-right text-slate-900">
                                        {{ number_format((float) ($pivot['totalesColumnas'][$marca['name']] ?? 0), 2) }}
                                    </td>
                                @endforeach

                                <td
                                    class="border border-emerald-300 bg-emerald-100 px-3 py-2 text-right font-extrabold text-emerald-800">
                                    {{ number_format((float) $pivot['granTotal'], 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
