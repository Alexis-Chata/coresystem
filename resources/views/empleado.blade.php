@extends('layouts.app')

@section('content')

    @vite('resources/js/sweetalert2_11.js')
    <livewire:empleado-table />
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
