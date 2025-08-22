<div>
    <div class="flex flex-wrap justify-center sm:flex-row items-center gap-2 sm:gap-4 mb-5.5 pt-2 text-[8px] text-sm">
        <button wire:click="report_pedido_detalle"
            class="px-3 py-2 bg-green-600 hover:bg-green-800 text-white rounded-lg transition-colors duration-200 ease-in-out">Reporte
            Pedido Detalle</button>
        <div class="relative sm:w-auto">
            <input type="date" wire:model.live="startDate"
                class="block px-2 pb-2.5 pt-4 text-sm text-gray-900 bg-transparent rounded-lg border border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer">
            <label
                class="absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-4 scale-8 top-2 z-10 origin-[0] bg-white dark:bg-[#1A222C] px-2 peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-8 peer-focus:-translate-y-4 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto start-1">
                Fecha Inicio
            </label>
        </div>
        <div class="relative sm:w-auto">
            <input type="date" wire:model.live="endDate"
                class="block px-2 pb-2.5 pt-4 text-sm text-gray-900 bg-transparent rounded-lg border border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer">
            <label
                class="absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-4 scale-8 top-2 z-10 origin-[0] bg-white dark:bg-[#1A222C] px-2 peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-8 peer-focus:-translate-y-4 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto start-1">
                Fecha Fin
            </label>
        </div>
        <button wire:click="exportarPedidosPDF"
            class="sm:w-auto px-3 py-2 bg-red-500 hover:bg-red-700 text-white rounded-lg transition-colors duration-200 ease-in-out">
            Progamacion Carga PDF
        </button>
        <button wire:click="cerrar_sessiones" wire:confirm="¿Está seguro de Cerrar las sesiones?"
            class="px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors duration-200 ease-in-out">Cerrar
            Sessiones</button>
        <button wire:click="permiso_crear_pedido"
            class="px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors duration-200 ease-in-out">Devolver
            Permisos</button>
    </div>
    <div>
        <div class="flex flex-wrap items-center gap-4">
            <div class="relative w-auto">
                <select id="conductor" wire:model="selectedConductor"
                    class="cursor-pointer block px-2.5 pb-2.5 pt-4 w-full text-sm text-gray-900 bg-transparent rounded-lg border border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer">
                    <option value="">Seleccione un conductor</option>
                    @foreach ($conductores as $conductor)
                        <option value="{{ $conductor['id'] }}">{{ $conductor['name'] }}</option>
                    @endforeach
                    <option value="null">Desasignar Conductor</option>
                </select>
                <label for="conductor"
                    class="absolute leading-normal text-[8px] sm:text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-4 scale-8 top-2 z-10 origin-[0] bg-white dark:bg-[#1A222C] px-2 peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-8 peer-focus:-translate-y-4 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto start-1">
                    Asignar Conductor
                </label>
            </div>
            <div class="relative w-auto">
                <input type="date" wire:model="fecha_reparto"
                    class="block px-2.5 pb-2.5 pt-4 text-sm text-gray-900 bg-transparent rounded-lg border border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer">
                <label
                    class="absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-4 scale-8 top-2 z-10 origin-[0] bg-white dark:bg-[#1A222C] px-2 peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-8 peer-focus:-translate-y-4 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto start-1">
                    Fecha de Reparto
                </label>
            </div>
            <button wire:click="asignarConductorASeleccionados"
                class="px-3 py-2 bg-gray-500 hover:bg-gray-700 text-white rounded-lg transition-colors duration-200 ease-in-out">
                <span class="flex items-center gap-2"> <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                        viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd"></path>
                    </svg> Asignar
                </span>
            </button>
            <div class="flex flex-wrap gap-2">
                @foreach ($pedidosFueraRango as $fuera)
                    <span
                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                        {{ $fuera->fecha }}
                        <span
                            class="ml-2 inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold leading-none text-white bg-red-600 rounded-full">
                            {{ $fuera->total }}
                        </span>
                    </span>
                @endforeach
            </div>
        </div>
    </div>
</div>
