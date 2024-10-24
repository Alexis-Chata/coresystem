@extends('layouts.app')

@section('content')
<div class="rounded-sm border border-stroke bg-white px-5 pt-6 pb-2.5 shadow-default dark:border-strokedark dark:bg-boxdark sm:px-7.5 xl:pb-1">
    <h4 class="mb-6 text-xl font-semibold text-black dark:text-white">
        Gestión de Roles de Usuario
    </h4>

    @if (session('success'))
        <div class="mb-4 flex w-full border-l-6 border-[#34D399] bg-[#34D399] bg-opacity-[15%] px-7 py-8 shadow-md dark:bg-[#1B1B24] dark:bg-opacity-30 md:p-9">
            <div class="mr-5 flex h-9 w-full max-w-[36px] items-center justify-center rounded-lg bg-[#34D399]">
                <svg width="16" height="12" viewBox="0 0 16 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M15.2984 0.826822L15.2868 0.811827L15.2741 0.797751C14.9173 0.401867 14.3238 0.400754 13.9657 0.794406L5.91888 9.45376L2.05667 5.2868C1.69856 4.89287 1.10487 4.89389 0.747996 5.28987C0.417335 5.65675 0.417335 6.22337 0.747996 6.59026L0.747959 6.59029L0.752701 6.59541L4.86742 11.0348C5.14445 11.3405 5.52858 11.5 5.89581 11.5C6.29242 11.5 6.65178 11.3355 6.92401 11.035L15.2162 2.11161C15.5833 1.74452 15.576 1.18615 15.2984 0.826822Z" fill="white" stroke="white"></path>
                </svg>
            </div>
            <div class="w-full">
                <h5 class="mb-3 text-lg font-semibold text-black dark:text-[#34D399]">
                    Éxito
                </h5>
                <p class="text-base leading-relaxed text-body">
                    {{ session('success') }}
                </p>
            </div>
        </div>
    @endif

    @if (isset($users) && isset($roles))
        <div class="max-w-full overflow-x-auto">
            <table class="w-full table-auto">
                <thead>
                    <tr class="bg-gray-2 text-left dark:bg-meta-4">
                        <th class="min-w-[220px] py-4 px-4 font-medium text-black dark:text-white xl:pl-11">
                            Usuario
                        </th>
                        <th class="min-w-[150px] py-4 px-4 font-medium text-black dark:text-white">
                            Rol Actual
                        </th>
                        <th class="min-w-[120px] py-4 px-4 font-medium text-black dark:text-white">
                            Asignar/Cambiar Rol
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td class="border-b border-[#eee] py-5 px-4 pl-9 dark:border-strokedark xl:pl-11">
                                <h5 class="font-medium text-black dark:text-white">{{ $user->name }}</h5>
                            </td>
                            <td class="border-b border-[#eee] py-5 px-4 dark:border-strokedark">
                                <p class="text-black dark:text-white">
                                    {{ $user->roles->pluck('name')->implode(', ') ?: 'Sin rol' }}
                                </p>
                            </td>
                            <td class="border-b border-[#eee] py-5 px-4 dark:border-strokedark">
                                <form action="{{ route('users.assign-role', $user) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div x-data="{ open: false }" class="relative">
                                        <button @click="open = !open" type="button" class="flex items-center justify-between w-full rounded border border-stroke bg-white py-3 px-5 text-left text-black dark:bg-form-input dark:text-white">
                                            <span>{{ $user->roles->isEmpty() ? 'Asignar Rol' : 'Cambiar Rol' }}</span>
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </button>
                                        <div x-show="open" @click.away="open = false" class="absolute z-10 mt-2 w-full rounded-md bg-white shadow-lg dark:bg-boxdark">
                                            <ul class="py-1">
                                                @foreach ($roles as $role)
                                                    <li>
                                                        <button type="submit" name="role" value="{{ $role->name }}" class="block w-full px-4 py-2 text-left text-black hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700">
                                                            {{ $role->name }}
                                                        </button>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="text-center text-body dark:text-bodydark">No se encontraron usuarios o roles.</p>
    @endif
</div>
@endsection

<style>
    .max-w-full.overflow-x-auto {
        overflow-x: auto !important;
    }
</style>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush