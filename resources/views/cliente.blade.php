@extends('layouts.app')

{{-- @section('title', 'Mi Título Personalizado') --}}
@section('content')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <livewire:cliente-table/>
    <br>
    <livewire:padron-table/>
<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('cliente-created', message => {
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