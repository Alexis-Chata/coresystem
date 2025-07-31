<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>
            @hasSection('title')
                @yield('title')
            @else
                {{ ucfirst(str_replace('.index', '', basename(request()->route()->getName()))) }}
            @endif
        </title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/tailadmin.css', 'resources/js/tailadmin.js'])

        <!-- Styles -->
        @livewireStyles
        {{-- <link href="style.css" rel="stylesheet"> --}}
    </head>
    <body
        class="font-sans antialiased"
        x-data="{
            page: 'ecommerce',
            loaded: true,
            darkMode: true,
            stickyMenu: false,
            sidebarToggle: false,
            scrollTop: false,
            ultimaUbicacion: Number(localStorage.getItem('ultimaUbicacion')) || 0,
            solicitarUbicacion() {
            const ahora = Date.now();
            const diferencia = ahora - this.ultimaUbicacion;

            if (diferencia < 20000) { // menos de 20 segundos
                console.log('Ya se solicitó ubicación recientemente.', 'hace:', diferencia, 'ms');
                return;
            }

            if (!navigator.geolocation) {
                alert('Tu navegador no soporta geolocalización.');
                return;
            }

            this.ultimaUbicacion = ahora;
            localStorage.setItem('ultimaUbicacion', ahora);

            navigator.geolocation.getCurrentPosition(pos => {
            const latitud = pos.coords.latitude;
            const longitud = pos.coords.longitude;
            console.log('Ubicación obtenida:', latitud, longitud);

            fetch('{{ route("guardar.ubicacion") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ latitud, longitud })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Ubicación guardada exitosamente:', data);
                }
            })
            .catch(error => {
                console.error('Error al guardar ubicación:', error);
            });

            }, error => {
                alert('Error obteniendo ubicación: ' + error.message);
            });

            }
        }"
        x-init="
            darkMode = JSON.parse(localStorage.getItem('darkMode'));
            $watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)));

            // Escuchar cambios en otras pestañas
            window.addEventListener('storage', event => {
                if (event.key === 'ultimaUbicacion') {
                    ultimaUbicacion = Number(event.newValue);
                }
            });
        "
        :class="{ 'dark text-bodydark bg-boxdark-2': darkMode === true }"
        @click="solicitarUbicacion"
        @keydown="solicitarUbicacion"
    >
        <x-banner />

        <!-- ===== Preloader Start ===== -->
        <div x-show="loaded" x-init="window.addEventListener('DOMContentLoaded', () => { setTimeout(() => loaded = false, 500) })"
            class="fixed left-0 top-0 z-999999 flex h-screen w-screen items-center justify-center bg-white dark:bg-black">
            <div class="h-16 w-16 animate-spin rounded-full border-4 border-solid border-primary border-t-transparent">
            </div>
        </div>
        <!-- ===== Preloader End ===== -->

        <!-- ===== Page Wrapper Start ===== -->
        <div class="flex h-screen overflow-hidden">
            <!-- ===== Sidebar Start ===== -->
            @include('layouts.partials.sidebar')
            <!-- ===== Sidebar End ===== -->

            <div class="relative flex flex-1 flex-col overflow-y-auto">
                <!-- ===== Header Start ===== -->
                @include('layouts.partials.header')
                <!-- ===== Header End ===== -->

                <!-- ===== Main Content Start ===== -->
                <main>
                    <div class="mx-auto p-4 md:p-6 2xl:p-10">
                        @yield('content')
                    </div>
                </main>
                <!-- ===== Main Content End ===== -->
            </div>
        </div>

        @stack('modals')

        @livewireScripts
        {{-- <script defer src="bundle.js"></script> --}}
        <style>
            span.absolute.inset-y-0.left-0.flex.items-center.pl-1 {
                pointer-events: none;
                justify-content: flex-end;
                right: 0;
            }
            .overflow-x-auto {
                /* overflow-x: initial !important; */
            }
            #power-grid-table-container.overflow-x-auto {
                overflow: visible;
            }
            [role="navigation"] .z-10{
                z-index: 8 !important;
            }
        </style>
        <script>
            document.addEventListener('livewire:initialized', () => {
                document.addEventListener('focusout', (event) => {
                    if (event.target.matches('[x-ref="editable"]')) {
                        event.target.dispatchEvent(new KeyboardEvent('keydown', {
                            key: 'Escape',
                            code: 'Escape',
                            bubbles: true
                        }));
                    }
                });
            });
        </script>
    </body>
</html>
