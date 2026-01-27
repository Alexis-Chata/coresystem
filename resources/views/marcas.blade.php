@extends('layouts.app')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
