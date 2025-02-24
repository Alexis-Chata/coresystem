<div class="bg-white dark:bg-secondary-900 shadow-lg rounded-lg p-6">
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-6">
        <!-- Tipo Comprobante -->
        <div>
            <label class="block text-gray-700 dark:text-gray-300 font-semibold mb-1">Tipo Comprobante</label>
            <select
                class="w-full bg-gray-100 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg p-2 focus:ring-2 focus:ring-indigo-500">
                <option value="07">NOTA DE CRÉDITO</option>
                <option value="08">NOTA DE DÉBITO</option>
            </select>
        </div>
        <!-- Serie -->
        <div>
            <label class="block text-gray-700 dark:text-gray-300 font-semibold mb-1">Serie</label>
            <select
                class="w-full bg-gray-100 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg p-2 focus:ring-2 focus:ring-indigo-500">
                <option value="">No hay series disponibles</option>
            </select>
        </div>
        <!-- Correlativo -->
        <div>
            <label class="block text-gray-700 dark:text-gray-300 font-semibold mb-1">Correlativo</label>
            <input type="text" disabled
                class="w-full bg-gray-200 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg p-2 opacity-50" />
        </div>
        <!-- Moneda -->
        <div>
            <label class="block text-gray-700 dark:text-gray-300 font-semibold mb-1">Moneda</label>
            <select
                class="w-full bg-gray-100 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg p-2 focus:ring-2 focus:ring-indigo-500">
                <option value="PEN">Soles</option>
                <option value="USD">Dólares</option>
            </select>
        </div>
        <!-- Fecha -->
        <div>
            <label class="block text-gray-700 dark:text-gray-300 font-semibold mb-1">Fecha</label>
            <input type="date"
                class="w-full bg-gray-100 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg p-2 focus:ring-2 focus:ring-indigo-500" />
        </div>
        <!-- Motivo de la Nota de Crédito -->
        <div>
            <label class="block text-gray-700 dark:text-gray-300 font-semibold mb-1">Motivo</label>
            <select
                class="w-full bg-gray-100 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg p-2 focus:ring-2 focus:ring-indigo-500">
                <option value="01">Anulación de la operación</option>
                <option value="02">Anulación por error en el RUC</option>
                <option value="03">Corrección por error en la descripción</option>
                <option value="04">Descuento global</option>
                <option value="05">Descuento por ítem</option>
                <option value="06">Devolución total</option>
                <option value="07">Devolución por ítem</option>
                <option value="08">Bonificación</option>
                <option value="09">Disminución en el valor</option>
                <option value="10">Otros Conceptos</option>
                <option value="11">Ajustes de operaciones de exportación</option>
                <option value="12">Ajustes afectos al IVAP</option>
                <option value="13">Corrección del monto neto pendiente de pago y/o la(s) fechas...</option>
            </select>
        </div>
    </div>

    <!-- Cliente -->
    <div class="mt-6">
        <label class="block text-gray-700 dark:text-gray-300 font-semibold mb-1">Cliente</label>
        <input type="text"
            class="w-full bg-gray-100 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg p-2 focus:ring-2 focus:ring-indigo-500"
            placeholder="Buscar cliente..." />
    </div>

    <!-- Botón Nuevo Cliente -->
    <div class="mt-4 flex justify-end">
        <button class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2 rounded-lg">Nuevo
            Cliente</button>
    </div>

    <!-- Producto -->
    <div class="mt-6">
        <label class="block text-gray-700 dark:text-gray-300 font-semibold mb-1">Producto</label>
        <input type="text"
            class="w-full bg-gray-100 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg p-2 focus:ring-2 focus:ring-indigo-500"
            placeholder="Buscar producto..." />
    </div>

    <!-- Botón Agregar Item -->
    <div class="mt-4 flex justify-end">
        <button class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2 rounded-lg">Agregar
            Ítem</button>
    </div>

    <!-- Totales -->
    <div class="mt-6 flex flex-col items-end space-y-3">
        <div class="flex items-center space-x-2">
            <span class="text-gray-700 dark:text-gray-300">IGV</span>
            <input type="text" disabled
                class="w-24 bg-gray-200 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg p-2 text-right opacity-50"
                value="0.00" />
        </div>
        <div class="flex items-center space-x-2">
            <span class="text-gray-700 dark:text-gray-300">Importe Total</span>
            <input type="text" disabled
                class="w-24 bg-gray-200 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg p-2 text-right opacity-50"
                value="0.00" />
        </div>
    </div>

    <!-- Importe en Letras -->
    <div class="mt-6 p-3 bg-gray-100 dark:bg-gray-800 rounded-lg text-gray-700 dark:text-gray-300 font-semibold">
        IMPORTE EN LETRAS: CERO CON 00/100 SOLES
    </div>

    <!-- Botón Emitir Nota -->
    <div class="mt-6 flex justify-end">
        <button class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-2 rounded-lg">Emitir
            Nota</button>
    </div>
</div>
