@extends('layouts.app')

@section('content')
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.0/css/dataTables.dataTables.css">
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdn.datatables.net/2.3.0/js/dataTables.js"></script>

    <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Productos LP001</h2>

    <table id="example" class="display compact">
        <thead>
            <tr>
                <th>Producto id</th>
                <th>Producto name</th>
                <th>Precio Unidad B</th>
                <th>Precio Cj B</th>
                <th>Marca</th>
                <th>Cantidad/Cj</th>
                <th>Precio Cj M</th>
                <th>Precio Unidad M</th>
                <th>deleted_at</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($productos as $producto)
                <tr>
                    <td>{{ $producto->id }}</td>
                    <td>{{ $producto->name }}</td>
                    <td>{{ number_format($producto->precios->{1} / $producto->cantidad, 2) }}</td>
                    <td>{{ $producto->precios->{1} }}</td>
                    <td>{{ $producto->marca }}</td>
                    <td>{{ $producto->cantidad }}</td>
                    <td>{{ $producto->precios->{2} }}</td>
                    <td>{{ number_format($producto->precios->{2} / $producto->cantidad, 2) }}</td>
                    <td>{{ $producto->deleted_at }}</td>
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
                <th>Precio Cj M</th>
                <th>Precio Unidad M</th>
                <th>deleted_at</th>
            </tr>
        </tfoot>
    </table>

    <script defer>
        document.addEventListener('DOMContentLoaded', function() {
            new DataTable('#example');
        });
    </script>
@endsection
