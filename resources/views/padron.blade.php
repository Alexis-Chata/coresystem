@extends('layouts.app')
{{-- @section('title', 'Mi Título Personalizado') --}}
@section('content')
    <livewire:padron-table/>

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
