@extends('layouts.app')

@section('content')
@vite('resources/js/sweetalert2_11.js')
@can('edit ruta')
<livewire:lista-precio-table />
<br>
<br>
<br>
<br>
@endcan
<livewire:ruta-table />
<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('ruta-created', message => {
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
