@extends('layouts.app')

@section('content')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <livewire:producto-table />
    <br>
    <br>
    <br>
    <br>
    @php
        Spatie\Permission\Models\Permission::findOrCreate('precio producto');
    @endphp
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
