@extends('layouts.app')
{{-- @section('title', 'Mi Título Personalizado') --}}
@section('content')
    <h1>Generar Comprobantes</h1>
    <br />
    <livewire:generar-comprobantes-table />

    <br />
    <br />
    <br />
    <br />
    <h1>Comprobantes Generados</h1>
    <livewire:movimiento-comprobante-generados-table />

    @vite('resources/js/sweetalert2_11.js')
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

            Livewire.on('sweetalert2', data => {
                const alert = Array.isArray(data) ? data[0] : data;
                Swal.fire({
                    title: alert.title,
                    text: alert.text,
                    icon: alert.icon,
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
@endsection
