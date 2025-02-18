<div class="flex flex-col sm:flex-row items-center gap-2 sm:gap-4 mb-5.5 pt-2 text-[8px] text-sm">
    <button wire:click="report_pedido_detalle"
        class="px-4 py-2 bg-green-600 hover:bg-green-800 text-white rounded-lg transition-colors duration-200 ease-in-out">Reporte
        Pedido Detalle</button>
    <div class="relative w-full sm:w-auto">
        <input type="date" wire:model.live="startDate"
            class="block w-full px-2.5 pb-2.5 pt-4 text-sm text-gray-900 bg-transparent rounded-lg border border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer">
        <label
            class="absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-4 scale-8 top-2 z-10 origin-[0] bg-white dark:bg-[#1A222C] px-2 peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-8 peer-focus:-translate-y-4 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto start-1">
            Fecha Inicio
        </label>
    </div>
    <div class="relative w-full sm:w-auto">
        <input type="date" wire:model.live="endDate"
            class="block w-full px-2.5 pb-2.5 pt-4 text-sm text-gray-900 bg-transparent rounded-lg border border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer">
        <label
            class="absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-4 scale-8 top-2 z-10 origin-[0] bg-white dark:bg-[#1A222C] px-2 peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-8 peer-focus:-translate-y-4 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto start-1">
            Fecha Fin
        </label>
    </div>
    <div class="w-full sm:w-auto">
        <button wire:click="exportarPedidosPDF"
            class="w-full sm:w-auto px-4 py-2 bg-red-500 hover:bg-red-700 text-white rounded-lg transition-colors duration-200 ease-in-out">
            Asignando Progamacion Carga PDF
        </button>
    </div>
    <button wire:click="cerrar_sessiones"
        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors duration-200 ease-in-out">Cerrar
        Sessiones</button>
    <button wire:click="permiso_crear_pedido"
        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors duration-200 ease-in-out">Devolver
        Permisos</button>
</div>
