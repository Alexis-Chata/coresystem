@extends('layouts.app')

@section('content')
    @vite('resources/js/sweetalert2_11.js')
    <livewire:user-table />

    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('user-SweetAlert2', message => {
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
