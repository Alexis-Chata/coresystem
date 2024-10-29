<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserRoleController;
use App\Http\Controllers\MarcaController;

// Rutas públicas (si las hay)
// ...

// Grupo de rutas protegidas por autenticación
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Rutas de UserRoleController
    Route::get('/user-roles', [UserRoleController::class, 'index'])->name('user-roles.index');
    Route::put('/users/{user}/assign-role', [UserRoleController::class, 'assignRole'])->name('users.assign-role');

    // Ruta de MarcaController
    Route::get('/marcas', [MarcaController::class, 'index'])->name('marcas.index');

    // Rutas de vistas
    Route::get('/cliente', function () {
        return view('cliente');
    })->name('cliente.index');

    Route::get('/producto', function () {
        return view('producto');
    })->name('producto.index');

    Route::get('/categoria', function () {
        return view('categoria');
    })->name('categoria.index');

    Route::get('/proveedor', function () {
        return view('proveedor');
    })->name('proveedor.index');

    Route::get('/empleado', function () {
        return view('empleado');
    })->name('empleado.index');

    Route::get('/ruta', function () {
        return view('ruta');
    })->name('ruta.index');

    Route::get('/padron', function () {
        return view('padron');
    })->name('padron.index');
});

Route::get('/', function () {
    return redirect(route('dashboard'));
})->name('index');

// ... código existente ...
