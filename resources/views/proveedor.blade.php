@extends('layouts.app')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<livewire:proveedor-table/>

<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('proveedor-created', message => {
            Swal.fire({
                title: '¡Éxito!',
                text: message,
                icon: 'success',
                confirmButtonText: 'OK'
            });
        });

        Livewire.on('showAlert', data => {
            const alertData = Array.isArray(data) ? data[0] : data;
            Swal.fire({
                title: alertData.type === 'error' ? '¡Error!' : '¡Atención!',
                text: alertData.message,
                icon: alertData.type,
                confirmButtonText: 'OK'
            });
        });
        
    });
</script>
@endsection