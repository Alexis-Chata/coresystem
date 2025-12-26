@extends('layouts.app')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<livewire:avance-ventas />

<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('categoria-created', message => {
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
