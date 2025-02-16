@extends('layouts.app')
{{-- @section('title', 'Mi Título Personalizado') --}}
@section('content')
    <h1 class="leading-normal ">Asignar Pedidos</h1>
    <br />
    <button class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors duration-200 ease-in-out"><a href="{{ route('report.pedido_detalle') }}">Reporte Pedido Deatlle</a></button>
    <br />
    @can('asignar pedido')
        <livewire:asignar-conductor-table />
    @endcan

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('padron-deleted', message => {
                Swal.fire({
                    title: '¡Éxito!',
                    text: message,
                    icon: 'success',
                    confirmButtonText: 'OK'
                });
            });

            Livewire.on('padron-restored', message => {
                Swal.fire({
                    title: '¡Éxito!',
                    text: message,
                    icon: 'success',
                    confirmButtonText: 'OK'
                });
            });

            Livewire.on('padron-force-deleted', message => {
                Swal.fire({
                    title: '¡Éxito!',
                    text: message,
                    icon: 'success',
                    confirmButtonText: 'OK'
                });
            });
        });
    </script>
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('limpiarCheckboxHeader', () => {
                const headerCheckbox = document.querySelector('thead input[type="checkbox"]');
                if (headerCheckbox) {
                    headerCheckbox.checked = false;
                }
            });

            Livewire.on('pg:notification', (data) => {
                const notification = data[0];

                const Toast = Swal.mixin({
                    toast: true,
                    position: 'center',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer)
                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                    }
                });

                Toast.fire({
                    icon: notification.type === 'success' ? 'success' : 'error',
                    title: notification.title,
                    text: notification.message
                });
            });
        });
    </script>
@endsection
