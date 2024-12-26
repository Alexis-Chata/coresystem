<div>
    <select wire:model="impresora">
        <option value="">Seleccionar Impresora</option>
        <option>POS-80C-1</option>
        <option>POS-80C-2</option>
        <option>EPSON-TM-U220-Receipt</option>
    </select>
    @if (session()->has('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    <button wire:click="imprimir"
        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors duration-200 ease-in-out">
        imprimir </button>
</div>
