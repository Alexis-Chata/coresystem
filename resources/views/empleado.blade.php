@extends('layouts.app')

@section('content')

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <livewire:empleado-table/>
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('empleado-created', message => {
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