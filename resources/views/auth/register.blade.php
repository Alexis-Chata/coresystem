<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} - Registro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-xl shadow-2xl w-full max-w-md">
        <div class="flex justify-center mb-8">
            <!-- Reemplaza esto con tu logo -->
            <svg class="w-20 h-20 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm0-2a6 6 0 100-12 6 6 0 000 12z" clip-rule="evenodd" />
            </svg>
        </div>
        <h2 class="text-3xl font-bold mb-6 text-center text-gray-800">Registro</h2>

        @if ($errors->any())
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                <p class="font-bold">Por favor, corrija los siguientes errores:</p>
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}">
            @csrf
            <div class="mb-6">
                <label for="name" class="block text-gray-700 text-sm font-semibold mb-2">Nombre</label>
                <input type="text" name="name" id="name" class="shadow-sm appearance-none border rounded-md w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required autofocus>
            </div>
            <div class="mb-6">
                <label for="email" class="block text-gray-700 text-sm font-semibold mb-2">Correo electrónico</label>
                <input type="email" name="email" id="email" class="shadow-sm appearance-none border rounded-md w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
            </div>
            <div class="mb-6">
                <label for="password" class="block text-gray-700 text-sm font-semibold mb-2">Contraseña</label>
                <input type="password" name="password" id="password" class="shadow-sm appearance-none border rounded-md w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
            </div>
            <div class="mb-6">
                <label for="password_confirmation" class="block text-gray-700 text-sm font-semibold mb-2">Confirmar Contraseña</label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="shadow-sm appearance-none border rounded-md w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
            </div>
            <div class="mb-6">
                <label for="empleado_id" class="block text-gray-700 text-sm font-semibold mb-2">Codigo</label>
                <select name="empleado_id" id="empleado_id" class="shadow-sm appearance-none border rounded-md w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                    <option value="">Seleccione Un Codigo</option>
                    @foreach($empleados_id_sin_asignar as $empleado)
                        <option value="{{ $empleado->id }}">{{ $empleado->id }}</option>
                    @endforeach
                </select>
            </div>
            {{-- <div class="mb-6">
                <label for="empresa_id" class="block text-gray-700 text-sm font-semibold mb-2">Empresa</label>
                <select name="empresa_id" id="empresa_id" class="shadow-sm appearance-none border rounded-md w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                    <option value="">Seleccione una empresa</option>
                    @foreach(App\Models\Empresa::all() as $empresa)
                        <option value="{{ $empresa->id }}">{{ $empresa->razon_social }}</option>
                    @endforeach
                </select>
            </div> --}}

            @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
                <div class="mb-6">
                    <label class="flex items-center">
                        <input type="checkbox" name="terms" id="terms" class="form-checkbox h-4 w-4 text-blue-600" required>
                        <span class="ml-2 text-sm text-gray-600">
                            {!! __('I agree to the :terms_of_service and :privacy_policy', [
                                'terms_of_service' => '<a target="_blank" href="'.route('terms.show').'" class="text-blue-600 hover:text-blue-800">'.__('Terms of Service').'</a>',
                                'privacy_policy' => '<a target="_blank" href="'.route('policy.show').'" class="text-blue-600 hover:text-blue-800">'.__('Privacy Policy').'</a>',
                            ]) !!}
                        </span>
                    </label>
                </div>
            @endif

            <div class="flex items-center justify-center">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-md focus:outline-none focus:shadow-outline w-full transition duration-150 ease-in-out">
                    Registrarse
                </button>
            </div>
        </form>
        <p class="text-center mt-8 text-sm text-gray-600">
            ¿Ya tiene una cuenta?
            <a href="{{ route('login') }}" class="font-semibold text-blue-600 hover:text-blue-800">Inicie sesión aquí</a>
        </p>
    </div>
</body>
</html>
