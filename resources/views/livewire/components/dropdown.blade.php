<div x-data="{ open: false }" class="relative inline-block text-left">
    <button @click="open = !open"
        class="inline-flex justify-center items-center w-full rounded-md border border-gray-300 shadow-sm px-2 py-1 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2">
        @if ($tipo_doc === '00')
            <x-svg_information />
        @else
            @if ($codigo_sunat === '0')
                <x-svg_check />
            @else
                <x-svg_advert />
            @endif
        @endif
        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd"
                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 011.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                clip-rule="evenodd" />
        </svg>
    </button>

    <div x-show="open" @click.outside="open = false"
        class="origin-top-left absolute left-0 z-1 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
        <div class="py-1">
            <a href="#" wire:click.prevent="pdf({{ $id }})"
                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><span>Pdf</span>
                <x-svg_pdf_cpe />
            </a>
            <a href="#" wire:click.prevent="xml({{ $id }})"
                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><span>Xml</span>
                <x-svg_xml_cpe />
            </a>
            <a href="#" wire:click.prevent="cdr({{ $id }})"
                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><span>Cdr</span>
                @if ($codigo_sunat === '0')
                    <x-svg_cdr_cpe />
                @else
                    <x-svg_get_cdr />
                @endif
            </a>
            <a href="#" wire:click.prevent="sunatResponse({{ $id }})"
                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><span>Sunat</span>
                @if ($tipo_doc === '00')
                    <x-svg_information />
                @else
                    @if ($codigo_sunat === '0')
                        <x-svg_check />
                    @else
                        <x-svg_advert />
                    @endif
                @endif
            </a>
            <a href="#" wire:click.prevent="anular({{ $id }})"
                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><span>Anular</span>
                <x-svg_circle_equis />
            </a>
        </div>
    </div>
</div>
