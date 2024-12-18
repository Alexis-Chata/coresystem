@extends('layouts.app')
{{-- @section('title', 'Mi Título Personalizado') --}}
@section('content')
<h1>Generar Comprobantes</h1>
<br />
<livewire:generar-comprobantes-table />

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
@endsection
