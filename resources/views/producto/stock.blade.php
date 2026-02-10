@extends('layouts.app')

@section('content')
    @vite('resources/js/sweetalert2_11.js')
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
