@extends('layouts.app')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@can('edit ruta')
<livewire:lista-precio-table />
<br>
<br>
<br>
<br>
@endcan
<livewire:ruta-table/>
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
