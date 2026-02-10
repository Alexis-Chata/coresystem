@extends('layouts.app')

@section('content')
    @vite('resources/js/sweetalert2_11.js')
    <livewire:producto-table />
    <br>
    <br>
    <br>
    <br>
    @can('precio producto')
        <livewire:producto-lista-precio-table />
    @endcan
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('producto-created', message => {
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
