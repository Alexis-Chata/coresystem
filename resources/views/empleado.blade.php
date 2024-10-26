@extends('layouts.app')

@section('content')

@can('view menuEmpleado')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <livewire:empleado-table/>
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('empleado-created', message => {
                Swal.fire({
                    title: '¡Éxito!',
                    text: message,
                    icon: 'success',
                    confirmButtonText: 'OK'
                });
            });
        });
    </script>
@else
    <div class="text-center">
        <p class="mt-4 text-xl">Acceso Denegado</p>
        <p class="mt-2">No tienes permisos para acceder a esta página.</p>
        <a href="{{ route('dashboard') }}" class="mt-6 inline-block px-6 py-2 text-white bg-blue-600 rounded hover:bg-blue-700">
            Volver al Dashboard
        </a>
    </div>
@endcan

@endsection