@extends('layouts.app')

@section('content')
@vite('resources/js/sweetalert2_11.js')
    <livewire:marca-table />
<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('marca-created', message => {
            Swal.fire({
                title: '¡Éxito!',
                text: message,
                icon: 'success',
                confirmButtonText: 'OK'
            });
        });
    });
</script>
<style>
    span.absolute.inset-y-0.left-0.flex.items-center.pl-1 {display: none;}
</style>
@endsection
