@extends('layouts.app')

@section('content')
    @vite('resources/js/sweetalert2_11.js')
    @can('create pedido')
        <livewire:pedido-table />
    @endcan

    @cannot('create pedido')
        <h1 class="leading-normal ">Sin Permisos, sistema cerrado</h1>
    @endcannot
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('pedido-guardado', message => {
                Swal.fire({
                    title: '¡Éxito!',
                    text: message,
                    icon: 'success',
                    confirmButtonText: 'OK'
                });
            });

            Livewire.on('error-guardando-pedido', message => {
                Swal.fire({
                    title: 'Oops...',
                    html: message,
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            });
        });
    </script>
@endsection

@push('scripts-body')
    <script>
        console.log("window Alpine:", window.Alpine?.version);
    </script>
@endpush
