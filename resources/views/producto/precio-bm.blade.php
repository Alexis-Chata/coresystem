@extends('layouts.app')

@section('content')
    <style>
        .dataTable>tbody>tr.bg-red-200 {
            --tw-bg-opacity: 1;
            background-color: rgb(254 202 202 / var(--tw-bg-opacity, 1));
        }

        .dark .dataTable>tbody>tr.bg-red-200 {
            --tw-bg-opacity: 1;
            background-color: rgb(127 29 29 / var(--tw-bg-opacity, 1));
        }
    </style>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.0/css/dataTables.dataTables.css">
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdn.datatables.net/2.3.0/js/dataTables.js"></script>

    <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Productos B/M</h2>

    <table id="example" class="display compact">
        <thead>
            <tr>
                <th>Producto id</th>
                <th>Producto name</th>
                <th>Precio Unidad B</th>
                <th>Precio Cj B</th>
                <th>Marca</th>
                <th>Cantidad/Cj</th>
                @if (isset($productos->first()->precios->{2}))
                    <th>Precio Unidad M</th>
                    <th>Precio Cj M</th>
                @endif
                <th>deleted_at</th>
                <th>peso</th>
                <th>stock_fisico</th>
                <th>stock_disponible</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($productos as $producto)
                <tr class="{{ $producto->deleted_at ? 'bg-red-200' : '' }}">
                    <td>{{ $producto->id }}</td>
                    <td>{{ $producto->name }}</td>
                    <td class="font-extrabold text-blue-400">
                        {{ number_format($producto->precios->{1} / $producto->cantidad, 2) }}</td>
                    <td>{{ $producto->precios->{1} }}</td>
                    <td>{{ $producto->marca }}</td>
                    <td>{{ $producto->cantidad }}</td>
                    @if (isset($producto->precios->{2}))
                        <td>{{ number_format($producto->precios->{2} / $producto->cantidad, 2) }}</td>
                        <td>{{ $producto->precios->{2} }}</td>
                    @endif
                    <td>{{ $producto->deleted_at?->format('d-m-Y | H:i:s') ?? '*' }}</td>
                    <td>{{ $producto->peso }}</td>
                    <td>{{ number_format($producto->stock_fisico, calcular_digitos($producto->cantidad)) }}</td>
                    <td>{{ number_format($producto->stock_disponible, calcular_digitos($producto->cantidad)) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th>Producto id</th>
                <th>Producto name</th>
                <th>Precio Unidad B</th>
                <th>Precio Cj B</th>
                <th>Marca</th>
                <th>Cantidad/Cj</th>
                @if (isset($productos->first()->precios->{2}))
                    <th>Precio Unidad M</th>
                    <th>Precio Cj M</th>
                @endif
                <th>deleted_at</th>
                <th>peso</th>
                <th>stock_fisico</th>
                <th>stock_disponible</th>
            </tr>
        </tfoot>
    </table>

    <script defer>
        document.addEventListener('DOMContentLoaded', function() {
            new DataTable('#example', {
                pageLength: 25, // ← mostrar 25 por página
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, 'Todos']
                ]
            });
        });
    </script>
@endsection
