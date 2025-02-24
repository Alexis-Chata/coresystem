@extends('layouts.app')
{{-- @section('title', 'Mi Título Personalizado') --}}
@section('content')

    <livewire:invoice-nota />

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        #data-table {

            [multiple],
            [type=date],
            [type=datetime-local],
            [type=email],
            [type=month],
            [type=number],
            [type=password],
            [type=search],
            [type=tel],
            [type=text],
            [type=time],
            [type=url],
            [type=week],
            select,
            textarea {
                border-width: 1px;
                font-size: 1rem;
                padding: .5rem .75rem
            }
        }
    </style>
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('sweetalert2-sunatResponse', message => {
                Swal.fire({
                    title: 'Detalle del Compobante',
                    html: message,
                    icon: 'info',
                    confirmButtonText: 'OK'
                });
            });

            Livewire.on('error-guardando-comprobante-nota', message => {
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
