@extends('layouts.app')

@section('content')
@vite('resources/js/sweetalert2_11.js')
<livewire:vehiculo-table/>

<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('vehiculo-created', message => {
            Swal.fire({
                title: '¡Éxito!',
                text: message,
                icon: 'success',
                confirmButtonText: 'OK'
            });
        });
        Livewire.on('vehiculo-deleted', message => {
            Swal.fire({
                title: '¡Eliminado!',
                text: message,
                icon: 'success',
                confirmButtonText: 'OK'
            });
        });
        Livewire.on('vehiculo-restored', message => {
            Swal.fire({
                title: '¡Restaurado!',
                text: message,
                icon: 'success',
                confirmButtonText: 'OK'
            });
        });
        Livewire.on('vehiculo-force-deleted', message => {
            Swal.fire({
                title: '¡Eliminado Permanentemente!',
                text: message,
                icon: 'warning',
                confirmButtonText: 'OK'
            });
        });
    });
</script>
@endsection
