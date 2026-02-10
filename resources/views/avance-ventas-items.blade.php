@extends('layouts.app')

@section('content')
@vite('resources/js/sweetalert2_11.js')
<livewire:avance-items-vendidos />

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
