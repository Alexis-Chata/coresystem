@extends('layouts.app')

@section('content')
    @if (request()->routeIs('movimiento.create'))
        <livewire:movimiento />
    @elseif (request()->routeIs('movimiento.view'))
        <livewire:movimiento-table />
    @elseif (request()->routeIs('movimiento.generar-movimientoliq'))
        <livewire:GenerarMovimientoLiquido />
    @endif
@endsection
