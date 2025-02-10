<div>
    <div id="data-table">
        <input type="date" wire:model="fecha_emision" wire:change="actualizar_table" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring focus:ring-indigo-200 focus:border-indigo-500 transition-colors duration-200 ease-in-out">
    </div>
    <div wire:loading wire:target="actualizar_table">
        Cargando...
    </div>
</div>
