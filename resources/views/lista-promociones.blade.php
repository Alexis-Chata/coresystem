@extends('layouts.app')

@section('content')
<livewire:promociones />

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
