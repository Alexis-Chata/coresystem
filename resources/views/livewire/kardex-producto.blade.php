<div class="space-y-3">

    <div class="flex flex-wrap gap-2 items-end">
        <div>
            <label class="text-xs text-gray-600">Producto</label>
            <select wire:model="productoId" class="border rounded px-2 py-1">
                <option value="">-- TODOS --</option>
                @foreach ($productosList as $id => $name)
                    <option value="{{ $id }}">{{ $id }} - {{ $name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="text-xs text-gray-600">Inicio</label>
            <input type="date" wire:model="fechaInicio" class="border rounded px-2 py-1" />
        </div>

        <div>
            <label class="text-xs text-gray-600">Fin</label>
            <input type="date" wire:model="fechaFin" class="border rounded px-2 py-1" />
        </div>

        <button type="button" wire:click="cargarKardex" wire:loading.attr="disabled" wire:target="cargarKardex"
            class="bg-blue-600 text-white px-3 py-1 rounded cursor-pointer disabled:opacity-60">
            <span wire:loading.remove wire:target="cargarKardex">Filtrar</span>
            <span wire:loading wire:target="cargarKardex">Cargando...</span>
        </button>

    </div>

    <div wire:loading wire:target="cargarKardex" class="text-sm text-gray-600 mt-2">
        Cargando...
    </div>

    <div wire:loading.remove wire:target="cargarKardex" class="space-y-4">

        {{-- ✅ ENCABEZADO --}}
        <div class="pb-2">
            <h2 class="text-lg font-bold">Kardex</h2>
            <div class="text-sm text-gray-600">
                Período: {{ $fechaInicio }} a {{ $fechaFin }}
            </div>
        </div>

        {{-- ✅ RESUMEN POR PRODUCTO --}}
        <div class="border rounded p-2 pb-6">
            <div class="font-semibold mb-2">Resumen por producto</div>
            <div class="overflow-auto">
                <table class="min-w-full text-sm border border-gray-300" id="dataTable_example">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border px-2 py-1">Cod</th>
                            <th class="border px-2 py-1">Producto</th>
                            <th class="border px-2 py-1 text-right">Saldo inicial</th>
                            <th class="border px-2 py-1 text-right">Saldo final</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($resumenProductos as $r)
                            <tr>
                                <td class="border px-2 py-1">{{ $r['producto_id'] }}</td>
                                <td class="border px-2 py-1">{{ $r['producto_nombre'] }}</td>
                                <td class="border px-2 py-1 text-right">{{ $r['saldo_inicial'] }}
                                    ({{ $r['saldo_inicial_cajas'] }})
                                </td>
                                <td class="border px-2 py-1 text-right font-semibold">{{ $r['saldo_final'] }}
                                    ({{ $r['saldo_final_cajas'] }})</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-3 text-gray-500">Sin datos</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ✅ DETALLE DE MOVIMIENTOS --}}
        <table class="min-w-full text-sm border border-gray-300">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border px-2 py-1">Fecha</th>
                    <th class="border px-2 py-1">Cod. Producto</th>
                    <th class="border px-2 py-1">Producto</th>
                    <th class="border px-2 py-1">Código</th>
                    <th class="border px-2 py-1">Movimiento</th>
                    <th class="border px-2 py-1">Tipo</th>
                    <th class="border px-2 py-1 text-right">Cantidad</th>
                    <th class="border px-2 py-1 text-right">Saldo antes</th>
                    <th class="border px-2 py-1 text-right">Saldo después</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($kardex as $row)
                    <tr>
                        <td class="border px-2 py-1">{{ $row['fecha'] }}</td>
                        <td class="border px-2 py-1">{{ $row['producto_codigo'] ?? '-' }}</td>
                        <td class="border px-2 py-1">{{ $row['producto_nombre'] ?? '-' }}</td>
                        <td class="border px-2 py-1">{{ $row['codigo'] }}</td>
                        <td class="border px-2 py-1">{{ $row['movimiento'] }}</td>
                        <td class="border px-2 py-1 font-semibold">{{ $row['tipo'] }}</td>
                        <td class="border px-2 py-1 text-right">{{ $row['cantidad'] }}</td>
                        <td class="border px-2 py-1 text-right">{{ $row['saldo_antes'] }}
                            ({{ $row['saldo_antes_cajas'] }})
                        </td>
                        <td class="border px-2 py-1 text-right font-semibold">{{ $row['saldo_despues'] }}
                            ({{ $row['saldo_despues_cajas'] }})</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-3 text-gray-500">
                            No hay movimientos en el período
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@script
    <script>
        $wire.on('DataTable-initialize', () => {
            requestAnimationFrame(() => {
                const table = document.querySelector('#dataTable_example');
                if (table) {
                    console.log("✅ DataTable ready to initialize");
                    new DataTable('#dataTable_example');

                    queueMicrotask(() => {
                        Alpine.initTree(document.getElementById('dataTable_example'));
                    });
                } else {
                    console.warn("❌ Tabla no encontrada al momento de inicializar");
                }
            });
        });
    </script>
@endscript
