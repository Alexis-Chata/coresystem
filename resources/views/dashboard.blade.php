@extends('layouts.app')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <h2>Esto siempre va a cambiar</h2>
    <livewire:empleados-table/>
    <script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('showAlert', (data) => {
            Swal.fire({
                title: data.type.charAt(0).toUpperCase() + data.type.slice(1),
                text: data.message,
                icon: data.type,
                confirmButtonText: 'OK'
            });
        });
    });
</script>
@endsection