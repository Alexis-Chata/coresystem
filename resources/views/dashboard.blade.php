@extends('layouts.app')

@section('content')
<div class="p-6 space-y-6">
    <!-- Título del Dashboard -->
    <h2 class="text-2xl font-bold text-gray-800 dark:text-white">
        Panel de Control
    </h2>

    <!-- Tarjetas de Resumen -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        @php
            $empleado = auth()->user()->empleados()->first();
            $empresa_id = $empleado?->empresa_id ?? auth()->user()->empresa_id;
        @endphp

        <!-- Clientes -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900">
                    <svg class="w-8 h-8 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">Clientes</h3>
                    @if($empleado?->tipo_empleado === 'vendedor')
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ \App\Models\Cliente::whereHas('padron.ruta', function($query) use ($empleado) {
                                $query->where('vendedor_id', $empleado->id);
                            })->count() }}
                        </p>
                        <p class="text-sm text-gray-500">En mis rutas</p>
                    @else
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ \App\Models\Cliente::where('empresa_id', $empresa_id)->count() }}
                        </p>
                        <p class="text-sm text-gray-500">Total empresa</p>
                    @endif
                </div>
            </div>
        </div>

        @can('view producto')
        <!-- Productos -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 dark:bg-green-900">
                    <svg class="w-8 h-8 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">Productos</h3>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ \App\Models\Producto::where('empresa_id', $empresa_id)->count() }}
                    </p>
                    <p class="text-sm text-gray-500">En catálogo</p>
                </div>
            </div>
        </div>
        @endcan

        @can('view empleado')
        <!-- Empleados -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 dark:bg-yellow-900">
                    <svg class="w-8 h-8 text-yellow-600 dark:text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">Empleados</h3>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ \App\Models\Empleado::where('empresa_id', $empresa_id)->count() }}
                    </p>
                    <p class="text-sm text-gray-500">Activos</p>
                </div>
            </div>
        </div>
        @endcan

        <!-- Rutas -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 dark:bg-purple-900">
                    <svg class="w-8 h-8 text-purple-600 dark:text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">Rutas</h3>
                    @if($empleado?->tipo_empleado === 'vendedor')
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ \App\Models\Ruta::where('vendedor_id', $empleado->id)->count() }}
                        </p>
                        <p class="text-sm text-gray-500">Asignadas</p>
                    @else
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ \App\Models\Ruta::where('empresa_id', $empresa_id)->count() }}
                        </p>
                        <p class="text-sm text-gray-500">Total empresa</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
<livewire:lista-precio-table/>
@endsection