@extends('layouts.app')

@section('content')
<div class="rounded-sm border border-stroke bg-white px-2.5 pt-6 pb-4 shadow-default dark:border-strokedark dark:bg-boxdark sm:px-7.5">
    <h4 class="mb-6 text-xl font-semibold text-black dark:text-white">
        Gestión de Permisos de los Roles
    </h4>
    <livewire:permisos-roles />
</div>
@endsection

<style>
    .max-w-full.overflow-x-auto {
        overflow-x: auto !important;
        padding-bottom: 99px;
    }
</style>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush
