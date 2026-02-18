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

        @if (app()->environment('local'))
        <link rel="icon" href="{{ asset('src/images/logo/logo-icon-local.svg') }}" sizes="any">
        @endif

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/tailadmin.css', 'resources/js/tailadmin.js'])

        <!-- Styles -->
        @livewireStyles
        {{-- <link href="style.css" rel="stylesheet"> --}}
    </head>
    <body class="font-sans antialiased" x-data="{
        page: 'ecommerce',
        loaded: true,
        darkMode: true,
        stickyMenu: false,
        sidebarToggle: false,
        scrollTop: false,
        ultimaUbicacion: parseInt(localStorage.getItem('ultimaUbicacion') || '0', 10),

        solicitarUbicacion() {
            const ahora = Date.now();
            const diferencia = ahora - this.ultimaUbicacion;

            // throttle: no pedir si fue hace < 15s
            if (diferencia < 15000) {
                console.log('Ya se solicitó ubicación recientemente. Hace:', diferencia, 'ms');
                return;
            }

            if (!navigator.geolocation) {
                alert('Tu navegador no soporta geolocalización.');
                return;
            }

            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    const latitud = pos.coords.latitude;
                    const longitud = pos.coords.longitude;

                    // Guardar throttle solo si sí obtuvo ubicación
                    this.ultimaUbicacion = ahora;
                    localStorage.setItem('ultimaUbicacion', String(ahora));

                    console.log('Ubicación obtenida:', latitud, longitud);

                    fetch(@js(route('guardar.ubicacion')), {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': @js(csrf_token()),
                            },
                            body: JSON.stringify({ latitud, longitud }),
                        })
                        .then(async (response) => {
                            if (!response.ok) {
                                // Si aquí cae un 302/401/419/500, lo vemos claro
                                const text = await response.text();
                                throw new Error('HTTP ' + response.status + ' - ' + text);
                            }
                            return response.json();
                        })
                        .then((data) => {
                            if (data?.success) console.log('Ubicación guardada:', data);
                            else console.warn('Respuesta inesperada:', data);
                        })
                        .catch((error) => console.error('Error al guardar ubicación:', error));
                },
                (error) => {
                    alert('Error obteniendo ubicación: ' + error.message);
                }, { enableHighAccuracy: true, timeout: 20000, maximumAge: 15000 }
            );
        }
    }" x-init="darkMode = JSON.parse(localStorage.getItem('darkMode'));
    $watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)));

    // Escuchar cambios en otras pestañas
    window.addEventListener('storage', event => {
        if (event.key === 'ultimaUbicacion') {
            ultimaUbicacion = Number(event.newValue);
        }
    });"
        :class="{ 'dark text-bodydark bg-boxdark-2': darkMode === true }" @click="solicitarUbicacion"
        @keydown="solicitarUbicacion">
        <x-banner />

        <!-- ===== Preloader Start ===== -->
        <div x-show="loaded" x-init="window.addEventListener('DOMContentLoaded', () => { setTimeout(() => loaded = false, 500) })" x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
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

                <!-- Page Heading -->
                @if (isset($header))
                    <header class="bg-white dark:bg-gray-800 shadow">
                        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endif

                <!-- ===== Main Content Start ===== -->
                <main class="flex-1">
                    <div class="mx-auto p-4 md:p-6 2xl:p-10">
                        @yield('content')
                        @if (isset($slot))
                            {{ $slot }}
                        @endif
                    </div>
                </main>
                <!-- ===== Main Content End ===== -->
                <!-- ===== footer Content Start ===== -->
                <!-- Footer al final -->
                <footer class="bg-white drop-shadow-1 dark:bg-boxdark dark:drop-shadow-none">
                    <div class="flex items-center justify-between px-4 py-4 shadow-2 md:px-6 2xl:px-11">
                        <span>{{ auth()->user()->fsede?->empresa?->name_comercial }}</span>
                        <span>{{ auth()->user()->fsede?->empresa?->ruc }}</span>
                    </div>
                </footer>
                <!-- ===== footer Content End ===== -->
            </div>
        </div>

        @stack('modals')

        @livewireScripts
        @wireUiScripts
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
            [role="navigation"] .z-10 {
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
        {{-- Scripts apilados desde vistas con @push('scripts-body') --}}
        @stack('scripts-body')
    </body>
</html>
