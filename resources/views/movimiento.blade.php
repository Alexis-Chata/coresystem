@extends('layouts.app')

@section('content')
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.0/css/dataTables.dataTables.css">
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdn.datatables.net/2.3.0/js/dataTables.js"></script>

    @if (request()->routeIs('movimiento.create'))
        <livewire:movimiento />
    @elseif (request()->routeIs('movimiento.view'))
        <livewire:kardex-producto />
        <br>
        <br>
        <br>
        <livewire:movimiento-table />
    @elseif (request()->routeIs('movimiento.generar-movimientoliq'))
        <livewire:GenerarMovimientoLiquido />
    @endif
@endsection
