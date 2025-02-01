@extends('layouts.app')
{{-- @section('title', 'Mi Título Personalizado') --}}
@section('content')
    <h1>envio Guias</h1>
    <br />
    <div id="data-table">
        @livewire('f-guia-sunats-datatable')
    </div>

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

            Livewire.on('sweetalert2-notapedido', message => {
                Swal.fire({
                    title: 'Detalle del Compobante',
                    html: `
                    <div class="w-full text-xl space-y-4">
                        <div class="flex justify-between">
                            Nota de Pedido
                            <span class="outline-none inline-flex justify-center items-center group rounded-md text-white bg-primary dark:bg-primary-700 gap-x-1 text-base font-semibold px-2.5 py-0.5">
                                ${message}
                            </span>
                        </div>
                        <div class="whitespace-normal bg-yellow-100">
                            <div class="w-full">
                                <p class="font-semibold">Observaciones:</p>
                                <p class="font-semibold py-2">Las notas de pedido no se envian a SUNAT</p>
                                <ul>
                                    <li></li>
                                </ul>
                            </div>

                        </div>
                    </div>`,
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
