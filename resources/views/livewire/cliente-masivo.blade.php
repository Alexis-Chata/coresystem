<div>
    {{-- Formulario: solo se muestra cuando NO se está importando --}}
    <div wire:loading.remove wire:target="importar">
        <form wire:submit.prevent="importar">
            <input type="file" wire:model="archivo" accept=".xlsx,.csv" />

            @error('archivo')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror

            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded mt-2" wire:loading.attr="disabled">
                Subir clientes
            </button>
        </form>
    </div>

    {{-- Indicador de carga: solo se muestra mientras se importa --}}
    <div wire:loading wire:target="importar"
        class="flex flex-col items-center justify-center mt-4 text-blue-600 font-semibold space-y-2">
        <svg class="animate-spin h-8 w-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none"
            viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
            </circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
        </svg>
        <span>⏳ Importando clientes... por favor espera.</span>
    </div>

    {{-- Mensaje de éxito --}}
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="mt-4 text-green-600 font-semibold">
            ✅ {{ session('message') }}
        </div>
    @endif
</div>
