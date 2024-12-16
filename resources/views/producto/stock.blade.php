@extends('layouts.app')

@section('content')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Stock de Productos</h2>
    <livewire:AlmacenProducto-table />
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('SweetAlert2', rowId => {
                console.log(rowId);

                Swal.fire({
                    title: '¡Éxito!',
                    text: rowId,
                    icon: 'success',
                    confirmButtonText: 'OK'
                });
            });
        });
    </script>
@endsection
