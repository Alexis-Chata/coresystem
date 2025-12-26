<div class="gap-4 grid space-y-5">

    {{-- ================= FILTROS ================= --}}
    <div class="rounded-md border border-stroke bg-white p-4 shadow-sm dark:border-strokedark dark:bg-boxdark">
        <div class="grid gap-4 md:grid-cols-4 lg:grid-cols-6 items-end">

            {{-- Desde --}}
            <div class="space-y-1">
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-300">
                    Desde
                </label>
                <input type="date"
                    class="w-full rounded-md border border-stroke bg-transparent px-2 py-1.5 text-sm
                            focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary
                            dark:border-strokedark dark:bg-boxdark-2"
                    wire:model.live="desde">
            </div>

            {{-- Hasta --}}
            <div class="space-y-1">
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-300">
                    Hasta
                </label>
                <input type="date"
                    class="w-full rounded-md border border-stroke bg-transparent px-2 py-1.5 text-sm
                            focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary
                            dark:border-strokedark dark:bg-boxdark-2"
                    wire:model.live="hasta">
            </div>

            @if ($this->isAdmin)
                {{-- Selector de vendedor --}}
                <div class="space-y-1 md:col-span-2 lg:col-span-2">
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-300">
                        Vendedor
                    </label>
                    <select
                        class="w-full rounded-md border border-stroke bg-transparent px-2 py-1.5 text-sm
                            focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary
                            dark:border-strokedark dark:bg-boxdark-2"
                        wire:model.live="vendedorFiltro">
                        <option value="ALL">Todos</option>
                        @foreach ($vendedores as $v)
                            <option value="{{ $v->cod_prevendedor }}">
                                {{ $v->vendedor }} ({{ $v->cod_prevendedor }})
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-[11px] text-gray-400">
                        * La lista muestra vendedores con ventas en el rango seleccionado.
                    </p>
                </div>

                <div class="md:col-span-1 lg:col-span-1">
                    <button type="button"
                        class="mt-4 inline-flex w-full items-center justify-center rounded-md border
                                border-stroke px-3 py-1.5 text-xs font-semibold text-gray-700
                                hover:bg-gray-50 dark:border-strokedark dark:text-gray-200
                                dark:hover:bg-boxdark-2"
                        wire:click="$set('vendedorFiltro','ALL')">
                        Ver todos
                    </button>
                </div>
            @endif
        </div>

        <div class="mt-2 flex items-center gap-2 text-[11px] text-primary" wire:loading>
            <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                </circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
            </svg>
            <span>Cargando datos…</span>
        </div>
    </div>

    {{-- ================= TARJETAS KPI ================= --}}
    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
        {{-- Total ventas --}}
        <div class="rounded-md border border-stroke bg-white p-4 shadow-sm dark:border-strokedark dark:bg-boxdark">
            <p class="text-[11px] font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                Total ventas
            </p>
            <p class="mt-1 text-xl font-semibold">
                S/ {{ number_format((float) ($kpis->total_ventas ?? 0), 2) }}
            </p>
        </div>

        {{-- Clientes únicos --}}
        <div class="rounded-md border border-stroke bg-white p-4 shadow-sm dark:border-strokedark dark:bg-boxdark">
            <p class="text-[11px] font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                Clientes únicos
            </p>
            <p class="mt-1 text-xl font-semibold">
                {{ (int) ($kpis->clientes_unicos ?? 0) }}
            </p>
        </div>

        {{-- Ticket promedio --}}
        <div class="rounded-md border border-stroke bg-white p-4 shadow-sm dark:border-strokedark dark:bg-boxdark">
            <p class="text-[11px] font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                Ticket promedio
            </p>
            <p class="mt-1 text-xl font-semibold">
                S/ {{ number_format((float) ($kpis->ticket_prom ?? 0), 2) }}
            </p>
        </div>
    </div>

    {{-- ================= TABLA RANKING (SIN MARCA) ================= --}}
    <div class="rounded-md border border-stroke bg-white shadow-sm dark:border-strokedark dark:bg-boxdark">
        <div
            class="flex items-center justify-between border-b border-stroke px-4 py-3 text-xs
                    dark:border-strokedark">
            <div class="font-semibold">
                Ranking de vendedores
            </div>
            <div class="text-[11px] text-gray-500 dark:text-gray-400">
                Rango: {{ $desde }} &rarr; {{ $hasta }}
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-[11px]">
                <thead
                    class="bg-gray-50 text-[11px] uppercase tracking-wide text-gray-500 dark:bg-boxdark-2 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-2">Cod</th>
                        <th class="px-4 py-2">Vendedor</th>
                        <th class="px-4 py-2 text-right">Ventas</th>
                        <th class="px-4 py-2 text-right">Clientes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stroke dark:divide-strokedark">
                    @forelse($ranking as $r)
                        <tr class="hover:bg-gray-50 dark:hover:bg-boxdark-2">
                            <td class="px-4 py-1.5 font-semibold text-gray-700 dark:text-gray-200">
                                {{ $r->cod_prevendedor }}
                            </td>
                            <td class="px-4 py-1.5 text-gray-800 dark:text-gray-100">
                                {{ $r->vendedor }}
                            </td>
                            <td class="px-4 py-1.5 text-right">
                                S/ {{ number_format((float) $r->total_ventas, 2) }}
                            </td>
                            <td class="px-4 py-1.5 text-right">
                                {{ (int) $r->clientes_unicos }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-gray-400">
                                No hay ventas registradas en el rango seleccionado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                @if ($ranking->count() > 0)
                    <tfoot
                        class="bg-gray-50 text-[11px] font-semibold text-gray-700 dark:bg-boxdark-2 dark:text-gray-100">
                        <tr>
                            {{-- Ahora solo Cod + Vendedor --}}
                            <td colspan="2" class="px-4 py-2 text-right">
                                Totales
                            </td>
                            <td class="px-4 py-2 text-right">
                                S/ {{ number_format((float) $ranking->sum('total_ventas'), 2) }}
                            </td>
                            <td class="px-4 py-2 text-right">
                                {{ (int) $ranking->sum('clientes_unicos') }}
                            </td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    {{-- ================= TABLA VENTAS POR VENDEDOR Y MARCA ================= --}}
    <div class="mt-5 rounded-md border border-stroke bg-white shadow-sm dark:border-strokedark dark:bg-boxdark">
        <div
            class="flex items-center justify-between border-b border-stroke px-4 py-3 text-xs
                dark:border-strokedark">
            <div class="font-semibold">
                Ventas por vendedor y marca
            </div>
            <div class="text-[11px] text-gray-500 dark:text-gray-400">
                Rango: {{ $desde }} &rarr; {{ $hasta }}
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-[11px]">
                <thead
                    class="bg-gray-50 text-[11px] uppercase tracking-wide text-gray-500 dark:bg-boxdark-2 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-2">Cod</th>
                        <th class="px-4 py-2">Vendedor</th>
                        <th class="px-4 py-2">Marca</th>
                        <th class="px-4 py-2 text-right">Ventas</th>
                        <th class="px-4 py-2 text-right">Clientes</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-stroke dark:divide-strokedark">
                    @forelse($rankingMarca as $r)
                        <tr class="hover:bg-gray-50 dark:hover:bg-boxdark-2">
                            <td class="px-4 py-1.5 font-semibold text-gray-700 dark:text-gray-200">
                                {{ $r->cod_prevendedor }}
                            </td>
                            <td class="px-4 py-1.5 text-gray-800 dark:text-gray-100">
                                {{ $r->vendedor }}
                            </td>
                            <td class="px-4 py-1.5 text-gray-800 dark:text-gray-100">
                                {{ $r->marca }}
                            </td>
                            <td class="px-4 py-1.5 text-right">
                                S/ {{ number_format((float) $r->total_ventas, 2) }}
                            </td>
                            <td class="px-4 py-1.5 text-right">
                                {{ (int) $r->clientes_unicos }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-6 text-center text-gray-400">
                                No hay ventas por marca en el rango seleccionado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                @if ($rankingMarca->count() > 0)
                    <tfoot
                        class="bg-gray-50 text-[11px] font-semibold text-gray-700 dark:bg-boxdark-2 dark:text-gray-100">
                        <tr>
                            <td colspan="3" class="px-4 py-2 text-right">
                                Totales
                            </td>
                            <td class="px-4 py-2 text-right">
                                S/ {{ number_format((float) $rankingMarca->sum('total_ventas'), 2) }}
                            </td>
                            <td class="px-4 py-2 text-right">
                                {{ (int) $rankingMarca->sum('clientes_unicos') }}
                            </td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

</div>
