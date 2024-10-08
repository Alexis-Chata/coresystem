<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @vite('resources/css/app.css')
        <title>Laravel</title>

        <style>
            body {
                margin-top: 10px;
                background: #111827;
            }
        </style>
    </head>
    <body>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    @if (session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">Rol Actual</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">Asignar Rol</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($users as $user)
                                <tr>
                                    <td class="px-6 py-4 whitespace-no-wrap">{{ $user->name }}</td>
                                    <td class="px-6 py-4 whitespace-no-wrap">
                                        {{ $user->roles->pluck('name')->implode(', ') ?: 'Sin rol' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-no-wrap">
                                        <form action="{{ route('users.assign-role', $user) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <select name="role" class="form-select rounded-md shadow-sm mt-1 block w-full">
                                                @foreach ($roles as $role)
                                                    <option value="{{ $role->name }}">{{ $role->name }}</option>
                                                @endforeach
                                            </select>
                                            <button type="submit" class="mt-2 px-4 py-2 bg-blue-500 text-white rounded-md">Asignar</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @livewireScripts
        @vite('resources/js/app.js')
    </body>
</html>