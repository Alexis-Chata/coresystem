@extends('layouts.app')
{{-- @section('title', 'Mi Título Personalizado') --}}
@section('content')
    <h1>envio Comprobantes</h1>
    <br />
    <livewire:enviar-comprobantes />

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        #data-table{
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
                --tw-shadow: 0 0 #0000;
                -webkit-appearance: none;
                -moz-appearance: none;
                appearance: none;
                background-color: #fff;
                border-color: #6b7280;
                border-width: 1px;
                font-size: 1rem;
                line-height: 1.5rem;
                padding: .5rem .75rem
            }
        }
    </style>
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('padron-deleted', message => {
                Swal.fire({
                    title: '¡Éxito!',
                    text: message,
                    icon: 'success',
                    confirmButtonText: 'OK'
                });
            });

            Livewire.on('padron-restored', message => {
                Swal.fire({
                    title: '¡Éxito!',
                    text: message,
                    icon: 'success',
                    confirmButtonText: 'OK'
                });
            });

            Livewire.on('padron-force-deleted', message => {
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
