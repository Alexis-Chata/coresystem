@extends('layouts.app')
{{-- @section('title', 'Mi Título Personalizado') --}}
@section('content')
    <livewire:empresa-table />
    <br />
    <br />
    <livewire:f-sede-table />
    <br />
    <br />
    <livewire:f-serie-table />

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('SweetAlert2', message => {
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
